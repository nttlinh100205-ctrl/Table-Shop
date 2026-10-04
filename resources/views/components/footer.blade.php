{{-- resources/views/components/footer.blade.php --}}
@php
    $categories = \App\Models\Category::orderBy('name')->take(6)->get();
    $hours = config('shop.hours', [
        ['Thứ Hai – Thứ Bảy', '08:00 – 20:00'],
        ['Chủ Nhật', '09:00 – 17:00'],
    ]);
    $socials = config('shop.social', []);
@endphp

<footer class="nth-footer" id="site-footer">
    <div class="nth-container">
        <div class="nth-footer__grid">
            {{-- Cột 1: Thương hiệu & Triết lý --}}
            <div class="nth-footer__col">
                <a href="{{ route('user.home') }}" class="nth-footer__brand">
                    <span class="nth-footer__brand-title">Nội Thất Tinh Hoa</span>
                    <span class="nth-footer__brand-sub">GỖ ĐẸP CHO NHÀ</span>
                </a>
                <p class="nth-footer__about">
                    {{ config('shop.about', 'Xưởng mộc thủ công chuyên chế tác bàn gỗ tự nhiên cho không gian sống hiện đại. Mỗi sản phẩm là sự kết tinh của thớ gỗ tuyển chọn và bàn tay người nghệ nhân.') }}
                </p>
                <div class="nth-footer__socials">
                    @if(!empty($socials['facebook']))
                        <a href="{{ $socials['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook" class="nth-social-link">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                            </svg>
                        </a>
                    @endif
                    @if(!empty($socials['instagram']))
                        <a href="{{ $socials['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram" class="nth-social-link">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                            </svg>
                        </a>
                    @endif
                    @if(!empty($socials['tiktok']))
                        <a href="{{ $socials['tiktok'] }}" target="_blank" rel="noopener" aria-label="TikTok" class="nth-social-link">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Cột 2: Bộ sưu tập & Không gian --}}
            <div class="nth-footer__col">
                <h4 class="nth-footer__heading">Không Gian Sống</h4>
                <ul class="nth-footer__list">
                    <li><a href="{{ route('user.home', ['category' => 59]) }}">Bàn Ăn Gia Đình</a></li>
                    <li><a href="{{ route('user.home', ['category' => 55]) }}">Bàn Làm Việc &amp; Văn Phòng</a></li>
                    <li><a href="{{ route('user.home', ['category' => 57]) }}">Bàn Trà Sofa Phòng Khách</a></li>
                    <li><a href="{{ route('user.home', ['category' => 58]) }}">Bàn Cafe &amp; Sân Vườn</a></li>
                    <li><a href="{{ route('user.home') }}#xuong-go">Quy Trình Xưởng Mộc</a></li>
                    <li><a href="{{ route('user.home') }}#tu-van">Đặt Bàn Theo Bản Vẽ</a></li>
                </ul>
            </div>

            {{-- Cột 3: Giờ làm việc & Showroom --}}
            <div class="nth-footer__col">
                <h4 class="nth-footer__heading">Giờ Hoạt Động</h4>
                <div class="nth-footer__hours">
                    @foreach($hours as $item)
                        <div class="nth-hours-row">
                            <span class="nth-hours-day">{{ $item[0] }}</span>
                            <span class="nth-hours-time">{{ $item[1] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="nth-footer__note">
                    Xưởng tiếp đón quý khách ghé thăm xưởng và trải nghiệm mẫu gỗ trực tiếp trong khung giờ mở cửa.
                </div>
            </div>

            {{-- Cột 4: Thông tin liên hệ thật --}}
            <div class="nth-footer__col">
                <h4 class="nth-footer__heading">Liên Hệ &amp; Xưởng</h4>
                <ul class="nth-footer__contact">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>{{ config('shop.address', 'Hồ Chí Minh, Việt Nam') }}</span>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        <a href="tel:{{ str_replace(' ', '', config('shop.hotline', '0123456789')) }}">
                            {{ config('shop.hotline', '0123 456 789') }}
                        </a>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <a href="mailto:{{ config('shop.email', 'support@store.vn') }}">
                            {{ config('shop.email', 'support@store.vn') }}
                        </a>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        <a href="https://zalo.me/{{ config('shop.zalo', '0123456789') }}" target="_blank" rel="noopener">
                            Zalo: {{ config('shop.zalo', '0123456789') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Dòng bản quyền dưới cùng --}}
        <div class="nth-footer__bottom">
            <span>&copy; {{ date('Y') }} Nội Thất Tinh Hoa. Chế tác thủ công từ tình yêu gỗ Việt.</span>
            <div class="nth-footer__legal">
                <span>Chính sách bảo hành 5 năm</span>
                <span class="nth-dot">·</span>
                <span>Vận chuyển &amp; Lắp đặt</span>
            </div>
        </div>
    </div>
</footer>

<style>
/* ========================================================
   FOOTER STYLES — NỘI THẤT TINH HOA
   ======================================================== */
.nth-footer {
    background: #3F2F24;
    color: #F3E9DC;
    padding: 72px 0 32px;
    font-family: 'Manrope', sans-serif;
    border-top: 1px solid #5A4536;
}
.nth-footer__grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1.2fr;
    gap: 48px;
    margin-bottom: 56px;
}
.nth-footer__col {
    display: flex;
    flex-direction: column;
}

/* Brand */
.nth-footer__brand {
    text-decoration: none;
    color: #F3E9DC;
    display: flex;
    flex-direction: column;
    margin-bottom: 16px;
}
.nth-footer__brand-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 24px;
    font-weight: 500;
    letter-spacing: 0.04em;
    color: #FAF6F0;
}
.nth-footer__brand-sub {
    font-size: 9px;
    letter-spacing: 0.22em;
    opacity: 0.7;
    margin-top: 2px;
}
.nth-footer__about {
    font-size: 13px;
    line-height: 1.7;
    color: #D6C7B8;
    margin: 0 0 20px;
}
.nth-footer__socials {
    display: flex;
    gap: 12px;
}
.nth-social-link {
    width: 36px;
    height: 36px;
    border: 1px solid rgba(243, 233, 220, 0.25);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #F3E9DC;
    text-decoration: none;
    transition: all 0.3s ease;
}
.nth-social-link:hover {
    background: #5A4536;
    border-color: #FAF6F0;
    transform: translateY(-2px);
}

/* Heading */
.nth-footer__heading {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 18px;
    font-weight: 500;
    color: #FAF6F0;
    margin: 0 0 20px;
    letter-spacing: 0.04em;
}

/* List */
.nth-footer__list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.nth-footer__list a {
    color: #D6C7B8;
    text-decoration: none;
    font-size: 13px;
    transition: color 0.2s, padding-left 0.2s;
    display: inline-block;
}
.nth-footer__list a:hover {
    color: #FAF6F0;
    padding-left: 4px;
}

/* Hours */
.nth-footer__hours {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
}
.nth-hours-row {
    display: flex;
    justify-content: space-between;
    font-size: 12.5px;
    padding-bottom: 6px;
    border-bottom: 1px solid rgba(243, 233, 220, 0.1);
}
.nth-hours-day { color: #D6C7B8; }
.nth-hours-time { color: #FAF6F0; font-weight: 600; }
.nth-footer__note {
    font-size: 12px;
    line-height: 1.6;
    color: #B8A89A;
}

/* Contact */
.nth-footer__contact {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.nth-footer__contact li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 13px;
    color: #D6C7B8;
    line-height: 1.5;
}
.nth-footer__contact svg {
    flex-shrink: 0;
    margin-top: 3px;
    color: #C29D62;
}
.nth-footer__contact a {
    color: #D6C7B8;
    text-decoration: none;
    transition: color 0.2s;
}
.nth-footer__contact a:hover {
    color: #FAF6F0;
    text-decoration: underline;
}

/* Bottom */
.nth-footer__bottom {
    border-top: 1px solid rgba(243, 233, 220, 0.15);
    padding-top: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 12px;
    color: #B8A89A;
}
.nth-footer__legal {
    display: flex;
    gap: 8px;
}
.nth-dot { opacity: 0.5; }

@media (max-width: 991px) {
    .nth-footer__grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 36px;
    }
}
@media (max-width: 575px) {
    .nth-footer { padding: 48px 0 24px; }
    .nth-footer__grid {
        grid-template-columns: 1fr;
        gap: 28px;
    }
    .nth-footer__bottom {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
