<?php

use App\Models\Promotion;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Chèn các mã khuyến mãi mặc định nếu chưa tồn tại trong hệ thống
        Promotion::firstOrCreate(
            ['code' => 'GIAM20'],
            [
                'name'                => 'Ưu đãi giảm 20% cho khách hàng mới',
                'description'         => 'Giảm 20% giá trị đơn hàng, tối đa 100.000đ cho đơn hàng từ 200.000đ.',
                'discount_type'       => 'percent',
                'discount_value'      => 20,
                'max_discount_amount' => 100000,
                'min_order_amount'    => 200000,
                'usage_limit'         => 100,
                'used_count'          => 0,
                'start_date'          => now()->subDay(),
                'end_date'            => now()->addMonths(6),
                'is_active'           => true,
            ]
        );

        Promotion::firstOrCreate(
            ['code' => 'BANMOI50K'],
            [
                'name'                => 'Voucher giảm ngay 50.000đ mua bàn ghế',
                'description'         => 'Giảm trực tiếp 50.000đ tiền mặt cho đơn hàng bàn ghế từ 500.000đ.',
                'discount_type'       => 'fixed',
                'discount_value'      => 50000,
                'max_discount_amount' => null,
                'min_order_amount'    => 500000,
                'usage_limit'         => 50,
                'used_count'          => 0,
                'start_date'          => now()->subDay(),
                'end_date'            => now()->addMonths(6),
                'is_active'           => true,
            ]
        );

        Promotion::firstOrCreate(
            ['code' => 'FREESHIP100'],
            [
                'name'                => 'Giảm 100.000đ tri ân khách hàng thân thiết',
                'description'         => 'Giảm 100.000đ tiền hàng cho đơn từ 1.000.000đ.',
                'discount_type'       => 'fixed',
                'discount_value'      => 100000,
                'max_discount_amount' => null,
                'min_order_amount'    => 1000000,
                'usage_limit'         => 30,
                'used_count'          => 0,
                'start_date'          => now()->subDay(),
                'end_date'            => now()->addMonths(3),
                'is_active'           => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Promotion::whereIn('code', ['GIAM20', 'BANMOI50K', 'FREESHIP100'])->delete();
    }
};
