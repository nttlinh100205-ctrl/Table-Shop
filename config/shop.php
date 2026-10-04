<?php

/*
|--------------------------------------------------------------------------
| Thông tin cửa hàng "Nội Thất Tinh Hoa"
|--------------------------------------------------------------------------
| Sửa thông tin THẬT tại đây (hoặc đặt trong .env / Render Environment).
| Header, footer, banner tư vấn và khối đánh giá đều đọc từ file này.
*/

return [
    'name'    => env('SHOP_NAME', 'Nội Thất Tinh Hoa'),
    'tagline' => env('SHOP_TAGLINE', 'Gỗ đẹp cho nhà'),
    'about'   => 'Xưởng mộc chế tác bàn gỗ tự nhiên cho phòng ăn, phòng khách, văn phòng và quán cafe. Mỗi mặt bàn được chọn thớ, bào và hoàn thiện thủ công.',

    // Liên hệ
    'hotline'       => env('SHOP_HOTLINE', '0123 456 789'),
    'zalo'          => env('SHOP_ZALO', '0123456789'),          // số Zalo, viết liền
    'email'         => env('SHOP_EMAIL', 'support@store.vn'),
    'address'       => env('SHOP_ADDRESS', 'Hồ Chí Minh, Việt Nam'),
    'map_url'       => env('SHOP_MAP_URL', 'https://maps.google.com/?q=Ho+Chi+Minh'),

    // Giờ làm việc
    'hours' => [
        ['Thứ Hai – Thứ Bảy', '08:00 – 20:00'],
        ['Chủ Nhật',          '09:00 – 17:00'],
    ],

    // Mạng xã hội (để trống '' sẽ tự ẩn icon)
    'social' => [
        'facebook'  => env('SHOP_FACEBOOK', 'https://facebook.com/'),
        'instagram' => env('SHOP_INSTAGRAM', 'https://instagram.com/'),
        'tiktok'    => env('SHOP_TIKTOK', 'https://tiktok.com/'),
        'youtube'   => env('SHOP_YOUTUBE', ''),
    ],

    // Đánh giá khách hàng (thay bằng đánh giá thật của shop)
    'testimonials' => [
        [
            'quote' => 'Mặt bàn óc chó vân rất đẹp, cạnh bo mềm tay. Xưởng tư vấn kích thước kỹ nên đặt vào phòng ăn vừa khít.',
            'name'  => 'Chị Thu Hà',
            'meta'  => 'Bàn ăn oval · Quận 7',
        ],
        [
            'quote' => 'Đặt bàn làm việc theo kích thước góc phòng, giao đúng hẹn và lắp đặt gọn gàng. Gỗ chắc, không mùi sơn.',
            'name'  => 'Anh Minh Khoa',
            'meta'  => 'Bàn làm việc · Thủ Đức',
        ],
        [
            'quote' => 'Quán mình dùng bàn tròn gỗ tự nhiên cho cả sân vườn, sau nhiều tháng vẫn giữ màu đẹp, khách khen hoài.',
            'name'  => 'Chị Ngọc Lan',
            'meta'  => 'Bàn cafe · Bình Thạnh',
        ],
    ],
];
