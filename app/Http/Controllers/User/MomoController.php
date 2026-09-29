<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\GHNOrderService;
use App\Services\MomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MomoController extends Controller
{
    /** Bắt đầu thanh toán MoMo (lần đầu sau khi đặt hàng) */
    public function start(Order $order, MomoService $momo)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (in_array($order->status, ['paid', 'cod_ordered', 'cancelled'], true)) {
            return redirect()->route('user.orders.show', $order)
                ->with('error', 'Đơn hàng không thể thanh toán MoMo.');
        }

        return $this->redirectToMomo($order, $this->newTransaction($order), $momo);
    }

    /** Thanh toán lại (không tạo đơn mới) */
    public function payAgain(Order $order, MomoService $momo)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (in_array($order->status, ['paid', 'cod_ordered', 'cancelled'], true)) {
            return redirect()->route('user.orders.show', $order)
                ->with('error', 'Đơn hàng không cần thanh toán lại.');
        }

        return $this->redirectToMomo($order, $this->newTransaction($order), $momo);
    }

    /** Callback khi khách quay về từ MoMo (browser redirect) */
    public function callback(Request $request, GHNOrderService $ghnOrders, MomoService $momo)
    {
        Log::info('MoMo callback received', [
            'payload'       => $request->except('signature'),
            'has_signature' => $request->has('signature'),
        ]);

        if (!$momo->isValidSuccessfulResponse($request->all())) {
            Log::warning('MoMo callback rejected', [
                'result_code'     => $request->input('resultCode'),
                'order_id'        => $request->input('orderId'),
                'signature_valid' => $momo->isValidResponse($request->all()),
            ]);
            if ($momo->isValidResponse($request->all())) {
                $this->markFailed($request->all(), $momo);
            }

            $orderId = $momo->orderId($request->all());
            $order = $orderId ? Order::find($orderId) : null;
            $resultCode = (string) $request->input('resultCode');
            $msg = $resultCode === '1006'
                ? 'Giao dịch MoMo đã được hủy. Bạn có thể thanh toán lại bằng MoMo hoặc chuyển sang nhận hàng COD.'
                : 'Thanh toán MoMo chưa hoàn tất (' . ($request->input('message') ?: 'Mã ' . $resultCode) . '). Bạn có thể thanh toán lại hoặc chuyển sang COD.';

            if ($order) {
                return redirect()->route('user.orders.show', $order)->with('warning', $msg);
            }

            return redirect()->route('user.orders.index')->with('warning', $msg);
        }

        $result  = $this->completePayment($request->all(), $ghnOrders, $momo);
        $message = in_array($result, ['created', 'already_created'], true)
            ? 'Thanh toán MoMo thành công! Vận đơn GHN đã được khởi tạo.'
            : 'Thanh toán MoMo thành công! Đơn hàng đang được chuẩn bị tạo vận đơn GHN.';

        $orderId = $momo->orderId($request->all());
        $order = $orderId ? Order::find($orderId) : null;
        if ($order) {
            return redirect()->route('user.orders.show', $order)->with('success', $message);
        }

        return redirect()->route('user.orders.index')->with('success', $message);
    }

    /** IPN từ server MoMo (không auth, không CSRF) */
    public function ipn(Request $request, GHNOrderService $ghnOrders, MomoService $momo)
    {
        Log::info('MoMo IPN received', [
            'payload'       => $request->except('signature'),
            'has_signature' => $request->has('signature'),
        ]);

        if ($momo->isValidSuccessfulResponse($request->all())) {
            $this->completePayment($request->all(), $ghnOrders, $momo);
        } elseif ($momo->isValidResponse($request->all())) {
            $this->markFailed($request->all(), $momo);
        }

        return response()->json(['message' => 'Received']);
    }

    private function newTransaction(Order $order): PaymentTransaction
    {
        // MoMo chỉ thu tiền hàng (total_price = tiền hàng)
        return PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway'  => 'momo',
            'amount'   => (int) round((float) $order->total_price),
            'status'   => 'pending',
            'message'  => 'MoMo: chỉ thu tiền hàng, phí ship thu khi nhận',
        ]);
    }

    private function redirectToMomo(Order $order, PaymentTransaction $transaction, MomoService $momo)
    {
        $result = $momo->createPayment($order, $transaction);

        if (!empty($result['payUrl'])) {
            return redirect()->away($result['payUrl']);
        }

        $msg = $result['message'] ?? 'lỗi không xác định';
        $code = $result['resultCode'] ?? '';

        return redirect()->route('user.orders.show', $order)
            ->with('error', 'Không mở được trang MoMo'
                . ($code !== '' ? " (mã {$code})" : '')
                . ': ' . $msg);
    }

    private function completePayment(array $payload, GHNOrderService $ghnOrders, MomoService $momo): string
    {
        $result = DB::transaction(function () use ($payload, $momo) {
            $transaction = PaymentTransaction::where('gateway', 'momo')
                ->where('gateway_order_id', $payload['orderId'] ?? '')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                return 'invalid';
            }

            $order = Order::lockForUpdate()->find($transaction->order_id);
            if (!$order) {
                return 'invalid';
            }

            if ($order->ghn_order_code) {
                return 'already_created';
            }

            if ($transaction->status === 'paid' && !$order->ghn_order_code) {
                return ['create', $order->id];
            }

            if ($transaction->status === 'paid') {
                return 'already_paid';
            }

            if ($order->shipping_status === 'processing') {
                return 'processing';
            }

            if ((int) $transaction->amount !== (int) ($payload['amount'] ?? 0)) {
                $momo->markFailed($transaction, $payload);
                return 'invalid';
            }

            $order->update([
                'status'          => 'paid',
                'shipping_status' => 'processing',
            ]);

            $momo->markPaid($transaction, $payload);

            return ['create', $order->id];
        });

        if (!is_array($result)) {
            return (string) $result;
        }

        $order    = Order::with('items.product')->find($result[1]);
        $response = $ghnOrders->create($order, true); // isPaid = true → COD amount = 0

        if (isset($response['code'])
            && (int) $response['code'] === 200
            && !empty($response['data']['order_code'])) {
            $order->update([
                'ghn_order_code'  => $response['data']['order_code'],
                'shipping_status' => 'ready_to_pick',
                'ghn_total_fee'   => (int) ($response['data']['total_fee'] ?? $order->ghn_total_fee),
            ]);

            return 'created';
        }

        Log::error('GHN order failed after MoMo payment', [
            'order_id' => $order->id,
            'response' => $response,
        ]);
        $order->update(['shipping_status' => 'pending']);

        return 'failed';
    }

    private function markFailed(array $payload, MomoService $momo): void
    {
        $transaction = PaymentTransaction::where('gateway', 'momo')
            ->where('gateway_order_id', $payload['orderId'] ?? '')
            ->first();

        if ($transaction && $transaction->status !== 'paid') {
            $momo->markFailed($transaction, $payload);
        }
    }
}
