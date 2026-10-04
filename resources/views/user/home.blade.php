{{-- resources/views/user/home.blade.php --}}
@extends('layouts.app')

@section('title', 'Nội Thất Tinh Hoa — Bàn Gỗ Tự Nhiên Cao Cấp Cho Mọi Không Gian')

@section('content')

@php
    $colorMap = \App\Models\Color::query()->get()->keyBy('name');

    // Đảm bảo số sản phẩm chia hết cho số cột (4 cột desktop, 2 cột mobile)
    $totalCount = $products->count();
    if ($totalCount >= 4) {
        $displayCount = intdiv($totalCount, 4) * 4;
        $gridProducts = $products->take($displayCount);
    } else {
        $gridProducts = $products;
    }

    $spaces = [
        [
            'title'    => 'Phòng Ăn Gia Đình',
            'subtitle' => 'Nơi ấm lửa bữa cơm sum vầy',
            'desc'     => 'Mặt bàn oval và chữ nhật từ gỗ óc chó nguyên khối, cạnh bo tròn êm dịu, an toàn cho trẻ nhỏ.',
            'category' => 59,
            'image'    => asset('images/home/phong-an.jpg'),
        ],
        [
            'title'    => 'Không Gian Làm Việc',
            'subtitle' => 'Kiến tạo sự tập trung & vị thế',
            'desc'     => 'Bàn giám đốc, bàn làm việc chân sắt tối giản với thớ vân gỗ cuốn hút, khơi nguồn cảm hứng mỗi ngày.',
            'category' => 55,
            'image'    => asset('images/home/van-phong.jpg'),
        ],
        [
            'title'    => 'Phòng Khách Sang Trọng',
            'subtitle' => 'Điểm nhấn nghệ thuật trung tâm',
            'desc'     => 'Bàn trà sofa dáng tròn, chân tiện điêu khắc kinh điển nâng tầm không gian tiếp khách thanh tao.',
            'category' => 57,
            'image'    => asset('images/home/phong-khach.jpg'),
        ],
        [
            'title'    => 'Cafe & Sân Vườn',
            'subtitle' => 'Góc trà thư thái đón nắng trời',
            'desc'     => 'Gỗ tếch và sồi ngoài trời qua xử lý dầu khoáng chống ẩm, giữ nét mộc mạc bên hiên xanh mát.',
            'category' => 58,
            'image'    => asset('images/home/cafe-san-vuon.jpg'),
        ],
    ];

    $testimonials = config('shop.testimonials', [
        [
            'quote' => 'Mặt bàn óc chó vân rất đẹp, cạnh bo mềm tay. Xưởng tư vấn kích thước kỹ nên đặt vào phòng ăn vừa khít.',
            'name'  => 'Chị Thu Hà',
            'meta'  => 'Bàn ăn oval · Quận 7',
        ],
        [
            'quote' => 'Đặt bàn làm việc theo kích thước góc phòng, giao đúng hẹn và lắp đặt gọn gàng. Gỗ chắc, không mùi sơn nồng.',
            'name'  => 'Anh Minh Khoa',
            'meta'  => 'Bàn làm việc · Thủ Đức',
        ],
        [
            'quote' => 'Quán mình dùng bàn tròn gỗ tự nhiên cho cả sân vườn, sau nhiều tháng vẫn giữ màu đẹp, khách khen hoài.',
            'name'  => 'Chị Ngọc Lan',
            'meta'  => 'Bàn cafe · Bình Thạnh',
        ],
    ]);
@endphp

{{-- 1. HERO BANNER CÓ ANIMATION --}}
<x-hero />

{{-- 2. THANH CAM KẾT 4 Ý PHONG CÁCH SANG TRỌNG --}}
<x-commitments />

{{-- 3. BỘ SƯU TẬP SẢN PHẨM (Lưới 4 cột Desktop / 2 cột Mobile) --}}
<section class="nth-section" id="product-section">
    <div class="nth-container">
        {{-- Tiêu đề phần sản phẩm --}}
        <div class="nth-section-head">
            <div class="nth-section-head__left">
                <span class="nth-section-kicker">BỘ SƯU TẬP CHẾ TÁC</span>
                <h2 class="nth-section-title">
                    @if(request('q'))
                        Kết quả tìm kiếm cho "{{ request('q') }}"
                    @elseif(!empty($currentCategory))
                        {{ $currentCategory->name }}
                    @else
                        Những Mẫu Bàn Được Tuyển Chọn
                    @endif
                </h2>
            </div>

            <div class="nth-section-head__right">
                @if(request('q') || request('category') || request('sub_category') || request('sub_sub_category'))
                    <a href="{{ route('user.home') }}#product-section" class="nth-btn-clear-filter">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                        <span>Xem tất cả mẫu</span>
                    </a>
                @else
                    <div class="nth-section-head__filter">
                        <a href="{{ route('user.home') }}#product-section" class="nth-filter-tag {{ !request('category') ? 'is-active' : '' }}">Tất cả</a>
                        <a href="{{ route('user.home', ['category' => 59]) }}#product-section" class="nth-filter-tag {{ request('category') == 59 ? 'is-active' : '' }}">Bàn ăn</a>
                        <a href="{{ route('user.home', ['category' => 55]) }}#product-section" class="nth-filter-tag {{ request('category') == 55 ? 'is-active' : '' }}">Bàn làm việc</a>
                        <a href="{{ route('user.home', ['category' => 57]) }}#product-section" class="nth-filter-tag {{ request('category') == 57 ? 'is-active' : '' }}">Bàn trà sofa</a>
                        <a href="{{ route('user.home', ['category' => 58]) }}#product-section" class="nth-filter-tag {{ request('category') == 58 ? 'is-active' : '' }}">Bàn cafe</a>
                    </div>
                @endif

                {{-- Cụm icon < và > chuyển sản phẩm khác --}}
                <div class="nth-slider-head-nav">
                    <button type="button" class="nth-head-arrow nth-head-arrow--prev" id="nth-prod-prev" aria-label="Xem sản phẩm trước" title="Sản phẩm trước">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <span class="nth-head-counter" id="nth-prod-counter">1 / 6</span>
                    <button type="button" class="nth-head-arrow nth-head-arrow--next" id="nth-prod-next" aria-label="Xem sản phẩm kế tiếp" title="Sản phẩm kế tiếp">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Dải trượt sản phẩm mượt mà với icon < và > chuyển sản phẩm khác --}}
        @if($products->count() > 0)
            <div class="nth-slider-wrapper">
                {{-- Nút mũi tên nổi bên trái < --}}
                <button type="button" class="nth-slider-floating-btn nth-slider-floating-btn--prev" id="nth-float-prev" aria-label="Xem sản phẩm trước" title="Sản phẩm trước">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>

                {{-- Khung cuộn sản phẩm mượt mà, không bị cắt xén lơ lửng --}}
                <div class="nth-product-slider" id="nth-product-slider">
                    @foreach($products as $product)
                        <div class="nth-product-slide">
                            <x-product-card :product="$product" :colorMap="$colorMap" />
                        </div>
                    @endforeach
                </div>

                {{-- Nút mũi tên nổi bên phải > --}}
                <button type="button" class="nth-slider-floating-btn nth-slider-floating-btn--next" id="nth-float-next" aria-label="Xem sản phẩm kế tiếp" title="Sản phẩm kế tiếp">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>

            {{-- Thanh chấm chỉ báo trang --}}
            <div class="nth-slider-footer">
                <div class="nth-slider-dots" id="nth-slider-dots"></div>
            </div>
        @else
            <div class="nth-empty-state">
                <div class="nth-empty-state__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
                <h3 class="nth-empty-state__title">Chưa tìm thấy mẫu bàn phù hợp</h3>
                <p class="nth-empty-state__desc">Quý khách vui lòng thử tìm với từ khóa khác hoặc liên hệ để được xưởng may đo theo yêu cầu.</p>
                <a href="{{ route('user.home') }}#product-section" class="nth-btn-primary">
                    Xem toàn bộ bộ sưu tập
                </a>
            </div>
        @endif
    </div>
</section>

{{-- 4. KHỐI CHIA THEO KHÔNG GIAN SỐNG (4 Ảnh lớn Portrait) --}}
<section class="nth-section nth-section--alt" id="khong-gian">
    <div class="nth-container">
        <div class="nth-section-head nth-section-head--center">
            <span class="nth-section-kicker">KHÔNG GIAN NỘI THẤT</span>
            <h2 class="nth-section-title">Chọn Chiếc Bàn Hoàn Hảo Cho Từng Góc Nhà</h2>
            <p class="nth-section-desc">
                Mỗi không gian sống đều có nhịp điệu và công năng riêng. Chúng tôi thiết kế các mẫu bàn tương thích trọn vẹn với trải nghiệm sinh hoạt của gia đình.
            </p>
        </div>

        <div class="nth-spaces-grid">
            @foreach($spaces as $space)
                <a href="{{ route('user.home', ['category' => $space['category']]) }}#product-section" class="nth-space-card">
                    <div class="nth-space-card__img-wrap">
                        <img src="{{ $space['image'] }}" alt="{{ $space['title'] }}" loading="lazy">
                        <div class="nth-space-card__overlay"></div>
                    </div>
                    <div class="nth-space-card__content">
                        <span class="nth-space-card__sub">{{ $space['subtitle'] }}</span>
                        <h3 class="nth-space-card__title">{{ $space['title'] }}</h3>
                        <p class="nth-space-card__desc">{{ $space['desc'] }}</p>
                        <span class="nth-space-card__link">
                            Khám phá bộ sưu tập
                            <svg width="18" height="8" viewBox="0 0 18 8" fill="none" stroke="currentColor" stroke-width="1.2">
                                <path d="M0 4h16M12 1l4 3-4 3"/>
                            </svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- 5. KHỐI CÂU CHUYỆN XƯỞNG GỖ & CHẤT LIỆU --}}
<section class="nth-section nth-story" id="xuong-go">
    <div id="triet-ly" style="position: relative; top: -80px; visibility: hidden;"></div>
    <div class="nth-container">
        <div class="nth-story__grid">
            {{-- Cột ảnh xưởng --}}
            <div class="nth-story__media">
                <div class="nth-story__img-wrap">
                    <img src="{{ asset('images/home/xuong-go.jpg') }}"
                         alt="Xưởng mộc chế tác thủ công Nội Thất Tinh Hoa"
                         loading="lazy">
                    <div class="nth-story__badge">
                        <span class="nth-story__badge-yr">TỪ 2012</span>
                        <span class="nth-story__badge-txt">Nghệ nhân mộc truyền thống</span>
                    </div>
                </div>
            </div>

            {{-- Cột nội dung câu chuyện --}}
            <div class="nth-story__content">
                <span class="nth-section-kicker">TRIẾT LÝ NỘI THẤT TINH HOA</span>
                <h2 class="nth-story__title">
                    Mỗi thớ gỗ mang một linh hồn,<br>
                    Mỗi chiếc bàn là một tác phẩm.
                </h2>
                <p class="nth-story__p">
                    Tại xưởng mộc <strong>Nội Thất Tinh Hoa</strong>, chúng tôi không xem chiếc bàn là một món hàng công nghiệp vô cảm. Đó là nơi cả gia đình quây quần sau một ngày dài, là nơi khởi sinh những ý tưởng tâm huyết và là chứng nhân cho bao khoảnh khắc gắn kết thiêng liêng.
                </p>
                <p class="nth-story__p">
                    Từng tấm gỗ óc chó, sồi tự nhiên đều được tuyển chọn kỹ lưỡng theo thớ vân, trải qua quá trình tẩm sấy tự nhiên và hoàn thiện bằng dầu khoáng thực vật không hóa chất độc hại — an toàn tuyệt đối cho bàn ăn gia đình và trẻ nhỏ.
                </p>

                <div class="nth-story__features">
                    <div class="nth-story-feat">
                        <span class="nth-story-feat__num">01</span>
                        <div>
                            <strong class="nth-story-feat__title">Gỗ tự nhiên loại 1</strong>
                            <p class="nth-story-feat__desc">Óc chó Bắc Mỹ &amp; sồi tuyển lọc, vân gỗ lượn sóng tự nhiên độc bản.</p>
                        </div>
                    </div>
                    <div class="nth-story-feat">
                        <span class="nth-story-feat__num">02</span>
                        <div>
                            <strong class="nth-story-feat__title">Ghép mộng âm dương</strong>
                            <p class="nth-story-feat__desc">Kết cấu vững chãi chống võng võng và chịu tải trọng bền bỉ hàng chục năm.</p>
                        </div>
                    </div>
                    <div class="nth-story-feat">
                        <span class="nth-story-feat__num">03</span>
                        <div>
                            <strong class="nth-story-feat__title">Dầu lau sinh học an toàn</strong>
                            <p class="nth-story-feat__desc">Tôn vinh chất gỗ mộc, không mùi sơn khó chịu, an toàn tuyệt đối cho bữa ăn.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 6. KHỐI ĐÁNH GIÁ KHÁCH HÀNG (Testimonials) --}}
<section class="nth-section nth-testimonials" id="danh-gia">
    <div id="trai-nghiem" style="position: relative; top: -80px; visibility: hidden;"></div>
    <div class="nth-container">
        <div class="nth-section-head nth-section-head--center">
            <span class="nth-section-kicker">TRẢI NGHIỆM KHÁCH HÀNG</span>
            <h2 class="nth-section-title">Những Câu Chuyện Bên Chiếc Bàn Gỗ</h2>
            <p class="nth-section-desc">
                Sự tin yêu của quý khách hàng chính là động lực quý báu để chúng tôi tiếp tục mài dũa từng chi tiết mộc.
            </p>
        </div>

        <div class="nth-testimonials__grid">
            @foreach($testimonials as $t)
                <div class="nth-testimonial-card">
                    <div class="nth-testimonial-card__quote-mark">“</div>
                    <p class="nth-testimonial-card__quote">{{ $t['quote'] }}</p>
                    <div class="nth-testimonial-card__author">
                        <strong class="nth-testimonial-card__name">{{ $t['name'] }}</strong>
                        <span class="nth-testimonial-card__meta">{{ $t['meta'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- 7. BANNER MỜI TƯ VẤN TRỰC TIẾP QUA ZALO / HOTLINE --}}
<section class="nth-cta-banner" id="tu-van">
    <div class="nth-container">
        <div class="nth-cta-banner__box">
            <div class="nth-cta-banner__content">
                <span class="nth-cta-banner__kicker">TƯ VẤN MAY ĐO THEO YÊU CẦU</span>
                <h2 class="nth-cta-banner__title">Cùng Kiến Tạo Chiếc Bàn Riêng Cho Không Gian Bạn</h2>
                <p class="nth-cta-banner__desc">
                    Quý khách đang băn khoăn về kích thước mặt bàn, loại vân gỗ hay kiểu chân phù hợp với thiết kế tổng thể? Hãy nhắn gửi thông tin hoặc bản vẽ phối cảnh cho xưởng để được tư vấn 1-1 nhanh chóng.
                </p>
            </div>
            <div class="nth-cta-banner__actions">
                <a href="https://zalo.me/{{ config('shop.zalo', '0123456789') }}" target="_blank" rel="noopener" class="nth-btn-zalo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.04 2 11.02c0 2.87 1.48 5.43 3.8 7.08L5 22l4.13-1.85c.91.26 1.87.4 2.87.4 5.52 0 10-4.04 10-9.02S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/>
                    </svg>
                    <span>Nhắn Zalo Tư Vấn</span>
                </a>
                <a href="tel:{{ str_replace(' ', '', config('shop.hotline', '0123456789')) }}" class="nth-btn-hotline">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    <span>Hotline: {{ config('shop.hotline', '0123 456 789') }}</span>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* ========================================================
   HOME PAGE STYLES — NỘI THẤT TINH HOA
   Palette:
     Kem:      #F3E9DC
     Nền phụ:  #FAF6F0
     Nâu gỗ:   #5A4536
     Nâu đậm:  #3F2F24
     Chữ:      #3A2E26
     Viền:     #E6D8C8
   Fonts:
     Tiêu đề:  'Cormorant Garamond', Georgia, serif
     Nội dung: 'Manrope', sans-serif
   ======================================================== */
.nth-section {
    padding: 60px 0;
    background: #FAF6F0;
    color: #3A2E26;
    font-family: 'Manrope', sans-serif;
}
.nth-section--alt {
    background: #F3E9DC;
}

/* Head */
.nth-section-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 32px;
    flex-wrap: wrap;
    gap: 16px;
}
.nth-section-head--center {
    flex-direction: column;
    align-items: center;
    text-align: center;
    max-width: 680px;
    margin-left: auto;
    margin-right: auto;
}
.nth-section-kicker {
    display: block;
    font-size: 11px;
    letter-spacing: 0.2em;
    color: #7E7065;
    font-weight: 600;
    margin-bottom: 6px;
}
.nth-section-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(26px, 3.2vw, 36px);
    font-weight: 400;
    color: #3A2E26;
    margin: 0;
    line-height: 1.15;
}
.nth-section-desc {
    font-size: 14px;
    line-height: 1.7;
    color: #7E7065;
    margin-top: 10px;
}
.nth-btn-clear-filter {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    color: #5A4536;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}
.nth-btn-clear-filter:hover {
    background: #5A4536;
    color: #FAF6F0;
    border-color: #5A4536;
}
.nth-section-head__filter {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.nth-filter-tag {
    padding: 6px 14px;
    font-size: 12px;
    border-radius: 2px;
    border: 1px solid #E6D8C8;
    background: #FAF6F0;
    color: #3A2E26;
    text-decoration: none;
    transition: all 0.2s;
}
.nth-filter-tag:hover,
.nth-filter-tag.is-active {
    background: #5A4536;
    color: #FAF6F0;
    border-color: #5A4536;
}

/* ========================================================
   PRODUCT SLIDER & CAROUSEL NAVIGATION (<, >)
   ======================================================== */
.nth-section-head__right {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

/* Cụm nút icon < và > ở đầu section */
.nth-slider-head-nav {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    padding: 3px 6px;
}
.nth-head-arrow {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    border-radius: 2px;
    color: #5A4536;
    cursor: pointer;
    transition: all 0.2s ease;
}
.nth-head-arrow:hover:not(:disabled) {
    background: #5A4536;
    color: #FAF6F0;
}
.nth-head-arrow:disabled,
.nth-head-arrow.is-disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
.nth-head-counter {
    font-size: 11.5px;
    font-weight: 700;
    color: #7E7065;
    padding: 0 6px;
    letter-spacing: 0.05em;
    min-width: 48px;
    text-align: center;
    user-select: none;
}

/* Container Slider bao bọc */
.nth-slider-wrapper {
    position: relative;
    max-width: 1240px;
    margin: 0 auto;
}

/* Dải trượt ngang sản phẩm mượt mà */
.nth-product-slider {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding: 8px 4px 16px;
}
.nth-product-slider::-webkit-scrollbar {
    display: none;
}

/* Mỗi thẻ sản phẩm căn tỉ lệ chuẩn xác, không bị cắt xén lơ lửng */
.nth-product-slide {
    flex: 0 0 calc((100% - 3 * 16px) / 4);
    min-width: 0;
    scroll-snap-align: start;
    display: flex;
}
.nth-product-slide .nth-card {
    width: 100%;
}

@media (max-width: 1199px) {
    .nth-product-slide {
        flex: 0 0 calc((100% - 2 * 16px) / 3);
    }
}
@media (max-width: 820px) {
    .nth-product-slide {
        flex: 0 0 calc((100% - 1 * 14px) / 2);
    }
}
@media (max-width: 520px) {
    .nth-product-slide {
        flex: 0 0 calc(100% - 28px);
    }
}

/* Nút mũi tên nổi 2 bên < và > */
.nth-slider-floating-btn {
    position: absolute;
    top: 40%;
    transform: translateY(-50%);
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    color: #5A4536;
    box-shadow: 0 6px 20px rgba(58, 46, 38, 0.16);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 20;
    transition: all 0.25s cubic-bezier(0.22, 0.61, 0.36, 1);
}
.nth-slider-floating-btn--prev {
    left: -22px;
}
.nth-slider-floating-btn--next {
    right: -22px;
}
.nth-slider-floating-btn:hover:not(:disabled) {
    background: #5A4536;
    color: #FAF6F0;
    border-color: #5A4536;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 8px 24px rgba(90, 69, 54, 0.28);
}
.nth-slider-floating-btn:disabled,
.nth-slider-floating-btn.is-disabled {
    opacity: 0;
    pointer-events: none;
    transform: translateY(-50%) scale(0.9);
}

@media (max-width: 1024px) {
    .nth-slider-floating-btn--prev { left: 4px; }
    .nth-slider-floating-btn--next { right: 4px; }
}
@media (max-width: 768px) {
    .nth-slider-floating-btn { display: none; }
}

/* Thanh chỉ báo bên dưới slider */
.nth-slider-footer {
    margin-top: 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.nth-slider-dots {
    display: flex;
    align-items: center;
    gap: 6px;
}
.nth-slider-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #E6D8C8;
    border: none;
    padding: 0;
    cursor: pointer;
    transition: all 0.25s ease;
}
.nth-slider-dot.is-active {
    width: 24px;
    border-radius: 4px;
    background: #5A4536;
}
.nth-slider-helper {
    font-size: 12px;
    color: #7E7065;
}

/* Empty State */
.nth-empty-state {
    padding: 64px 24px;
    text-align: center;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
}
.nth-empty-state__icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: #F3E9DC;
    color: #7E7065;
    display: flex;
    align-items: center;
    justify-content: center;
}
.nth-empty-state__title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 26px;
    font-weight: 500;
    margin: 0 0 8px;
}
.nth-empty-state__desc {
    font-size: 13.5px;
    color: #7E7065;
    margin: 0 0 24px;
}
.nth-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    background: #5A4536;
    color: #FAF6F0;
    text-decoration: none;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.08em;
    border-radius: 2px;
    transition: background 0.3s;
}
.nth-btn-primary:hover { background: #3F2F24; }

/* 4 Không Gian Sống */
.nth-spaces-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}
.nth-space-card {
    position: relative;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    overflow: hidden;
    transition: transform 0.4s cubic-bezier(0.22, 0.61, 0.36, 1),
                box-shadow 0.4s cubic-bezier(0.22, 0.61, 0.36, 1);
}
.nth-space-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 36px rgba(58, 46, 38, 0.1);
}
.nth-space-card__img-wrap {
    position: relative;
    aspect-ratio: 4 / 5;
    overflow: hidden;
    background: #EFE3D3;
}
.nth-space-card__img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.7s cubic-bezier(0.22, 0.61, 0.36, 1);
}
.nth-space-card:hover .nth-space-card__img-wrap img {
    transform: scale(1.06);
}
.nth-space-card__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(63, 47, 36, 0.55) 100%);
}
.nth-space-card__content {
    padding: 22px 20px 24px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.nth-space-card__sub {
    font-size: 11px;
    letter-spacing: 0.14em;
    color: #7E7065;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 6px;
}
.nth-space-card__title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 23px;
    font-weight: 500;
    color: #3A2E26;
    margin: 0 0 8px;
    line-height: 1.25;
}
.nth-space-card__desc {
    font-size: 12.5px;
    line-height: 1.6;
    color: #7E7065;
    margin: 0 0 16px;
}
.nth-space-card__link {
    margin-top: auto;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.08em;
    color: #5A4536;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: gap 0.25s;
}
.nth-space-card:hover .nth-space-card__link {
    gap: 12px;
}

/* Xưởng Gỗ Story */
.nth-story {
    background: #FAF6F0;
}
.nth-story__grid {
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    gap: 64px;
    align-items: center;
}
.nth-story__img-wrap {
    position: relative;
    aspect-ratio: 4 / 5;
    overflow: hidden;
    border-radius: 2px;
    border: 1px solid #E6D8C8;
    background: #EFE3D3;
}
.nth-story__img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.nth-story__badge {
    position: absolute;
    bottom: 24px;
    left: 24px;
    background: rgba(63, 47, 36, 0.92);
    color: #F3E9DC;
    padding: 12px 18px;
    border-radius: 2px;
    display: flex;
    flex-direction: column;
}
.nth-story__badge-yr {
    font-size: 11px;
    letter-spacing: 0.2em;
    font-weight: 700;
}
.nth-story__badge-txt {
    font-size: 12px;
    opacity: 0.85;
}
.nth-story__title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(32px, 4vw, 44px);
    font-weight: 400;
    line-height: 1.2;
    color: #3A2E26;
    margin: 12px 0 24px;
}
.nth-story__p {
    font-size: 14.5px;
    line-height: 1.85;
    color: #5C4F44;
    margin: 0 0 16px;
}
.nth-story__features {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #E6D8C8;
}
.nth-story-feat {
    display: flex;
    align-items: flex-start;
    gap: 18px;
}
.nth-story-feat__num {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 22px;
    font-weight: 500;
    color: #5A4536;
    line-height: 1;
}
.nth-story-feat__title {
    display: block;
    font-size: 14px;
    color: #3A2E26;
    margin-bottom: 2px;
}
.nth-story-feat__desc {
    font-size: 12.5px;
    color: #7E7065;
    margin: 0;
    line-height: 1.5;
}

/* Đánh giá (Testimonials) */
.nth-testimonials {
    background: #F3E9DC;
}
.nth-testimonials__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
}
.nth-testimonial-card {
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    padding: 36px 32px;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: transform 0.3s;
}
.nth-testimonial-card:hover {
    transform: translateY(-4px);
}
.nth-testimonial-card__quote-mark {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 52px;
    line-height: 1;
    color: #5A4536;
    opacity: 0.35;
    margin-bottom: -16px;
}
.nth-testimonial-card__quote {
    font-size: 14px;
    line-height: 1.75;
    color: #3A2E26;
    font-style: italic;
    margin: 0 0 24px;
    flex: 1;
}
.nth-testimonial-card__author {
    display: flex;
    flex-direction: column;
    border-top: 1px solid #E6D8C8;
    padding-top: 14px;
}
.nth-testimonial-card__name {
    font-size: 13.5px;
    font-weight: 600;
    color: #3A2E26;
}
.nth-testimonial-card__meta {
    font-size: 11.5px;
    color: #7E7065;
    margin-top: 2px;
}

/* Banner Tư Vấn */
.nth-cta-banner {
    padding: 32px 0 88px;
    background: #FAF6F0;
}
.nth-cta-banner__box {
    background: #3F2F24;
    color: #F3E9DC;
    border-radius: 2px;
    padding: clamp(36px, 6vw, 64px);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 40px;
    position: relative;
    overflow: hidden;
}
.nth-cta-banner__box::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 60%;
    height: 200%;
    background: radial-gradient(circle, rgba(243, 233, 220, 0.08) 0%, transparent 70%);
    pointer-events: none;
}
.nth-cta-banner__content {
    max-width: 640px;
}
.nth-cta-banner__kicker {
    display: block;
    font-size: 11.5px;
    letter-spacing: 0.2em;
    color: #C29D62;
    font-weight: 600;
    margin-bottom: 12px;
}
.nth-cta-banner__title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(28px, 3.5vw, 40px);
    font-weight: 400;
    color: #FAF6F0;
    margin: 0 0 16px;
    line-height: 1.2;
}
.nth-cta-banner__desc {
    font-size: 14px;
    line-height: 1.75;
    color: #D6C7B8;
    margin: 0;
}
.nth-cta-banner__actions {
    display: flex;
    flex-direction: column;
    gap: 14px;
    flex-shrink: 0;
}
.nth-btn-zalo,
.nth-btn-hotline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 16px 28px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-decoration: none;
    border-radius: 2px;
    white-space: nowrap;
    transition: all 0.3s ease;
}
.nth-btn-zalo {
    background: #C29D62;
    color: #3F2F24;
}
.nth-btn-zalo:hover {
    background: #d8b67b;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
}
.nth-btn-hotline {
    background: transparent;
    color: #FAF6F0;
    border: 1px solid rgba(243, 233, 220, 0.4);
}
.nth-btn-hotline:hover {
    background: rgba(243, 233, 220, 0.1);
    border-color: #FAF6F0;
}

/* ========================================================
   RESPONSIVE DESIGN
   ======================================================== */
@media (max-width: 1200px) {
    .nth-product-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }
}
@media (max-width: 991px) {
    .nth-product-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    .nth-spaces-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }
    .nth-story__grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    .nth-testimonials__grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    .nth-cta-banner__box {
        flex-direction: column;
        align-items: flex-start;
    }
    .nth-cta-banner__actions {
        width: 100%;
        flex-direction: row;
        flex-wrap: wrap;
    }
    .nth-btn-zalo, .nth-btn-hotline {
        flex: 1;
        min-width: 200px;
    }
}
@media (max-width: 575px) {
    .nth-section { padding: 56px 0; }
    .nth-product-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .nth-spaces-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .nth-cta-banner__actions {
        flex-direction: column;
    }
    .nth-btn-zalo, .nth-btn-hotline {
        width: 100%;
    }
}

/* Giảm chuyển động */
@media (prefers-reduced-motion: reduce) {
    .nth-card, .nth-card:hover,
    .nth-space-card, .nth-space-card:hover,
    .nth-space-card__img-wrap img,
    .nth-btn-quick-add,
    .nth-testimonial-card {
        transition: none !important;
        transform: none !important;
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Đóng popup kích thước khi click ra ngoài
    document.addEventListener('click', function () {
        document.querySelectorAll('.nth-size-popup.open').forEach(p => p.classList.remove('open'));
    });

    document.querySelectorAll('.nth-card').forEach(function (card) {
        const productId = card.dataset.productId;
        let variants = [];
        try { variants = JSON.parse(card.dataset.variants || '[]'); } catch (e) {}

        const quickAddBtn = card.querySelector('.nth-btn-quick-add');
        const popup = card.querySelector('.nth-size-popup');
        const colorDots = card.querySelectorAll('.nth-swatch');

        function selectedColor() {
            const active = card.querySelector('.nth-swatch.active');
            return active ? active.dataset.color : null;
        }

        function filterSizesByColor() {
            if (!popup) return;
            const color = selectedColor();

            popup.querySelectorAll('.nth-size-option').forEach(function (li) {
                if (li.classList.contains('out-of-stock')) {
                    li.classList.add('hidden-by-color');
                    return;
                }
                let colorsForSize = [];
                try { colorsForSize = JSON.parse(li.dataset.colors || '[]'); } catch (e) {}

                let show = true;
                if (color) {
                    show = colorsForSize.indexOf(color) !== -1;
                }
                if (show) {
                    li.classList.remove('hidden-by-color');
                } else {
                    li.classList.add('hidden-by-color');
                }
            });
        }

        colorDots.forEach(function (dot) {
            dot.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                colorDots.forEach(d => d.classList.remove('active'));
                this.classList.add('active');
                filterSizesByColor();
            });
        });

        function findVariant(sizeKey, color) {
            let v = variants.find(function (x) {
                const sizeOk = sizeKey ? x.size_key === sizeKey : true;
                const colorOk = color ? x.color === color : true;
                return sizeOk && colorOk && x.stock > 0;
            });
            if (v) return v;
            v = variants.find(function (x) {
                return (sizeKey ? x.size_key === sizeKey : true) && x.stock > 0;
            });
            return v || null;
        }

        function addToCart(variantId) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            formData.append('_token', csrf);
            if (variantId) formData.append('variant_id', variantId);

            return fetch('{{ route("user.cart.add") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
                credentials: 'same-origin',
            })
            .then(async function (r) {
                let data = null;
                const text = await r.text();
                try { data = JSON.parse(text); } catch (e) {}
                if (r.status === 401 || r.status === 403) {
                    throw new Error('Bạn cần đăng nhập để thêm vào giỏ hàng.');
                }
                if (r.status === 419) throw new Error('Phiên hết hạn. Vui lòng tải lại trang.');
                if (!r.ok || !data || !data.success) {
                    throw new Error((data && data.message) || ('Lỗi: ' + r.status));
                }
                return data;
            })
            .then(function (data) {
                if (window.updateCartBadge) window.updateCartBadge(data.cart_count);
                if (window.showCartToast) window.showCartToast(data.message || 'Thêm vào giỏ hàng thành công.');
            })
            .catch(function (err) {
                if (window.showCartToast) window.showCartToast(err.message || 'Không thể thêm vào giỏ.', true);
                else console.error(err);
            });
        }

        if (quickAddBtn) {
            quickAddBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                if (popup && variants.length > 0) {
                    filterSizesByColor();
                    document.querySelectorAll('.nth-size-popup.open').forEach(p => {
                        if (p !== popup) p.classList.remove('open');
                    });
                    popup.classList.toggle('open');
                    return;
                }
                addToCart(null);
            });
        }

        if (popup) {
            popup.addEventListener('click', function (e) { e.stopPropagation(); });

            popup.querySelectorAll('.nth-size-option').forEach(function (li) {
                li.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (this.classList.contains('out-of-stock') || this.classList.contains('hidden-by-color')) return;

                    const sizeKey = this.dataset.sizeKey;
                    const color = selectedColor();
                    const v = findVariant(sizeKey, color);

                    if (!v) {
                        if (window.showCartToast) window.showCartToast('Kích thước này tạm hết hàng cho màu đã chọn.', true);
                        return;
                    }

                    popup.classList.remove('open');
                    addToCart(v.id);
                });
            });
        }
    });

    // ========================================================
    // BỘ ĐIỀU KHIỂN DẢI TRƯỢT SẢN PHẨM (NÚT < VÀ > CHUYỂN MẪU)
    // ========================================================
    (function initProductSlider() {
        const slider = document.getElementById('nth-product-slider');
        if (!slider) return;

        const prevBtns = [document.getElementById('nth-prod-prev'), document.getElementById('nth-float-prev')].filter(Boolean);
        const nextBtns = [document.getElementById('nth-prod-next'), document.getElementById('nth-float-next')].filter(Boolean);
        const counter = document.getElementById('nth-prod-counter');
        const dotsContainer = document.getElementById('nth-slider-dots');

        function getScrollStep() {
            const slide = slider.querySelector('.nth-product-slide');
            if (!slide) return slider.clientWidth;
            const slideWidth = slide.offsetWidth + 16;
            const visibleSlides = Math.max(1, Math.floor(slider.clientWidth / slideWidth));
            return slideWidth * visibleSlides;
        }

        function createDots() {
            if (!dotsContainer) return;
            dotsContainer.innerHTML = '';
            const step = getScrollStep();
            const totalPages = Math.max(1, Math.ceil(slider.scrollWidth / step));
            if (totalPages <= 1) return;

            for (let i = 0; i < totalPages; i++) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'nth-slider-dot' + (i === 0 ? ' is-active' : '');
                dot.setAttribute('aria-label', 'Đến trang ' + (i + 1));
                dot.addEventListener('click', function () {
                    slider.scrollTo({ left: i * step, behavior: 'smooth' });
                });
                dotsContainer.appendChild(dot);
            }
        }

        function updateSliderState() {
            const scrollLeft = slider.scrollLeft;
            const maxScroll = slider.scrollWidth - slider.clientWidth;
            const isStart = scrollLeft <= 8;
            const isEnd = maxScroll > 0 ? scrollLeft >= maxScroll - 8 : true;

            prevBtns.forEach(b => {
                b.disabled = isStart;
                b.classList.toggle('is-disabled', isStart);
            });
            nextBtns.forEach(b => {
                b.disabled = isEnd;
                b.classList.toggle('is-disabled', isEnd);
            });

            const step = getScrollStep();
            const totalPages = Math.max(1, Math.ceil(slider.scrollWidth / step));
            const currentPage = Math.min(totalPages, Math.max(1, Math.round(scrollLeft / step) + 1));

            if (counter) {
                counter.textContent = currentPage + ' / ' + totalPages;
            }

            if (dotsContainer) {
                const dots = dotsContainer.querySelectorAll('.nth-slider-dot');
                dots.forEach((d, idx) => {
                    d.classList.toggle('is-active', idx === currentPage - 1);
                });
            }
        }

        prevBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                slider.scrollBy({ left: -getScrollStep(), behavior: 'smooth' });
            });
        });

        nextBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                slider.scrollBy({ left: getScrollStep(), behavior: 'smooth' });
            });
        });

        slider.addEventListener('scroll', updateSliderState, { passive: true });
        window.addEventListener('resize', function () {
            createDots();
            updateSliderState();
        });

        createDots();
        updateSliderState();
    })();
});
</script>
@endpush

@endsection
