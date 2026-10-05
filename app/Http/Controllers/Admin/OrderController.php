<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\GHNOrderService;
use App\Services\GHNService;
use App\Services\MembershipService;
use App\Services\MomoService;
use App\Support\OrderStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private const TABS = [
        'all'            => ['label' => 'Tất cả', 'color' => 'blue', 'statuses' => []],
        'pending'        => ['label' => 'Chờ xử lý', 'color' => 'slate', 'statuses' => ['pending', 'not_shipped', 'processing']],
        'ready'          => ['label' => 'Chờ lấy hàng', 'color' => 'cyan', 'statuses' => ['ready_to_pick']],
        'picking'        => ['label' => 'Đang lấy hàng', 'color' => 'cyan', 'statuses' => ['picking']],
        'delivering'     => ['label' => 'Đang giao', 'color' => 'amber', 'statuses' => ['delivering', 'picked', 'storing', 'transporting', 'sorting']],
        'delivered'      => ['label' => 'Thành công', 'color' => 'green', 'statuses' => ['delivered']],
        'return'         => ['label' => 'Hoàn hàng', 'color' => 'orange', 'statuses' => ['return', 'returning', 'returned', 'return_transporting', 'return_sorting']],
        'cancel_request' => ['label' => 'Chờ duyệt hủy', 'color' => 'rose', 'statuses' => []], // filter by status=cancel_requested
        'cancelled'      => ['label' => 'Đã hủy', 'color' => 'red', 'statuses' => ['cancelled']],
    ];

    public function index(Request $request)
    {
        $paymentLabels = [
            'pending'         => 'Chờ thanh toán',
            'initiated'       => 'Đang chờ MoMo',
            'paid'            => 'Đã thanh toán',
            'failed'          => 'Thanh toán thất bại',
            'cancelled'       => 'Đã hủy',
            'refund_pending'  => 'Chờ hoàn tiền',
            'refunded'        => 'Đã hoàn tiền',
        ];

        $shippingLabels  = OrderStatus::SHIPPING;
        $orderLabels     = OrderStatus::ORDER;
        $bulkOrderLabels = OrderStatus::bulkOrderOptions();
        $bulkShipLabels  = OrderStatus::bulkShipOptions();

        $filters = $request->validate([
            'search'          => ['nullable', 'string', 'max:100'],
            'status'          => ['nullable', Rule::in(['pending', 'paid', 'paid_momo', 'cod_ordered', 'cod_paid', 'cancel_requested', 'cancelled'])],
            'payment_status'  => ['nullable', Rule::in(array_keys($paymentLabels))],
            'shipping_status' => ['nullable', Rule::in(array_keys($shippingLabels))],
            'gateway'         => ['nullable', Rule::in(['cod', 'momo', 'unknown'])],
            'tab'             => ['nullable', Rule::in(array_keys(self::TABS))],
            'date_from'       => ['nullable', 'date_format:Y-m-d'],
            'date_to'         => ['nullable', 'date_format:Y-m-d'],
            'per_page'        => ['nullable', 'integer', Rule::in([25, 50, 100])],
            'sort'            => ['nullable', Rule::in(['newest', 'oldest', 'amount_desc', 'amount_asc'])],
            'page'            => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Order::query()->with(['items.product', 'user', 'paymentTransactions']);

        if ($request->filled('status')) {
            $query->where('status', $filters['status']);
        }
        if ($request->filled('shipping_status')) {
            $query->where('shipping_status', $filters['shipping_status']);
        }
        if ($request->filled('search')) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('ghn_order_code', 'like', "%{$search}%")
                    ->orWhereHas('items.product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
                if (preg_match('/^(?:#|DH)?0*(\d+)$/i', $search, $m)) {
                    $q->orWhere('id', $m[1]);
                }
            });
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<', Carbon::parse($filters['date_to'])->addDay()->startOfDay());
        }

        // Tab counts
        $shippingCounts = (clone $query)->select('shipping_status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('shipping_status')
            ->pluck('total', 'shipping_status');

        $cancelRequestCount = (clone $query)->where('status', 'cancel_requested')->count();

        $tabs = collect(self::TABS)->map(function ($tab, $key) use ($shippingCounts, $cancelRequestCount) {
            if ($key === 'all') {
                $tab['count'] = $shippingCounts->sum();
            } elseif ($key === 'cancel_request') {
                $tab['count'] = $cancelRequestCount;
            } else {
                $tab['count'] = collect($tab['statuses'])->sum(fn ($s) => $shippingCounts->get($s, 0));
            }
            return $tab;
        });

        $activeTab = $filters['tab'] ?? 'all';
        if ($activeTab === 'cancel_request') {
            $query->where('status', 'cancel_requested');
        } elseif ($activeTab !== 'all' && !empty(self::TABS[$activeTab]['statuses'])) {
            $query->whereIn('shipping_status', self::TABS[$activeTab]['statuses']);
        }

        [$column, $direction] = match ($filters['sort'] ?? 'newest') {
            'oldest'      => ['created_at', 'asc'],
            'amount_desc' => ['total_price', 'desc'],
            'amount_asc'  => ['total_price', 'asc'],
            default       => ['created_at', 'desc'],
        };

        $orders = $query->orderBy($column, $direction)
            ->orderBy('id', $direction)
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();

        return view('admin.orders.index', compact(
            'orders', 'filters', 'tabs', 'activeTab', 'paymentLabels', 'shippingLabels', 'orderLabels', 'bulkOrderLabels', 'bulkShipLabels'
        ));
    }

    public function show($id)
    {
        $order = Order::with([
            'user',
            'items.product',
            'paymentTransactions' => fn ($q) => $q->latest(),
        ])->findOrFail($id);

        $orderLabels    = OrderStatus::ORDER;
        $shippingLabels = OrderStatus::SHIPPING;
        $returnLabels   = OrderStatus::RETURN;
        $nextShip       = OrderStatus::nextShip($order->shipping_status);
        $shipOptions    = OrderStatus::manualShipOptions();

        return view('admin.orders.show', compact(
            'order', 'orderLabels', 'shippingLabels', 'returnLabels', 'nextShip', 'shipOptions'
        ));
    }

    /**
     * Cập nhật trạng thái đơn / vận chuyển.
     * - Không cho hủy khi đang giao / đã giao
     * - return* chỉ set qua yêu cầu trả hàng của user
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status'          => 'nullable|string|max:50',
            'shipping_status' => 'nullable|string|max:50',
        ]);

        $blockedCancel = ['delivering', 'picked', 'storing', 'transporting', 'sorting', 'delivered', 'return', 'returning', 'returned'];
        if (in_array($order->shipping_status, $blockedCancel, true) && $request->shipping_status === 'cancelled') {
            return back()->with('error', 'Đơn đang giao / đã giao / đang trả — không được hủy.');
        }

        $shippingStepsRequiringGhnCode = [
            'ready_to_pick', 'picking', 'picked', 'storing', 'transporting', 'sorting', 'delivering', 'delivered',
        ];
        if ($request->filled('shipping_status')
            && in_array($request->shipping_status, $shippingStepsRequiringGhnCode, true)
            && !$order->ghn_order_code) {
            return back()->with('error', 'Chưa có mã vận đơn GHN nên không thể chuyển đơn sang trạng thái đang giao. Hãy tạo vận đơn trước.');
        }

        // Không cho admin gán tay trạng thái return*
        $returnOnly = ['return', 'returning', 'returned', 'return_transporting', 'return_sorting'];
        if ($request->filled('shipping_status') && in_array($request->shipping_status, $returnOnly, true)) {
            return back()->with('error', 'Trạng thái hoàn hàng chỉ được cập nhật khi user yêu cầu trả hàng.');
        }

        $data = [];
        if ($request->filled('status') && array_key_exists($request->status, OrderStatus::ORDER)) {
            $data['status'] = $request->status;
        }
        if ($request->filled('shipping_status') && array_key_exists($request->shipping_status, OrderStatus::SHIPPING)) {
            $data['shipping_status'] = $request->shipping_status;
            // Khi giao xong → có thể đánh dấu đơn completed
            if ($request->shipping_status === 'delivered' && in_array($order->status, ['paid', 'paid_momo', 'cod_ordered'], true)) {
                $data['status'] = $data['status'] ?? 'completed';
            }
        }

        if ($data) {
            $order->update($data);
            $order->refresh();

            // Nếu đơn thành hoàn thành -> cộng điểm; nếu bị huỷ -> hoàn trả điểm
            if ($order->status === 'completed') {
                MembershipService::awardOrderPoints($order);
            } elseif ($order->status === 'cancelled') {
                MembershipService::revokeOrderPoints($order);
            }
        }

        $msg = 'Đã cập nhật đơn #' . $order->id;
        if (isset($data['shipping_status'])) {
            $msg .= ' → VC: ' . OrderStatus::shipLabel($data['shipping_status']);
        }
        if (isset($data['status'])) {
            $msg .= ' · Đơn: ' . OrderStatus::orderLabel($data['status']);
        }

        return back()->with('success', $msg);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'order_ids'       => ['required', 'array', 'min:1', 'max:100'],
            'order_ids.*'     => ['required', 'integer', 'distinct', 'exists:orders,id'],
            'shipping_status' => ['required', Rule::in(array_keys(OrderStatus::bulkShipOptions()))],
        ]);

        $newShipStatus = $validated['shipping_status'];

        [$updated, $skipped] = DB::transaction(function () use ($validated, $newShipStatus) {
            $orders = Order::whereIn('id', $validated['order_ids'])
                ->lockForUpdate()
                ->get();

            $updated = 0;
            $skipped = 0;

            foreach ($orders as $order) {
                // Bo qua don da huy / cho duyet huy
                if (in_array($order->status, ['cancelled', 'cancel_requested'], true)) {
                    $skipped++;
                    continue;
                }

                // Bo qua don dang trong luong tra hang
                if (!empty($order->return_status)) {
                    $skipped++;
                    continue;
                }

                $data = ['shipping_status' => $newShipStatus];

                // Tu dong chuyen trang thai don -> Hoan thanh khi giao xong
                if ($newShipStatus === 'delivered'
                    && in_array($order->status, ['paid', 'paid_momo', 'cod_ordered', 'confirmed'], true)
                ) {
                    $data['status'] = 'completed';
                }

                $order->update($data);
                $order->refresh();

                if ($order->status === 'completed') {
                    MembershipService::awardOrderPoints($order);
                }
                $updated++;
            }

            return [$updated, $skipped];
        });

        $shipLabel = \App\Support\OrderStatus::shipLabel($newShipStatus);
        $msg = 'Da cap nhat van chuyen -> ' . $shipLabel . ' cho ' . $updated . ' don.';
        if ($newShipStatus === 'delivered') {
            $msg .= ' Don du dieu kien da tu dong chuyen sang Hoan thanh.';
        }
        if ($skipped > 0) {
            $msg .= ' Bo qua ' . $skipped . ' don (da huy hoac dang tra hang).';
        }

        return back()->with('success', $msg);
    }

    public function retryGhnOrder(Order $order, GHNOrderService $ghnOrder)
    {
        $retry = DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->ghn_order_code) {
                return ['error' => 'Đơn hàng đã có mã vận đơn GHN.'];
            }
            if (in_array($lockedOrder->status, ['cancelled', 'cancel_requested'], true)
                || $lockedOrder->shipping_status === 'cancelled'
                || !empty($lockedOrder->return_status)) {
                return ['error' => 'Đơn đã hủy hoặc đang xử lý hoàn hàng, không thể tạo vận đơn.'];
            }
            if ($lockedOrder->shipping_status === 'processing') {
                return ['error' => 'Đơn đang được tạo vận đơn, vui lòng chờ hoàn tất.'];
            }

            $isPaidMomo = $lockedOrder->paymentTransactions()
                ->where('gateway', 'momo')
                ->where('status', 'paid')
                ->exists();
            $isCod = $lockedOrder->paymentTransactions()
                ->where('gateway', 'cod')
                ->exists();

            // Nếu MoMo đã trả tiền thì COD = 0; nếu chưa trả tiền hoặc đơn COD thì thu COD = tiền hàng
            $isPaid = $isPaidMomo;
            if (!$isPaid && (int) round((float) $lockedOrder->total_price) > GHNOrderService::codLimit()) {
                return ['error' => GHNOrderService::codLimitMessage()];
            }

            // Nếu đơn MoMo chưa thanh toán nhưng Admin tạo vận đơn giao, cập nhật thành COD
            if (!$isPaidMomo && !$isCod && $lockedOrder->status === 'pending') {
                $lockedOrder->update(['status' => 'cod_ordered']);
                PaymentTransaction::create([
                    'order_id' => $lockedOrder->id,
                    'gateway'  => 'cod',
                    'amount'   => (int) round((float) $lockedOrder->total_price),
                    'status'   => 'pending',
                    'message'  => 'Admin tạo vận đơn GHN (thu tiền hàng COD)',
                ]);
            }

            $lockedOrder->update(['shipping_status' => 'processing']);

            return ['is_paid' => $isPaid];
        });

        if (isset($retry['error'])) {
            return back()->with('error', $retry['error']);
        }

        $order->refresh()->load('items.product');
        $response = $ghnOrder->create($order, $retry['is_paid']);
        $orderCode = $response['data']['order_code'] ?? null;

        if ((int) ($response['code'] ?? 0) === 200 && $orderCode) {
            $order->update([
                'ghn_order_code'  => $orderCode,
                'shipping_status' => 'ready_to_pick',
                'ghn_total_fee'   => (int) ($response['data']['total_fee'] ?? $order->ghn_total_fee),
            ]);

            return back()->with('success', 'Đã tạo vận đơn GHN: ' . $orderCode);
        }

        $order->update(['shipping_status' => 'pending']);
        Log::warning('Admin GHN retry failed', ['order_id' => $order->id, 'response' => $response]);

        $message = $response['data']['code_message_value']
            ?? $response['data']['message']
            ?? $response['message']
            ?? 'GHN chưa trả mã vận đơn.';
        if (is_array($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        }

        return back()->with('error', 'Chưa tạo được vận đơn GHN: ' . $message);
    }

    public function cancel(Order $order, GHNService $ghn)
    {
        $blocked = ['delivering', 'picked', 'storing', 'transporting', 'sorting', 'delivered'];
        if (in_array($order->shipping_status, $blocked, true)) {
            return back()->with('error', 'Đơn đang giao — không được hủy.');
        }

        if ($order->paymentTransactions()->where('gateway', 'momo')->where('status', 'paid')->exists()) {
            return back()->with('error', 'Đơn MoMo đã thanh toán phải được khách gửi yêu cầu và admin duyệt hủy.');
        }

        if ($order->ghn_order_code) {
            try {
                $response = $ghn->cancelOrder([$order->ghn_order_code]);
                if (($response['code'] ?? null) !== 200) {
                    Log::warning('GHN cancel on admin cancel failed', [
                        'order_id' => $order->id,
                        'res'      => $response,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('GHN cancel exception', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        $order->update([
            'status'          => 'cancelled',
            'shipping_status' => 'cancelled',
        ]);
        MembershipService::revokeOrderPoints($order);

        return back()->with('success', 'Đã hủy đơn #' . $order->id . '.');
    }

    /**
     * Duyệt / từ chối yêu cầu hủy đơn (MoMo đã thanh toán).
     * Dùng status = cancel_requested (không cần cột cancel_*).
     * - approve: hủy GHN (nếu có) + status=cancelled + hoàn tiền MoMo
     * - reject: trả status về paid_momo / paid, ghi chú
     */
    public function processCancel(Request $request, Order $order, MomoService $momo, GHNService $ghn)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'note'   => 'nullable|string|max:500',
        ]);

        if ($order->status !== 'cancel_requested') {
            return back()->with('error', 'Đơn không có yêu cầu hủy đang chờ duyệt.');
        }
        if (!$order->paymentTransactions()->where('gateway', 'momo')->where('status', 'paid')->exists()) {
            return back()->with('error', 'Không tìm thấy giao dịch MoMo đã thanh toán để xử lý yêu cầu hủy.');
        }

        $action = $request->action;
        $note   = $request->input('note');

        if ($action === 'reject') {
            $order->update([
                'status'              => $order->cancel_previous_status ?: 'paid',
                'cancel_admin_note'   => $note,
                'cancel_processed_at' => now(),
            ]);

            return back()->with('success', 'Đã từ chối yêu cầu hủy đơn #' . $order->id);
        }

        // approve → hủy vận đơn GHN (nếu còn) + hủy đơn + hoàn tiền
        if ($order->ghn_order_code) {
            try {
                $response = $ghn->cancelOrder([$order->ghn_order_code]);
                if (($response['code'] ?? null) !== 200) {
                    Log::warning('GHN cancel on processCancel failed', [
                        'order_id' => $order->id,
                        'code'     => $order->ghn_order_code,
                        'res'      => $response,
                    ]);
                    return back()->with('error', 'GHN chưa hủy được vận đơn; chưa hủy đơn hoặc hoàn tiền.');
                }
            } catch (\Throwable $e) {
                Log::error('GHN cancel exception', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                return back()->with('error', 'GHN gặp lỗi khi hủy vận đơn; chưa hủy đơn hoặc hoàn tiền.');
            }
        }

        $order->update([
            'status'          => 'cancelled',
            'shipping_status' => 'cancelled',
            'cancel_admin_note' => $note,
            'cancel_processed_at' => now(),
        ]);
        MembershipService::revokeOrderPoints($order);

        $refundMsg = $this->refundPaidTransactions($order);

        return back()->with('success', 'Đã duyệt hủy đơn #' . $order->id . '. ' . $refundMsg);
    }

    /**
     * Duyệt / từ chối / hoàn tất trả hàng.
     * - approve: shipping=returning, giao dịch → refund_pending
     * - complete: shipping=returned, status=cancelled, giao dịch → refunded
     * - reject: quay lại delivered
     */
    public function processReturn(Request $request, Order $order)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,complete',
            'note'   => 'nullable|string|max:500',
        ]);

        if (!in_array($order->return_status, ['requested', 'approved'], true)) {
            return back()->with('error', 'Đơn không có yêu cầu trả hàng đang mở.');
        }

        $action = $request->action;
        $note   = $request->input('note');

        if ($action === 'approve') {
            $order->update([
                'return_status'       => 'approved',
                'return_admin_note'   => $note,
                'return_processed_at' => now(),
                'shipping_status'     => 'returning',
            ]);

            $paid = $order->paymentTransactions()->where('status', 'paid')->latest()->first();
            if ($paid) {
                $paid->update(['status' => 'refund_pending']);
            }

            return back()->with('success', 'Đã duyệt trả hàng đơn #' . $order->id);
        }

        if ($action === 'complete') {
            $order->update([
                'return_status'       => 'completed',
                'return_admin_note'   => $note ?: $order->return_admin_note,
                'return_processed_at' => now(),
                'shipping_status'     => 'returned',
                'status'              => 'cancelled',
            ]);
            MembershipService::revokeOrderPoints($order);

            // Tự động hoàn tiền hàng nếu đã thu qua MoMo
            $refundMsg = $this->refundPaidTransactions($order);

            return back()->with('success', 'Hoàn tất trả hàng đơn #' . $order->id . '. ' . $refundMsg);
        }

        $order->update([
            'return_status'       => 'rejected',
            'return_admin_note'   => $note,
            'return_processed_at' => now(),
            'shipping_status'     => 'delivered',
        ]);

        return back()->with('success', 'Đã từ chối trả hàng đơn #' . $order->id);
    }

    /**
     * Admin bấm "Hoàn tiền" thủ công (MoMo / đánh dấu COD).
     */
    public function refund(Request $request, Order $order, MomoService $momo)
    {
        $request->validate([
            'transaction_id' => 'nullable|integer|exists:payment_transactions,id',
        ]);

        $txQuery = $order->paymentTransactions()
            ->whereIn('status', ['paid', 'refund_pending'])
            ->where('gateway', 'momo');

        if ($request->filled('transaction_id')) {
            $txQuery->where('id', $request->transaction_id);
        }

        $tx = $txQuery->latest()->first();

        if (!$tx) {
            // COD / không có GD MoMo → chỉ đánh dấu hoàn nội bộ
            $cod = $order->paymentTransactions()
                ->whereIn('status', ['paid', 'refund_pending', 'pending'])
                ->where('gateway', 'cod')
                ->latest()
                ->first();

            if ($cod) {
                $cod->update(['status' => 'refunded', 'message' => 'Hoàn nội bộ (COD / không thu tiền hàng)']);
                return back()->with('success', 'Đã đánh dấu hoàn (COD — không gọi cổng thanh toán).');
            }

            // Không có transaction → tạo bản ghi hoàn ảo theo total_price nếu đơn đã paid
            if (in_array($order->status, ['paid', 'paid_momo', 'cancelled'], true)) {
                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'gateway'  => 'manual',
                    'amount'   => $order->total_price,
                    'status'   => 'refunded',
                    'message'  => 'Hoàn thủ công bởi admin',
                    'paid_at'  => now(),
                ]);
                return back()->with('success', 'Đã ghi nhận hoàn tiền thủ công ' . number_format($order->total_price, 0, ',', '.') . 'đ');
            }

            return back()->with('error', 'Không tìm thấy giao dịch cần hoàn.');
        }

        $result = $momo->refund($tx, null, 'Hoan tien tra hang don #' . $order->id);

        if ($result['ok']) {
            return back()->with('success', $result['message']);
        }

        // MoMo sandbox đôi khi fail — vẫn cho admin đánh dấu hoàn nội bộ
        if ($request->boolean('force_manual')) {
            $tx->update(['status' => 'refunded', 'message' => 'Hoàn thủ công (MoMo lỗi: ' . $result['message'] . ')']);
            return back()->with('warning', 'MoMo lỗi, đã đánh dấu hoàn nội bộ. ' . $result['message']);
        }

        return back()->with('error', $result['message'] . ' — tick "Hoàn thủ công" nếu cần ghi nhận nội bộ.');
    }

    /** Hoàn các GD đã paid/refund_pending của đơn */
    private function refundPaidTransactions(Order $order): string
    {
        $momo = app(MomoService::class);
        $messages = [];

        $txs = $order->paymentTransactions()
            ->whereIn('status', ['paid', 'refund_pending'])
            ->whereNotIn('gateway', ['cancel_note', 'manual'])
            ->get();

        if ($txs->isEmpty()) {
            return 'Không có giao dịch tiền hàng cần hoàn (COD / chưa thu).';
        }

        foreach ($txs as $tx) {
            if ($tx->gateway === 'momo' && !empty($tx->transaction_id ?: ($tx->response_payload['transId'] ?? null))) {
                $result = $momo->refund($tx, null, 'Hoan tien tra hang don #' . $order->id);
                $messages[] = $result['message'];
                if (!$result['ok']) {
                    // Fallback: đánh dấu nội bộ để không kẹt trạng thái
                    $tx->update(['status' => 'refunded', 'message' => 'Hoàn nội bộ sau trả hàng (MoMo: ' . ($result['message'] ?? '') . ')']);
                    $messages[] = 'Đã đánh dấu hoàn nội bộ cho GD #' . $tx->id;
                }
            } else {
                $tx->update(['status' => 'refunded', 'message' => 'Hoàn nội bộ (COD / không qua MoMo)']);
                $messages[] = 'Hoàn nội bộ GD #' . $tx->id . ' (' . $tx->gateway . ')';
            }
        }

        return implode(' | ', $messages);
    }
}
