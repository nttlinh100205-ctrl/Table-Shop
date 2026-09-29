<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Bàn văn phòng', 'Ghế văn phòng', 'Tủ - Kệ', 'Sofa - Ghế thư giãn'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
