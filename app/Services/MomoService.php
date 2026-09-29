<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomoService
{
    /**
     * Tạo yêu cầu thanh toán MoMo, trả về JSON (có payUrl nếu thành công).
     */
    public function createPayment(Order $order, PaymentTransaction $transaction): array
    {
        $endpoint    = config('services.momo.endpoint', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = (string) config('services.momo.partner_code', '');
        $accessKey   = (string) config('services.momo.access_key', '');
        $secretKey   = (string) config('services.momo.secret_key', '');

        // Dùng url() theo host đang truy cập (127.0.0.1:8000) thay vì APP_URL=localhost
        $redirectUrl = config('services.momo.redirect_url') ?: url('/payment/momo/callback');
        $ipnUrl      = config('services.momo.ipn_url') ?: url('/payment/momo/ipn');

        $orderInfo = 'Thanh toan don hang #' . $order->id;
        // MoMo chỉ thu tiền hàng (total_price = tiền hàng, không gồm ship)
        $amount    = (string) max(1000, (int) round((float) $order->total_price));
        $orderId   = $order->id . '_' . $transaction->id . '_' . time();
        $extraData = (string) $order->id;
        $requestId = (string) (time() . $transaction->id);

        // payWithMethod: Cổng thanh toán tổng hợp MoMo (QR Code, App MoMo, Thẻ ATM NAPAS, Thẻ quốc tế Visa/Master)
        // captureWallet: Quét mã QR MoMo và ứng dụng MoMo
        // payWithATM: Cổng thanh toán thẻ ATM nội địa
        $requestTypes = ['payWithMethod', 'captureWallet', 'payWithATM'];

        $lastResult = [];
        foreach ($requestTypes as $requestType) {
            $rawHash = 'accessKey=' . $accessKey
                . '&amount=' . $amount
                . '&extraData=' . $extraData
                . '&ipnUrl=' . $ipnUrl
                . '&orderId=' . $orderId
                . '&orderInfo=' . $orderInfo
                . '&partnerCode=' . $partnerCode
                . '&redirectUrl=' . $redirectUrl
                . '&requestId=' . $requestId
                . '&requestType=' . $requestType;

            $data = [
                'partnerCode' => $partnerCode,
                'partnerName' => config('app.name', 'Store'),
                'storeId'     => 'MomoStore',
                'requestId'   => $requestId,
                'amount'      => $amount,
                'orderId'     => $orderId,
                'orderInfo'   => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl'      => $ipnUrl,
                'lang'        => 'vi',
                'extraData'   => $extraData,
                'requestType' => $requestType,
                'autoCapture' => true,
                'signature'   => hash_hmac('sha256', $rawHash, $secretKey),
            ];

            $transaction->update([
                'gateway_order_id' => $orderId,
                'request_payload'  => $data,
            ]);

            try {
                $response = Http::withOptions([
                    'verify' => filter_var(config('services.momo.verify_ssl', false), FILTER_VALIDATE_BOOLEAN),
                ])->timeout(25)->asJson()->post($endpoint, $data);

                $result = $response->json() ?? [];
                if (!is_array($result)) {
                    $result = ['message' => (string) $response->body(), 'resultCode' => $response->status()];
                }
            } catch (\Throwable $e) {
                Log::error('MoMo createPayment connection error', [
                    'error'        => $e->getMessage(),
                    'request_type' => $requestType,
                ]);
                $result = [
                    'resultCode' => -1,
                    'message'    => 'Không kết nối được MoMo: ' . $e->getMessage(),
                ];
            }

            Log::info('MoMo createPayment response', [
                'order_id'     => $order->id,
                'request_type' => $requestType,
                'result_code'  => $result['resultCode'] ?? null,
                'message'      => $result['message'] ?? null,
                'has_pay_url'  => isset($result['payUrl']),
                'redirect_url' => $redirectUrl,
                'amount'       => $amount,
            ]);

            $lastResult = $result;

            if (!empty($result['payUrl'])) {
                $transaction->update([
                    'response_payload' => $result,
                    'result_code'      => isset($result['resultCode']) ? (int) $result['resultCode'] : null,
                    'message'          => $result['message'] ?? null,
                    'status'           => 'initiated',
                ]);

                return $result;
            }

            // Đổi requestId/orderId cho lần thử kế tiếp (MoMo không cho trùng)
            $requestId = (string) (time() . $transaction->id . rand(10, 99));
            $orderId   = $order->id . '_' . $transaction->id . '_' . time() . rand(10, 99);
        }

        $transaction->update([
            'response_payload' => $lastResult,
            'result_code'      => isset($lastResult['resultCode']) ? (int) $lastResult['resultCode'] : null,
            'message'          => $lastResult['message'] ?? 'MoMo không trả payUrl',
            'status'           => 'failed',
        ]);

        return $lastResult;
    }

    public function isSuccessful(array $payload): bool
    {
        return (string) ($payload['resultCode'] ?? '') === '0';
    }

    /**
     * Hoàn tiền qua MoMo API (full hoặc partial).
     * Endpoint: POST /v2/gateway/api/refund
     *
     * @return array{ok: bool, result: array, message: string}
     */
    public function refund(PaymentTransaction $transaction, ?int $amount = null, string $description = ''): array
    {
        $partnerCode = (string) config('services.momo.partner_code', '');
        $accessKey   = (string) config('services.momo.access_key', '');
        $secretKey   = (string) config('services.momo.secret_key', '');

        $createEndpoint = (string) config('services.momo.endpoint', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $refundEndpoint = str_replace('/create', '/refund', $createEndpoint);
        if (!str_contains($refundEndpoint, '/refund')) {
            $refundEndpoint = 'https://test-payment.momo.vn/v2/gateway/api/refund';
        }

        $transId = $transaction->transaction_id;
        if (empty($transId)) {
            // Thử lấy từ response_payload
            $transId = $transaction->response_payload['transId'] ?? null;
        }
        if (empty($transId)) {
            return [
                'ok'      => false,
                'result'  => [],
                'message' => 'Thiếu transId MoMo — không gọi hoàn tiền được.',
            ];
        }

        $refundAmount = $amount !== null
            ? max(1000, $amount)
            : max(1000, (int) round((float) $transaction->amount));

        $requestId = 'RF' . time() . $transaction->id . rand(10, 99);
        $orderId   = 'RF' . $transaction->order_id . '_' . $transaction->id . '_' . time();
        $description = $description !== '' ? $description : ('Hoan tien don #' . $transaction->order_id);

        $rawHash = 'accessKey=' . $accessKey
            . '&amount=' . $refundAmount
            . '&description=' . $description
            . '&orderId=' . $orderId
            . '&partnerCode=' . $partnerCode
            . '&requestId=' . $requestId
            . '&transId=' . $transId;

        $payload = [
            'partnerCode' => $partnerCode,
            'orderId'     => $orderId,
            'requestId'   => $requestId,
            'amount'      => $refundAmount,
            'transId'     => (int) $transId,
            'lang'        => 'vi',
            'description' => $description,
            'signature'   => hash_hmac('sha256', $rawHash, $secretKey),
        ];

        try {
            $response = Http::withOptions([
                'verify' => filter_var(config('services.momo.verify_ssl', false), FILTER_VALIDATE_BOOLEAN),
            ])->timeout(30)->asJson()->post($refundEndpoint, $payload);

            $result = $response->json() ?? [];
            if (!is_array($result)) {
                $result = ['message' => (string) $response->body(), 'resultCode' => $response->status()];
            }
        } catch (\Throwable $e) {
            Log::error('MoMo refund connection error', ['error' => $e->getMessage()]);
            return [
                'ok'      => false,
                'result'  => [],
                'message' => 'Không kết nối được MoMo refund: ' . $e->getMessage(),
            ];
        }

        Log::info('MoMo refund response', [
            'order_id'    => $transaction->order_id,
            'tx_id'       => $transaction->id,
            'result_code' => $result['resultCode'] ?? null,
            'message'     => $result['message'] ?? null,
        ]);

        $ok = (string) ($result['resultCode'] ?? '') === '0';

        // Ghi nhận giao dịch hoàn
        $refundTx = PaymentTransaction::create([
            'order_id'         => $transaction->order_id,
            'gateway'          => 'momo',
            'gateway_order_id' => $orderId,
            'transaction_id'   => $result['transId'] ?? null,
            'amount'           => $refundAmount,
            'status'           => $ok ? 'refunded' : 'failed',
            'result_code'      => isset($result['resultCode']) ? (int) $result['resultCode'] : null,
            'message'          => $result['message'] ?? null,
            'request_payload'  => $payload,
            'response_payload' => $result,
            'paid_at'          => $ok ? now() : null,
        ]);

        if ($ok) {
            $transaction->update([
                'status'  => 'refunded',
                'message' => 'Đã hoàn tiền qua MoMo (refund_tx#' . $refundTx->id . ')',
            ]);
        }

        return [
            'ok'      => $ok,
            'result'  => $result,
            'message' => $ok
                ? ('Hoàn tiền MoMo thành công: ' . number_format($refundAmount, 0, ',', '.') . 'đ')
                : ('MoMo từ chối hoàn: ' . ($result['message'] ?? 'unknown')),
            'refund_tx_id' => $refundTx->id,
        ];
    }

    public function markPaid(PaymentTransaction $transaction, array $payload): void
    {
        $transaction->update([
            'transaction_id'   => $payload['transId'] ?? null,
            'result_code'      => (int) ($payload['resultCode'] ?? 0),
            'message'          => $payload['message'] ?? null,
            'response_payload' => $payload,
            'status'           => 'paid',
            'paid_at'          => Carbon::now(),
        ]);
    }

    public function markFailed(PaymentTransaction $transaction, array $payload): void
    {
        $transaction->update([
            'transaction_id'   => $payload['transId'] ?? null,
            'result_code'      => isset($payload['resultCode']) ? (int) $payload['resultCode'] : null,
            'message'          => $payload['message'] ?? null,
            'response_payload' => $payload,
            'status'           => 'failed',
        ]);
    }

    public function isValidSuccessfulResponse(array $payload): bool
    {
        return $this->isValidResponse($payload) && $this->isSuccessful($payload);
    }

    public function isValidResponse(array $payload): bool
    {
        if (!isset($payload['signature'])) {
            return false;
        }

        $accessKey = (string) config('services.momo.access_key', '');
        $secretKey = (string) config('services.momo.secret_key', '');

        $rawHash = 'accessKey=' . $accessKey
            . '&amount=' . ($payload['amount'] ?? '')
            . '&extraData=' . ($payload['extraData'] ?? '')
            . '&message=' . ($payload['message'] ?? '')
            . '&orderId=' . ($payload['orderId'] ?? '')
            . '&orderInfo=' . ($payload['orderInfo'] ?? '')
            . '&orderType=' . ($payload['orderType'] ?? '')
            . '&partnerCode=' . ($payload['partnerCode'] ?? '')
            . '&payType=' . ($payload['payType'] ?? '')
            . '&requestId=' . ($payload['requestId'] ?? '')
            . '&responseTime=' . ($payload['responseTime'] ?? '')
            . '&resultCode=' . ($payload['resultCode'] ?? '')
            . '&transId=' . ($payload['transId'] ?? '');

        return hash_equals(
            hash_hmac('sha256', $rawHash, $secretKey),
            (string) $payload['signature']
        );
    }

    public function orderId(array $payload): ?int
    {
        $orderId = $payload['extraData'] ?? null;

        return is_numeric($orderId) ? (int) $orderId : null;
    }
}
