<?php

namespace App\Services;

use App\Models\Order;

class GHNOrderService
{
    public static function codLimit(): int
    {
        return max(0, min(50000000, (int) config('services.ghn.max_cod_amount', 50000000)));
    }

    public static function codLimitMessage(): string
    {
        return 'Tiền hàng vượt hạn mức thu hộ COD ' . number_format(self::codLimit(), 0, ',', '.')
            . 'đ. Vui lòng giảm số lượng hoặc chọn thanh toán online; chỉ giao hàng sau khi xác nhận đã thanh toán.';
    }

    public static function validateCodAmount(int $amount): void
    {
        if ($amount > self::codLimit()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'payment_method' => self::codLimitMessage(),
            ]);
        }
    }

    public function __construct(private GHNService $ghn)
    {
    }

    /**
     * Tạo vận đơn GHN từ Order trong hệ thống.
     *
     * @param  bool  $isPaid  true = đã thanh toán online (MoMo)
     *
     * Logic (giống đơn #13):
     * - total_price trên Order = chỉ tiền hàng
     * - MoMo ($isPaid = true):
     *     + Đã trả tiền hàng qua MoMo → cod_amount = 0
     *     + payment_type_id = 2 → Người nhận trả phí ship khi nhận hàng
     * - COD ($isPaid = false):
     *     + cod_amount = tiền hàng
     *     + payment_type_id = 2 → Người nhận trả phí ship khi nhận hàng
     */
    public function create(Order $order, bool $isPaid = false): array
    {
        if (!$isPaid && (int) round((float) $order->total_price) > self::codLimit()) {
            return ['code' => 422, 'message' => self::codLimitMessage(), 'data' => null];
        }
        $order->loadMissing('items.product');

        $items  = [];
        $weight = 0;
        $defaultWeight = (int) config('services.ghn.default_weight', 25000);

        foreach ($order->items as $item) {
            // Cùng quy ước với GHNController@getShippingFee
            $itemWeight = (int) ($item->product->weight ?? $defaultWeight);
            if ($itemWeight < 1000) {
                $itemWeight = $defaultWeight; // tránh nhầm đơn vị gram quá nhỏ
            }
            $qty     = (int) $item->quantity;
            $weight += $itemWeight * $qty;

            $items[] = [
                'name'     => $item->product->name ?? 'Sản phẩm',
                'quantity' => $qty,
                'price'    => (int) $item->price,
                'weight'   => $itemWeight,
            ];
        }

        // Làm sạch SĐT (chỉ còn số)
        $phone = preg_replace('/\D+/', '', (string) $order->phone);

        // Cùng packageParameters với lúc tính phí trên checkout → phí đồng nhất
        $finalWeight = $weight > 0 ? $weight : $defaultWeight;
        $finalWeight = min($finalWeight, 50000);
        $package     = $this->ghn->packageParameters($finalWeight);

        // total_price = chỉ tiền hàng
        $goodsAmount = max(0, (int) round((float) $order->total_price));

        // Luôn để người nhận trả phí ship
        $paymentTypeId = 2;
        // MoMo đã trả tiền hàng → COD = 0; COD thì thu tiền hàng
        $codAmount     = $isPaid ? 0 : $goodsAmount;

        // Thông tin kho gửi và trả hàng (Table-Store shop 217485)
        $fromName     = (string) config('services.ghn.from_name', 'Table-Store');
        $fromPhone    = (string) config('services.ghn.from_phone', '0346222645');
        $fromAddress  = (string) config('services.ghn.from_address', '43/58 Trần Bình, Phường Mai Dịch, Quận Cầu Giấy, Hà Nội');
        $fromWardCode = (string) config('services.ghn.from_ward_code', '1A0603');
        $fromDistrict = (int) config('services.ghn.from_district_id', 1485);

        $payload = [
            'payment_type_id'    => $paymentTypeId,
            'note'               => 'Don hang #' . $order->id . ' - Cho xem hang, khong thu',
            'required_note'      => 'CHOXEMHANGKHONGTHU', // Cho xem hàng, không thử
            'from_name'          => $fromName,
            'from_phone'         => $fromPhone,
            'from_address'       => $fromAddress,
            'from_ward_code'     => $fromWardCode,
            'from_district_id'   => $fromDistrict,
            'return_phone'       => $fromPhone,
            'return_address'     => $fromAddress,
            'return_ward_code'   => $fromWardCode,
            'return_district_id' => $fromDistrict,
            'to_name'            => $order->name,
            'to_phone'           => $phone,
            'to_address'         => $order->address,
            'to_ward_code'       => (string) $order->to_ward_code,
            'to_district_id'     => (int) $order->to_district_id,
            'cod_amount'         => $codAmount,
            'weight'             => (int) $package['weight'],
            'length'             => (int) $package['length'],
            'width'              => (int) $package['width'],
            'height'             => (int) $package['height'],
            'service_type_id'    => (int) $package['service_type_id'],
            'items'              => $items,
        ];

        $res = $this->ghn->createOrder($payload);

        // Chỉ thử lại lỗi kết nối hoặc lỗi truy vấn kho tạm thời từ GHN.
        if (($res['code'] ?? null) !== 200 && $this->isRetryableFailure($res)) {
            usleep(500000); // 0.5s
            $res = $this->ghn->createOrder($payload);
        }

        return $res;
    }

    private function isRetryableFailure(array $response): bool
    {
        if ((int) ($response['code'] ?? 0) < 0) {
            return true;
        }

        return ($response['data']['code_message'] ?? null) === 'WAREHOUSE_NOT_FOUND';
    }
}
