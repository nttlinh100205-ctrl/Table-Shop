<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\ProductVariant;
use App\Services\GHNOrderService;
use App\Services\GHNService;
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
    public function store(Request $request, GHNOrderService $ghnOrder)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => ['required', 'string', 'regex:/^(0[3|5|7|8|9])[0-9]{8}$/'],
            'address'        => 'required|string|max:500',
            'to_district_id' => 'required|integer',
            'to_ward_code'   => 'required|string',
            'shipping_fee'   => 'nullable|integer|min:0',
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

        $subtotal    = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $shippingFee = (int) ($validated['shipping_fee'] ?? 0);
        $totalPrice  = $subtotal + $shippingFee;
        $method      = $validated['payment_method'];

        try {
            $order = DB::transaction(function () use ($validated, $cart, $totalPrice, $shippingFee, $method) {
                $order = Order::create([
                    'user_id'         => Auth::id(),
                    'name'            => $validated['name'],
                    'phone'           => $validated['phone'],
                    'address'         => $validated['address'],
                    'total_price'     => $totalPrice,
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

                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'gateway'  => $method,
                    'amount'   => $totalPrice,
                    'status'   => 'pending',
                    'message'  => $method === 'cod' ? 'Thanh toán khi nhận hàng' : null,
                ]);

                return $order;
            });

            session()->forget('cart');

            if ($method === 'momo') {
                return redirect()->route('user.orders.momo.start', $order);
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
    public function orderHistory()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product', 'paymentTransactions'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.payment.orders', compact('orders'));
    }

    /** Chi tiết đơn */
    public function show(Order $order)
    {
        abort_unless($order->user_id === Auth::id() || (Auth::user() && Auth::user()->isAdmin()), 403);

        $order->load(['items.product', 'paymentTransactions']);

        return view('user.payment.show', compact('order'));
    }

    /** Hủy đơn */
    public function cancel(Order $order, GHNService $ghn)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $allowed = ['pending', 'ready_to_pick'];
        if (!in_array($order->shipping_status, $allowed, true)) {
            return back()->with('error', 'Đơn hàng không còn ở trạng thái có thể hủy.');
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
}
