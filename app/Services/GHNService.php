<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GHNService
{
    protected string $baseUrl;
    protected string $token;
    protected int $shopId;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.ghn.base_url'), '/');
        $this->token   = (string) (config('services.ghn.token') ?? '');
        $this->shopId  = (int) config('services.ghn.shop_id', 0);
    }

    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withOptions([
                'verify' => filter_var(config('services.ghn.verify_ssl', true), FILTER_VALIDATE_BOOLEAN),
            ])
            ->acceptJson()
            ->timeout(15)
            ->withHeaders([
                'Token'        => $this->token,
                'ShopId'       => (string) $this->shopId,
                'Content-Type' => 'application/json',
            ]);
    }

    /** Lấy Tỉnh/Thành */
    public function getProvinces(): array
    {
        return $this->get('/master-data/province');
    }

    /** Lấy Quận/Huyện theo tỉnh */
    public function getDistricts(int $provinceId): array
    {
        return $this->get('/master-data/district', [
            'province_id' => $provinceId,
        ]);
    }

    /** Lấy Phường/Xã theo quận */
    public function getWards(int $districtId): array
    {
        return $this->get('/master-data/ward', [
            'district_id' => $districtId,
        ]);
    }

    /**
     * Tham số gói hàng — dùng chung tính phí + tạo vận đơn.
     *
     * GHN sandbox (dev) hay báo "Cân nặng không hợp lệ" với weight ≥ 20kg / service 5.
     * → Dùng service_type_id = 2, cap weight < 20.000g để API fee luôn chạy được trên test.
     */
    public function packageParameters(int $weight): array
    {
        $defaultWeight = (int) config('services.ghn.default_weight', 15000);
        $w = $weight > 0 ? $weight : $defaultWeight;
        if ($w < 1000) {
            $w = $defaultWeight;
        }
        // Cap dưới 20kg + service 2 (ổn định trên môi trường dev)
        $w = min($w, 19999);

        return [
            'service_type_id' => 2,
            'weight'          => $w,
            'length'          => 50,
            'width'           => 40,
            'height'          => 40,
        ];
    }

    /** Tính phí giao hàng */
    public function calculateFee(array $params): array
    {
        return $this->post('/v2/shipping-order/fee', array_merge([
            'shop_id' => $this->shopId,
        ], $params));
    }

    /** Tạo vận đơn GHN */
    public function createOrder(array $orderData): array
    {
        return $this->post('/v2/shipping-order/create', array_merge([
            'shop_id' => $this->shopId,
        ], $orderData));
    }

    /** Hủy vận đơn */
    public function cancelOrder(array $orderCodes): array
    {
        return $this->post('/v2/switch-status/cancel', [
            'order_codes' => $orderCodes,
            'shop_id'     => $this->shopId,
        ]);
    }

    protected function get(string $uri, array $query = []): array
    {
        try {
            $response = $this->client()->get($uri, $query);

            if (!$response->successful()) {
                Log::warning('GHN GET failed', [
                    'uri'    => $uri,
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);

                return [
                    'code'    => $response->status(),
                    'message' => 'GHN API request failed.',
                    'data'    => null,
                ];
            }

            return $response->json() ?? [
                'code'    => -1,
                'message' => 'GHN returned an empty response.',
                'data'    => null,
            ];
        } catch (ConnectionException $e) {
            Log::error('Unable to connect to GHN', [
                'uri'   => $uri,
                'error' => $e->getMessage(),
            ]);

            return [
                'code'    => -1,
                'message' => 'Unable to connect to GHN.',
                'data'    => null,
            ];
        }
    }

    protected function post(string $uri, array $payload): array
    {
        try {
            $response = $this->client()->post($uri, $payload);

            if (!$response->successful()) {
                Log::warning('GHN POST failed', [
                    'uri'    => $uri,
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);

                return [
                    'code'    => $response->status(),
                    'message' => 'GHN API request failed.',
                    'data'    => $response->json(),
                ];
            }

            return $response->json() ?? [
                'code'    => -1,
                'message' => 'GHN returned an empty response.',
                'data'    => null,
            ];
        } catch (ConnectionException $e) {
            Log::error('Unable to connect to GHN', [
                'uri'   => $uri,
                'error' => $e->getMessage(),
            ]);

            return [
                'code'    => -1,
                'message' => 'Unable to connect to GHN.',
                'data'    => null,
            ];
        }
    }
}
