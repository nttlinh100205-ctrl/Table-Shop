<?php

return [
    'referral_points' => 10000,
    /*
    |--------------------------------------------------------------------------
    | Tỷ lệ tích luỹ điểm từ đơn hàng
    |--------------------------------------------------------------------------
    | 1 điểm cho mỗi 10 VNĐ giá trị đơn hàng (Ví dụ: 3.000.000đ = 300.000 điểm)
    */
    'earn_rate' => 10,

    /*
    |--------------------------------------------------------------------------
    | Thời hạn sử dụng của điểm (ngày)
    |--------------------------------------------------------------------------
    | Điểm có hạn 1 năm kể từ ngày nhận
    */
    'point_lifetime_days' => 365,

    /*
    |--------------------------------------------------------------------------
    | Bảng phân hạng thành viên (Dựa trên TỔNG điểm tích lũy trọn đời)
    |--------------------------------------------------------------------------
    */
    'tiers' => [
        'bronze' => [
            'name'        => 'Thành viên Đồng',
            'min_points'  => 0,
            'color'       => '#a87957',
            'badge_class' => 'bg-secondary',
            'description' => 'Hạng mặc định cho tất cả thành viên mới đăng ký',
        ],
        'silver' => [
            'name'        => 'Thành viên Bạc',
            'min_points'  => 200000, // Tích lũy 2.000.000đ tiền hàng
            'color'       => '#94a3b8',
            'badge_class' => 'bg-light text-dark border',
            'description' => 'Được đổi gói voucher ưu đãi từ hạng Bạc',
        ],
        'gold' => [
            'name'        => 'Thành viên Vàng',
            'min_points'  => 600000, // Tích lũy 6.000.000đ tiền hàng
            'color'       => '#d97706',
            'badge_class' => 'bg-warning text-dark',
            'description' => 'Đổi voucher giá trị cao với tỷ lệ điểm ưu đãi',
        ],
        'diamond' => [
            'name'        => 'Thành viên Kim Cương',
            'min_points'  => 1500000, // Tích lũy 15.000.000đ tiền hàng
            'color'       => '#0284c7',
            'badge_class' => 'bg-info text-white',
            'description' => 'Đặc quyền tối cao: Đổi các voucher cao cấp nhất với tỷ lệ tốt nhất',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Danh mục các gói đổi điểm sang voucher
    |--------------------------------------------------------------------------
    | - Hạng cao hơn đổi được gói lớn hơn hoặc có tỷ lệ điểm ưu đãi hơn.
    | - Điểm trừ theo FIFO (lô điểm cũ trừ trước).
    | - Voucher sinh ra gắn riêng cho user đó, dùng 1 lần, có hạn sử dụng.
    */
    'voucher_packages' => [
        'pkg_50k' => [
            'id'              => 'pkg_50k',
            'name'            => 'Voucher Giảm 50.000đ',
            'points_required' => 150000,
            'discount_value'  => 50000,
            'min_order'       => 200000,
            'min_tier'        => 'bronze',
            'valid_days'      => 30,
            'badge'           => 'Phổ thông',
        ],
        'pkg_100k' => [
            'id'              => 'pkg_100k',
            'name'            => 'Voucher Giảm 100.000đ',
            'points_required' => 300000, // Ví dụ: 300.000 điểm = giảm 100.000đ
            'discount_value'  => 100000,
            'min_order'       => 400000,
            'min_tier'        => 'bronze',
            'valid_days'      => 30,
            'badge'           => 'Ưa chuộng',
        ],
        'pkg_200k' => [
            'id'              => 'pkg_200k',
            'name'            => 'Voucher Giảm 200.000đ',
            'points_required' => 550000, // Tỷ lệ tốt hơn: 550.000 thay vì 600.000
            'discount_value'  => 200000,
            'min_order'       => 800000,
            'min_tier'        => 'silver',
            'valid_days'      => 45,
            'badge'           => 'Hạng Bạc trở lên',
        ],
        'pkg_500k' => [
            'id'              => 'pkg_500k',
            'name'            => 'Voucher Giảm 500.000đ',
            'points_required' => 1300000, // Tỷ lệ tốt hơn: 1.300.000 thay vì 1.500.000
            'discount_value'  => 500000,
            'min_order'       => 2000000,
            'min_tier'        => 'gold',
            'valid_days'      => 60,
            'badge'           => 'Hạng Vàng trở lên',
        ],
        'pkg_1000k' => [
            'id'              => 'pkg_1000k',
            'name'            => 'Voucher Giảm 1.000.000đ',
            'points_required' => 2500000, // Tỷ lệ tốt hơn: 2.500.000 thay vì 3.000.000
            'discount_value'  => 1000000,
            'min_order'       => 4000000,
            'min_tier'        => 'diamond',
            'valid_days'      => 90,
            'badge'           => 'Đặc quyền Kim Cương',
        ],
    ],
];
