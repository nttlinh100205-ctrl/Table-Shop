<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    public function run()
    {
        $colors = [
            // --- Gỗ Sồi (Oak) — mã MFA khớp file ảnh ---
            ['name' => 'Oak Light MFA-402',  'code' => 'MFA-402', 'hex' => '#EDE0C8', 'group' => 'van_go', 'sort_order' => 1],
            ['name' => 'Oak Nature MFA-407',  'code' => 'MFA-407', 'hex' => '#D9C8A9', 'group' => 'van_go', 'sort_order' => 2],
            ['name' => 'Oak Brown MFA-408',  'code' => 'MFA-408', 'hex' => '#C9A66B', 'group' => 'van_go', 'sort_order' => 3],
            ['name' => 'Oak Grey MFA-409',  'code' => 'MFA-409', 'hex' => '#B8956C', 'group' => 'van_go', 'sort_order' => 4],
            ['name' => 'Oak Smoke MFA-411',  'code' => 'MFA-411', 'hex' => '#C4AE8A', 'group' => 'van_go', 'sort_order' => 5],
            ['name' => 'Oak Snow MFA-415',  'code' => 'MFA-415', 'hex' => '#D4C4A8', 'group' => 'van_go', 'sort_order' => 6],

            ['name' => 'Óc chó nâu nhạt MFC-403', 'code' => 'MFC-403', 'hex' => '#5D4037', 'group' => 'van_go', 'sort_order' => 7],
            ['name' => 'Óc chó xám nâu MFC-409', 'code' => 'MFC-409', 'hex' => '#6B4E3D', 'group' => 'van_go', 'sort_order' => 8],
            ['name' => 'Óc chó xám đậm MFC-411', 'code' => 'MFC-411', 'hex' => '#3E2723', 'group' => 'van_go', 'sort_order' => 9],
            ['name' => 'Óc chó đậm MFC-412', 'code' => 'MFC-412', 'hex' => '#6D4C41', 'group' => 'van_go', 'sort_order' => 10],
            ['name' => 'Óc chó Coffe MFC-417', 'code' => 'MFC-417', 'hex' => '#8D6E63', 'group' => 'van_go', 'sort_order' => 11],

            // --- Vân gỗ khác ---
            ['name' => 'XGT Nâu Ấm',      'code' => 'MFA-VGT07',   'hex' => '#D4A84B', 'group' => 'van_go', 'sort_order' => 11],
            ['name' => 'VGT Tự Nhiên',       'code' => 'MFA-VGT01',    'hex' => '#9A8F7A', 'group' => 'van_go', 'sort_order' => 12],
            ['name' => 'Sồi Tráng Vàng',            'code' => 'MFA-SO103',  'hex' => '#F5F5F0', 'group' => 'van_go', 'sort_order' => 13],
            ['name' => 'Lim xanh O-liu',     'code' => 'MFA-LN111', 'hex' => '#EDE8DF', 'group' => 'van_go', 'sort_order' => 14],
            ['name' => 'Gỗ đỏ nguyên bản',              'code' => 'MFA-OS20',    'hex' => '#2B2B2B', 'group' => 'van_go', 'sort_order' => 15],
            ['name' => 'Hương Chocolate',         'code' => 'MFA-S08',   'hex' => '#C8C8C8', 'group' => 'van_go', 'sort_order' => 16],
            ['name' => 'Hương Vân',          'code' => 'MFA-S02',   'hex' => '#6B6B6B', 'group' => 'van_go', 'sort_order' => 17],

            // --- Sơn tĩnh điện (chân sắt, khung) ---
            ['name' => 'Trắng sơn',        'code' => 'ST-TRANG',  'hex' => '#FFFFFF', 'group' => 'son_tinh_dien', 'sort_order' => 20],
            ['name' => 'Đen sơn',          'code' => 'ST-DEN',    'hex' => '#1A1A1A', 'group' => 'son_tinh_dien', 'sort_order' => 21],
            ['name' => 'Ghi xám',          'code' => 'ST-GHI',    'hex' => '#7A7A7A', 'group' => 'son_tinh_dien', 'sort_order' => 22],
            ['name' => 'Xám bạc',          'code' => 'ST-BAC',    'hex' => '#A8A8A8', 'group' => 'son_tinh_dien', 'sort_order' => 23],
            ['name' => 'Xanh đen',         'code' => 'ST-XANHD',  'hex' => '#1C2B3A', 'group' => 'son_tinh_dien', 'sort_order' => 24],
            ['name' => 'Nâu sơn',          'code' => 'ST-NAU',    'hex' => '#4A3728', 'group' => 'son_tinh_dien', 'sort_order' => 25],

            // --- Sơn vân bông ---
            ['name' => 'Vân bông HTP_927',      'code' => 'HTP_927',    'hex' => '#8E8E8E', 'group' => 'son_van_bong', 'sort_order' => 30],
            ['name' => 'Vân bông HTP_928',      'code' => 'HTP_928',    'hex' => '#3D3D3D', 'group' => 'son_van_bong', 'sort_order' => 31],
            ['name' => 'Vân bông HTP_929',     'code' => 'HTP_929',   'hex' => '#4A6FA5', 'group' => 'son_van_bong', 'sort_order' => 32],

            // --- Vải / Lưới (ghế) ---
            ['name' => 'Đen lưới',         'code' => 'VL-DEN',    'hex' => '#222222', 'group' => 'vai', 'sort_order' => 40],
            ['name' => 'Xám lưới',         'code' => 'VL-XAM',    'hex' => '#8A8A8A', 'group' => 'vai', 'sort_order' => 41],
            ['name' => 'Xanh lưới',        'code' => 'VL-XANH',   'hex' => '#3A5F7A', 'group' => 'vai', 'sort_order' => 42],
            ['name' => 'Đỏ đô',            'code' => 'VL-DO',     'hex' => '#6B1E2A', 'group' => 'vai', 'sort_order' => 43],
            ['name' => 'Nâu vải',          'code' => 'VL-NAU',    'hex' => '#5C4033', 'group' => 'vai', 'sort_order' => 44],
              
            // Đá ốp bàn
            ['name' => 'Đá phú sơn',         'code' => 'VCT-01',  'hex' => '#F5F5F0', 'group' => 'da', 'sort_order' => 45],
            ['name' => 'Đá kim sa',        'code' => 'VCT-02',  'hex' => '#F5F5F0', 'group' => 'da', 'sort_order' => 46],
            ['name' => 'Đá nung kết',         'code' => 'VCT-03',  'hex' => '#F5F5F0', 'group' => 'da', 'sort_order' => 47],
            ['name' => 'Đá Vicostone',        'code' => 'VCT-04',  'hex' => '#F5F5F0', 'group' => 'da', 'sort_order' => 48],


        ];

        foreach ($colors as $c) {
            Color::updateOrCreate(
                ['code' => $c['code']],
                array_merge($c, ['is_active' => true])
            );
        }
    }
}
