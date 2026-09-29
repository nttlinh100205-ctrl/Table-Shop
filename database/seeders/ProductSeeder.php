<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['Bàn làm việc BC111', 'Bàn văn phòng', 3500000],
            ['Bàn văn phòng BC113', 'Bàn văn phòng', 2800000],
            ['Cụm bàn 4 người BC114', 'Bàn văn phòng', 8500000],
            ['Cụm bàn 6 người BC115', 'Bàn văn phòng', 12000000],
            ['Ghế lưới GX698D', 'Ghế văn phòng', 1800000],
            ['Ghế xoay văn phòng BLV135', 'Ghế văn phòng', 2200000],
            ['Ghế giám đốc BT26', 'Ghế văn phòng', 3500000],
            ['Tủ hồ sơ 3 ngăn', 'Tủ - Kệ', 1500000],
            ['Kệ sách văn phòng', 'Tủ - Kệ', 900000],
            ['Sofa phòng khách 3 chỗ', 'Sofa - Ghế thư giãn', 5500000],
        ];

        foreach ($products as [$name, $categoryName, $price]) {
            $category = Category::firstOrCreate(['name' => $categoryName]);
            Product::firstOrCreate(
                ['name' => $name, 'category_id' => $category->id],
                ['price' => $price],
            );
        }
    }
}
