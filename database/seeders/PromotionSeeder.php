<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Promotion::updateOrCreate(
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
                'start_date'          => now(),
                'end_date'            => now()->addMonths(3),
                'is_active'           => true,
            ]
        );

        Promotion::updateOrCreate(
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
                'start_date'          => now(),
                'end_date'            => now()->addMonths(6),
                'is_active'           => true,
            ]
        );

        Promotion::updateOrCreate(
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
                'start_date'          => now(),
                'end_date'            => now()->addMonths(1),
                'is_active'           => true,
            ]
        );
    }
}
