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
            ['Quần jean nam', 'Quần', 350000],
            ['Áo thun nữ', 'Áo', 250000],
            ['Phụ kiện thời trang', 'Phụ kiện', 150000],
            ['Áo sơ mi nam', 'Áo', 300000],
            ['Quần short nữ', 'Quần', 280000],
            ['Balo mini', 'Phụ kiện', 400000],
            ['Áo khoác jean', 'Áo', 500000],
            ['Vòng tay đá', 'Phụ kiện', 180000],
            ['Quần tây nam', 'Quần', 320000],
            ['Áo hoodie', 'Áo', 450000],
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
