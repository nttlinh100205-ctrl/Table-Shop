<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

/**
 * 4 mẫu cụm bàn làm việc 4 người — tham khảo truongmaisaigon.vn
 */
class ClusterDeskSeeder extends Seeder
{
    public function run()
    {
        // Danh mục
        $category = Category::firstOrCreate(
            ['name' => 'Bàn văn phòng'],
            ['description' => 'Bàn & cụm bàn làm việc văn phòng']
        );

        $sub = SubCategory::firstOrCreate(
            [
                'category_id' => $category->id,
                'name'        => 'Cụm bàn làm việc 4 người',
            ],
            ['description' => 'Module bàn nhóm 4 chỗ']
        );

        // Lấy vài màu có sẵn trong bảng colors
        $colorNames = Color::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->pluck('name')
            ->values()
            ->all();

        if (count($colorNames) < 2) {
            $colorNames = ['Oak Grey MFA-409', 'Óc chó đậm MFC-412', 'Oak Smoke MFA-411'];
        }

        $products = [
            [
                'sku'      => 'BC115',
                'name'     => 'Cụm Bàn Làm Việc 4 Người Có Vách Ngăn Kệ Sách - BC115',
                'price'    => 10000000,
                'price_old'=> 11200000,
                'material' => 'Gỗ công nghiệp Melamine + chân sắt sơn tĩnh điện',
                'style'    => 'Hiện đại, có vách ngăn & kệ sách',
                'warranty' => '12 tháng',
                'description' => 'Cụm bàn 4 người tích hợp vách ngăn và kệ sách trung tâm, tăng riêng tư và không gian lưu trữ. Phù hợp văn phòng open-space.',
                'advantages'  => "Vách ngăn giảm tiếng ồn\nKệ sách trung tâm tiện dụng\nChân sắt chắc chắn\nMặt bàn Melamine chống xước",
                'usage_guide' => 'Lắp đặt theo hướng dẫn. Không kê sát tường kín gió. Lau bề mặt bằng khăn mềm.',
                'sizes' => [
                    ['label' => 'BC115_240', 'w' => 240, 'd' => 120, 'h' => 75, 'desk' => 60, 'price' => 10000000, 'price_old' => 11200000],
                    ['label' => 'BC115_280', 'w' => 280, 'd' => 140, 'h' => 75, 'desk' => 70, 'price' => 11500000, 'price_old' => 12800000],
                ],
            ],
            [
                'sku'      => 'BC114',
                'name'     => 'Cụm Bàn Làm Việc 4 Người Khung Sắt Lưới Mới Lạ - BC114',
                'price'    => 8000000,
                'price_old'=> 8800000,
                'material' => 'Mặt gỗ công nghiệp + khung sắt lưới',
                'style'    => 'Công nghiệp, khung lưới độc đáo',
                'warranty' => '12 tháng',
                'description' => 'Thiết kế khung sắt lưới tạo điểm nhấn hiện đại, thoáng khí. Module 4 chỗ ngồi đối xứng.',
                'advantages'  => "Khung sắt lưới bền đẹp\nDễ tháo lắp module\nPhù hợp không gian trẻ trung\nGiá hợp lý",
                'usage_guide' => 'Kiểm tra ốc vít định kỳ 3–6 tháng. Tránh đặt vật nặng lệch một góc bàn.',
                'sizes' => [
                    ['label' => 'BC114_240', 'w' => 240, 'd' => 120, 'h' => 75, 'desk' => 60, 'price' => 8000000, 'price_old' => 8800000],
                    ['label' => 'BC114_260', 'w' => 260, 'd' => 120, 'h' => 75, 'desk' => 60, 'price' => 8800000, 'price_old' => 9500000],
                ],
            ],
            [
                'sku'      => 'BC113',
                'name'     => 'Cụm Bàn Làm Việc 4 Người Khung Sắt Chữ K Hiện Đại - BC113',
                'price'    => 4950000,
                'price_old'=> 5200000,
                'material' => 'Gỗ công nghiệp + chân sắt chữ K sơn tĩnh điện',
                'style'    => 'Tối giản, chân chữ K',
                'warranty' => '12 tháng',
                'description' => 'Chân sắt chữ K chắc chắn, thiết kế tối giản. Phù hợp startup và văn phòng nhỏ.',
                'advantages'  => "Chân chữ K vững chắc\nGiá cạnh tranh\nLắp đặt nhanh\nNhiều màu mặt bàn",
                'usage_guide' => 'Đặt trên nền phẳng. Không đứng lên mặt bàn.',
                'sizes' => [
                    ['label' => 'BC113_200', 'w' => 200, 'd' => 120, 'h' => 75, 'desk' => 60, 'price' => 4950000, 'price_old' => 5200000],
                    ['label' => 'BC113_240', 'w' => 240, 'd' => 120, 'h' => 75, 'desk' => 60, 'price' => 5500000, 'price_old' => 5900000],
                ],
            ],
            [
                'sku'      => 'BC111',
                'name'     => 'Cụm Bàn Làm Việc 4 Người Chữ Thập Độc Đáo - BC111',
                'price'    => 7900000,
                'price_old'=> 9950000,
                'material' => 'Melamine cao cấp + khung sắt chữ thập',
                'style'    => 'Độc đáo, bố cục chữ thập',
                'warranty' => '18 tháng',
                'description' => 'Bố cục chữ thập tạo không gian giao tiếp nhóm tốt, mỗi người có góc làm việc riêng.',
                'advantages'  => "Bố cục chữ thập sáng tạo\nTăng tương tác nhóm\nMặt bàn chống ẩm\nBảo hành 18 tháng",
                'usage_guide' => 'Cần diện tích sàn tối thiểu khoảng 3m x 3m. Lau bằng khăn ẩm, không dùng hóa chất mạnh.',
                'sizes' => [
                    ['label' => 'BC111_240', 'w' => 240, 'd' => 240, 'h' => 75, 'desk' => 60, 'price' => 7900000, 'price_old' => 9950000],
                    ['label' => 'BC111_280', 'w' => 280, 'd' => 280, 'h' => 75, 'desk' => 70, 'price' => 9200000, 'price_old' => 11500000],
                ],
            ],
        ];

        // 2–3 màu cho mỗi size
        $colorsForProduct = array_slice($colorNames, 0, min(3, count($colorNames)));

        foreach ($products as $data) {
            $sizes = $data['sizes'];
            unset($data['sizes']);

            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                array_merge($data, [
                    'category_id'     => $category->id,
                    'sub_category_id' => $sub->id,
                ])
            );

            // Xóa variant cũ của SP này rồi seed lại (tránh trùng khi chạy lại)
            $product->variants()->delete();

            foreach ($sizes as $size) {
                foreach ($colorsForProduct as $i => $colorName) {
                    $stock = 5 + ($i * 3) + ((int) substr($data['sku'], -2) % 5);
                    $product->variants()->create([
                        'size_label'    => $size['label'],
                        'width'         => $size['w'],
                        'depth'         => $size['d'],
                        'height'        => $size['h'],
                        'desktop_width' => $size['desk'],
                        'color'         => $colorName,
                        'price'         => $size['price'],
                        'price_old'     => $size['price_old'],
                        'stock'         => $stock,
                    ]);
                }
            }
        }

        $this->command?->info('Đã seed 4 cụm bàn BC115, BC114, BC113, BC111.');
    }
}
