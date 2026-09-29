<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\ProductVariant;
use App\Services\GHNOrderService;
use App\Services\GHNService;
use App\Services\MomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /** Trang thanh toán (checkout) */
    public function index()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('user.cart.index')
                ->with('error', 'Giỏ hàng đang trống.');
        }

        $totalPrice = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);

        return view('user.payment.index', compact('cart', 'totalPrice'));
    }

    /**
     * Đặt hàng: COD hoặc MoMo.
     * - COD  → tạo đơn + PaymentTransaction(cod) + tạo vận đơn GHN ngay
     * - MoMo → tạo đơn + PaymentTransaction(momo) → chuyển sang MomoController@start
     */
    public function store(Request $request, GHNOrderService $ghnOrder, MomoService $momo)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => ['required', 'string', 'regex:/^(0[3|5|7|8|9])[0-9]{8}$/'],
            'address'        => 'required|string|max:500',
            'to_district_id' => 'required|integer',
            'to_ward_code'   => 'required|string',
            'shipping_fee'   => 'required|integer|min:1',
            'payment_method' => 'required|in:cod,momo',
        ], [
            'name.required'           => 'Vui lòng nhập họ tên người nhận.',
            'phone.required'          => 'Vui lòng nhập số điện thoại.',
            'phone.regex'             => 'Số điện thoại phải đủ 10 số (VD: 0912345678).',
            'address.required'        => 'Vui lòng nhập địa chỉ nhận hàng.',
            'to_district_id.required' => 'Vui lòng chọn quận/huyện.',
            'to_ward_code.required'   => 'Vui lòng chọn phường/xã.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in'       => 'Phương thức thanh toán không hợp lệ.',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('user.cart.index')
                ->with('error', 'Giỏ hàng đang trống.');
        }

        // total_price = chỉ tiền hàng (giống đơn #13)
        // Phí ship lưu riêng ở ghn_total_fee, thu khi nhận hàng
        $subtotal    = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $shippingFee = (int) ($validated['shipping_fee'] ?? 0);
        $goodsAmount = (int) round((float) $subtotal);
        $method      = $validated['payment_method'];

        try {
            $order = DB::transaction(function () use ($validated, $cart, $goodsAmount, $shippingFee, $method) {
                $order = Order::create([
                    'user_id'         => Auth::id(),
                    'name'            => $validated['name'],
                    'phone'           => $validated['phone'],
                    'address'         => $validated['address'],
                    'total_price'     => $goodsAmount, // chỉ tiền hàng
                    'status'          => 'pending',
                    'shipping_status' => 'pending',
                    'ghn_total_fee'   => $shippingFee,
                    'to_district_id'  => $validated['to_district_id'],
                    'to_ward_code'    => $validated['to_ward_code'],
                ]);

                foreach ($cart as $item) {
                    $color     = null;
                    $sizeLabel = null;
                    $variantId = $item['variant_id'] ?? null;

                    if ($variantId) {
                        $variant = ProductVariant::find($variantId);
                        if ($variant) {
                            $color     = $variant->color;
                            $sizeLabel = $variant->size_label
                                ?: $variant->size_button_label
                                ?: $variant->dimensions;
                        }
                    }

                    OrderItem::create([
                        'order_id'           => $order->id,
                        'product_id'         => $item['id'],
                        'product_variant_id' => $variantId,
                        'color'              => $color ?? ($item['color'] ?? null),
                        'size_label'         => $sizeLabel ?? ($item['size_label'] ?? null),
                        'quantity'           => (int) $item['quantity'],
                        'price'              => (float) $item['price'],
                    ]);
                }

                // MoMo/COD đều ghi nhận chỉ tiền hàng
                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'gateway'  => $method,
                    'amount'   => $goodsAmount,
                    'status'   => 'pending',
                    'message'  => $method === 'cod'
                        ? 'COD: thu tiền hàng, phí ship thu khi nhận'
                        : 'MoMo: chỉ thu tiền hàng, phí ship thu khi nhận',
                ]);

                return $order->fresh();
            });

            session()->forget('cart');

            // ===== MoMo: gọi API ngay → chuyển sang trang thanh toán MoMo =====
            if ($method === 'momo') {
                $tx = PaymentTransaction::where('order_id', $order->id)
                    ->where('gateway', 'momo')
                    ->latest('id')
                    ->first();

                if (!$tx) {
                    $tx = PaymentTransaction::create([
                        'order_id' => $order->id,
                        'gateway'  => 'momo',
                        'amount'   => (int) round((float) $order->total_price),
                        'status'   => 'pending',
                        'message'  => 'MoMo: chỉ thu tiền hàng, phí ship thu khi nhận',
                    ]);
                }

                $result = $momo->createPayment($order, $tx);

                if (!empty($result['payUrl'])) {
                    return redirect()->away($result['payUrl']);
                }

                $errMsg = $result['message'] ?? 'MoMo không trả link thanh toán';
                Log::warning('MoMo payUrl missing after order store', [
                    'order_id' => $order->id,
                    'result'   => $result,
                ]);

                return redirect()->route('user.orders.show', $order)
                    ->with('error', 'Đã tạo đơn #' . $order->id . ' nhưng chưa mở được MoMo: ' . $errMsg
                        . '. Hãy bấm "Thanh toán lại bằng MoMo" trên trang đơn hàng.');
            }

            // COD: tạo vận đơn GHN ngay
            $order->load('items.product');
            $ghnRes = $ghnOrder->create($order, false);

            $ghnMessage = null;
            if (($ghnRes['code'] ?? null) === 200 && !empty($ghnRes['data']['order_code'])) {
                $order->update([
                    'status'          => 'cod_ordered',
                    'ghn_order_code'  => $ghnRes['data']['order_code'],
                    'shipping_status' => 'ready_to_pick',
                    'ghn_total_fee'   => (int) ($ghnRes['data']['total_fee'] ?? $shippingFee),
                ]);
            } else {
                Log::warning('GHN create order failed (COD)', ['order_id' => $order->id, 'res' => $ghnRes]);
                $order->update(['status' => 'cod_ordered']);
                $ghnMessage = $ghnRes['data']['code_message_value']
                    ?? ($ghnRes['data']['message'] ?? null)
                    ?? ($ghnRes['message'] ?? null)
                    ?? 'GHN không trả mã vận đơn (kiểm tra Token/ShopId/địa chỉ).';
                if (is_array($ghnMessage)) {
                    $ghnMessage = json_encode($ghnMessage, JSON_UNESCAPED_UNICODE);
                }
            }

            $redirect = redirect()->route('user.orders.show', $order)
                ->with('success', 'Đặt hàng COD thành công!' . ($order->ghn_order_code ? ' Mã GHN: ' . $order->ghn_order_code : ''));

            if ($ghnMessage && !$order->ghn_order_code) {
                $redirect->with('warning', 'Chưa tạo được vận đơn GHN: ' . $ghnMessage);
            }

            return $redirect;
        } catch (\Throwable $e) {
            Log::error('Order store error', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Lỗi đặt hàng: ' . $e->getMessage());
        }
    }

    /** Lịch sử đơn hàng */
    public function orderHistory(Request $request)
    {
        $filter = $request->query('status', 'all');

        $base = Order::where('user_id', Auth::id());

        $counts = [
            'all'       => (clone $base)->count(),
            'unpaid'    => (clone $base)->where('status', 'pending')->whereNull('ghn_order_code')->count(),
            'shipping'  => (clone $base)->whereIn('shipping_status', ['ready_to_pick', 'picking', 'delivering', 'processing'])->where('status', '!=', 'cancelled')->count(),
            'done'      => (clone $base)->whereIn('status', ['paid', 'cod_ordered', 'completed'])->whereIn('shipping_status', ['delivered', 'ready_to_pick', 'picking', 'delivering'])->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
        ];

        $query = Order::where('user_id', Auth::id())->with(['items.product']);

        switch ($filter) {
            case 'unpaid':
                $query->where('status', 'pending')->whereNull('ghn_order_code');
                break;
            case 'shipping':
                $query->where('status', '!=', 'cancelled')
                    ->whereIn('shipping_status', ['ready_to_pick', 'picking', 'delivering', 'processing']);
                break;
            case 'done':
                $query->where(function ($q) {
                    $q->whereIn('status', ['paid', 'cod_ordered', 'completed'])
                      ->orWhere('shipping_status', 'delivered');
                });
                break;
            case 'cancelled':
                $query->where('status', 'cancelled');
                break;
            default:
                $filter = 'all';
        }

        $orders = $query->orderByDesc('created_at')->paginate(8)->withQueryString();

        return view('user.payment.orders', compact('orders', 'filter', 'counts'));
    }

    /** Chi tiết đơn */
    public function show(Order $order)
    {
        abort_unless($order->user_id === Auth::id() || (Auth::user() && Auth::user()->isAdmin()), 403);

        $order->load(['items.product']);
        if (method_exists($order, 'paymentTransactions')) {
            $order->load('paymentTransactions');
        }

        return view('user.payment.show', compact('order'));
    }

    /** Hủy đơn */
    public function cancel(Request $request, Order $order, GHNService $ghn)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        if ($order->status === 'cancel_requested') {
            return back()->with('error', 'Yêu cầu hủy đơn đang chờ admin xử lý.');
        }

        $paidMomo = $order->paymentTransactions()
            ->where('gateway', 'momo')
            ->where('status', 'paid')
            ->latest()
            ->first();

        $allowed = $paidMomo ? ['pending', 'processing', 'ready_to_pick'] : ['pending', 'ready_to_pick'];
        if (!in_array($order->shipping_status, $allowed, true)) {
            return back()->with('error', 'Đơn hàng không còn ở trạng thái có thể hủy.');
        }

        if ($paidMomo) {
            $validated = $request->validate([
                'cancel_reason' => ['required', 'string', 'min:10', 'max:1000'],
            ], [
                'cancel_reason.required' => 'Vui lòng nhập lý do hủy đơn.',
                'cancel_reason.min' => 'Lý do hủy cần ít nhất 10 ký tự.',
            ]);

            $order->update([
                'status'                 => 'cancel_requested',
                'cancel_reason'          => $validated['cancel_reason'],
                'cancel_previous_status' => $order->status,
                'cancel_admin_note'      => null,
                'cancel_requested_at'    => now(),
                'cancel_processed_at'    => null,
            ]);

            return back()->with('success', 'Đã gửi yêu cầu hủy đơn. Tiền chỉ được hoàn nếu admin chấp nhận.');
        }

        if ($order->ghn_order_code) {
            $response = $ghn->cancelOrder([$order->ghn_order_code]);
            if (($response['code'] ?? null) !== 200) {
                return back()->with('error', 'GHN không cho phép hủy vận đơn này.');
            }
        }

        $order->update([
            'status'          => 'cancelled',
            'shipping_status' => 'cancelled',
        ]);

        return back()->with('success', 'Đơn hàng đã được hủy.');
    }

    /**
     * Yêu cầu trả hàng (xem hàng không ưng → trả).
     * Phí ship chưa thu (thu khi nhận) → user không mất ship.
     * Tiền hàng đã trả (MoMo) → admin duyệt hoàn tiền.
     */
    public function requestReturn(Request $request, Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        if (!$order->canRequestReturn()) {
            return back()->with('error', 'Đơn hàng hiện không thể yêu cầu trả hàng.');
        }

        $validated = $request->validate([
            'return_reason' => 'required|string|min:10|max:1000',
        ], [
            'return_reason.required' => 'Vui lòng ghi lý do trả hàng.',
            'return_reason.min'      => 'Lý do cần ít nhất 10 ký tự.',
        ]);

        $order->update([
            'return_status'       => 'requested',
            'return_reason'       => $validated['return_reason'],
            'return_requested_at' => now(),
            'shipping_status'     => 'return', // tab "Hoàn hàng" trên admin
        ]);

        return back()->with('success', 'Đã gửi yêu cầu trả hàng. Shop sẽ xác nhận sớm. Phí ship không thu nếu bạn từ chối nhận.');
    }

    /**
     * Chuyển đơn MoMo chưa thanh toán sang COD và tạo vận đơn GHN ngay.
     */
    public function switchToCod(Order $order, GHNOrderService $ghnOrder)
    {
        abort_unless($order->user_id === Auth::id() || (Auth::user() && Auth::user()->isAdmin()), 403);

        if (in_array($order->status, ['paid', 'completed', 'cancelled'], true) || !empty($order->ghn_order_code)) {
            return back()->with('error', 'Đơn hàng không thể chuyển sang COD.');
        }

        $order->loadMissing('items.product');

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'cod_ordered',
            ]);

            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway'  => 'cod',
                'amount'   => (int) round((float) $order->total_price),
                'status'   => 'pending',
                'message'  => 'Chuyển từ MoMo sang COD: thu tiền hàng khi nhận hàng',
            ]);
        });

        // Tạo vận đơn GHN (isPaid = false -> COD amount = tiền hàng)
        $ghnRes = $ghnOrder->create($order, false);

        if (($ghnRes['code'] ?? null) === 200 && !empty($ghnRes['data']['order_code'])) {
            $order->update([
                'ghn_order_code'  => $ghnRes['data']['order_code'],
                'shipping_status' => 'ready_to_pick',
                'ghn_total_fee'   => (int) ($ghnRes['data']['total_fee'] ?? $order->ghn_total_fee),
            ]);

            return back()->with('success', 'Đã chuyển sang thanh toán COD và tạo vận đơn GHN thành công! Mã GHN: ' . $ghnRes['data']['order_code']);
        }

        $errMsg = $ghnRes['data']['code_message_value']
            ?? ($ghnRes['data']['message'] ?? null)
            ?? ($ghnRes['message'] ?? 'Lỗi không xác định từ GHN');

        return back()->with('warning', 'Đã chuyển sang COD nhưng chưa tạo được vận đơn GHN: ' . $errMsg);
    }
}
