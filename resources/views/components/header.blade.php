{{-- resources/views/components/header.blade.php --}}
@php
    $categories = \App\Models\Category::with(['subCategories.subSubCategories'])->orderBy('name')->get();
    $cartCount = collect(session('cart', []))->sum('quantity');

    // 4 không gian cho mega menu
    $spaces = [
        [
            'name' => 'Phòng Ăn Gia Đình',
            'desc' => 'Bàn ăn oval, chữ nhật, bàn mở rộng',
            'cat_id' => 59,
            'image' => asset('images/home/phong-an.jpg'),
            'url' => route('user.home', ['category' => 59]),
        ],
        [
            'name' => 'Không Gian Làm Việc',
            'desc' => 'Bàn giám đốc, bàn làm việc chân sắt',
            'cat_id' => 55,
            'image' => asset('images/home/van-phong.jpg'),
            'url' => route('user.home', ['category' => 55]),
        ],
        [
            'name' => 'Phòng Khách Sang Trọng',
            'desc' => 'Bàn trà sofa, bàn góc, bàn decor',
            'cat_id' => 57,
            'image' => asset('images/home/phong-khach.jpg'),
            'url' => route('user.home', ['category' => 57]),
        ],
        [
            'name' => 'Cafe & Sân Vườn',
            'desc' => 'Bàn cafe ngoài trời, bàn ban công',
            'cat_id' => 58,
            'image' => asset('images/home/cafe-san-vuon.jpg'),
            'url' => route('user.home', ['category' => 58]),
        ],
    ];
@endphp

<header class="nth-header" id="site-header">
    {{-- Thanh thông báo trên cùng (rất mảnh, sang trọng) --}}
    <div class="nth-topbar">
        <div class="nth-container nth-topbar__inner">
            <span class="nth-topbar__text">
                Xưởng mộc chế tác thủ công · Gỗ óc chó &amp; sồi tự nhiên tuyển chọn
            </span>
            <div class="nth-topbar__links">
                <a href="tel:{{ str_replace(' ', '', config('shop.hotline', '0123456789')) }}">
                    Hotline: {{ config('shop.hotline', '0123 456 789') }}
                </a>
                <span class="nth-topbar__divider">·</span>
                <a href="https://zalo.me/{{ config('shop.zalo', '0123456789') }}" target="_blank" rel="noopener">
                    Zalo tư vấn
                </a>
            </div>
        </div>
    </div>

    {{-- Thanh điều hướng chính --}}
    <div class="nth-navbar">
        <div class="nth-container nth-navbar__inner">
            {{-- Logo chữ Serif --}}
            <a href="{{ route('user.home') }}" class="nth-brand">
                <span class="nth-brand__title">Nội Thất Tinh Hoa</span>
                <span class="nth-brand__sub">GỖ ĐẸP CHO NHÀ</span>
            </a>

            {{-- Menu chính (Desktop) --}}
            <nav class="nth-nav" aria-label="Menu chính">
                <ul class="nth-nav__list">
                    <li class="nth-nav__item">
                        <a href="{{ route('user.home') }}" class="nth-nav__link {{ request()->routeIs('user.home') && !request()->hasAny(['category','q','sub_category']) ? 'is-active' : '' }}">
                            Trang chủ
                        </a>
                    </li>

                    {{-- Mega menu theo Không gian sống & Danh mục --}}
                    <li class="nth-nav__item nth-has-mega">
                        <a href="#product-section" class="nth-nav__link">
                            Bộ sưu tập
                            <svg class="nth-caret" width="9" height="6" viewBox="0 0 9 6" fill="none" stroke="currentColor" stroke-width="1.2">
                                <path d="M1 1.5L4.5 4.5L8 1.5"/>
                            </svg>
                        </a>

                        {{-- Mega dropdown --}}
                        <div class="nth-mega">
                            <div class="nth-mega__inner">
                                <div class="nth-mega__head">
                                    <span class="nth-mega__kicker">KHÔNG GIAN NỘI THẤT</span>
                                    <a href="{{ route('user.home') }}#product-section" class="nth-mega__all">
                                        Xem tất cả sản phẩm
                                        <svg width="14" height="8" viewBox="0 0 14 8" fill="none" stroke="currentColor" stroke-width="1.2">
                                            <path d="M1 4h12M9 1l4 3-4 3"/>
                                        </svg>
                                    </a>
                                </div>

                                <div class="nth-mega__grid">
                                    @foreach($spaces as $space)
                                        <a href="{{ $space['url'] }}" class="nth-mega-card">
                                            <div class="nth-mega-card__img-wrap">
                                                <img src="{{ $space['image'] }}" alt="{{ $space['name'] }}" loading="lazy">
                                            </div>
                                            <div class="nth-mega-card__body">
                                                <h4 class="nth-mega-card__title">{{ $space['name'] }}</h4>
                                                <p class="nth-mega-card__desc">{{ $space['desc'] }}</p>
                                                <span class="nth-mega-card__cta">Khám phá</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nth-nav__item">
                        <a href="{{ route('user.home') }}#khong-gian" class="nth-nav__link">
                            Không gian
                        </a>
                    </li>

                    <li class="nth-nav__item">
                        <a href="{{ route('user.home') }}#xuong-go" class="nth-nav__link">
                            Xưởng gỗ
                        </a>
                    </li>

                    <li class="nth-nav__item">
                        <a href="{{ route('user.home') }}#tu-van" class="nth-nav__link">
                            Tư vấn may đo
                        </a>
                    </li>
                </ul>
            </nav>

            {{-- Các nút thao tác góc phải --}}
            <div class="nth-actions">
                {{-- Ô tìm kiếm thu gọn thanh lịch --}}
                <form action="{{ route('user.home') }}" method="GET" class="nth-search">
                    <input type="search"
                           name="q"
                           class="nth-search__input"
                           placeholder="Tìm kiếm mẫu bàn..."
                           value="{{ request('q') }}"
                           aria-label="Tìm mẫu bàn">
                    <button type="submit" class="nth-search__btn" aria-label="Tìm kiếm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                </form>

                {{-- Tài khoản / Đơn hàng --}}
                @auth
                    <div class="nth-account-dropdown">
                        <button type="button" class="nth-action-btn" id="nth-user-btn" aria-label="Tài khoản">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>
                            </svg>
                        </button>
                        <div class="nth-account-menu" id="nth-user-menu">
                            <div class="nth-account-menu__header">
                                <strong>{{ auth()->user()->name }}</strong>
                                <small>{{ auth()->user()->email }}</small>
                            </div>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="nth-account-menu__item">Trang quản trị</a>
                            @else
                                <a href="{{ route('user.orders.index') }}" class="nth-account-menu__item">Đơn hàng của tôi</a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" class="nth-account-menu__logout">
                                @csrf
                                <button type="submit">Đăng xuất</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="nth-action-btn" title="Đăng nhập" aria-label="Đăng nhập">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>
                        </svg>
                    </a>
                @endauth

                {{-- Nút giỏ hàng --}}
                <a href="{{ route('user.cart.index') }}" class="nth-action-btn nth-cart-btn" title="Giỏ hàng" aria-label="Giỏ hàng">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <span id="cart-count-badge" class="nth-cart-badge" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">
                        {{ $cartCount }}
                    </span>
                </a>

                {{-- Nút menu mobile --}}
                <button type="button" class="nth-mobile-toggle" id="nth-mobile-toggle" aria-label="Mở menu">
                    <span class="nth-bar"></span>
                    <span class="nth-bar"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Drawer Menu Mobile --}}
    <div class="nth-mobile-drawer" id="nth-mobile-drawer">
        <div class="nth-mobile-drawer__head">
            <span class="nth-brand__title" style="font-size: 20px;">Nội Thất Tinh Hoa</span>
            <button type="button" class="nth-mobile-close" id="nth-mobile-close" aria-label="Đóng menu">✕</button>
        </div>
        <div class="nth-mobile-drawer__body">
            <nav class="nth-mobile-nav">
                <a href="{{ route('user.home') }}" class="nth-mobile-link">Trang chủ</a>
                <a href="{{ route('user.home') }}#product-section" class="nth-mobile-link">Tất cả sản phẩm</a>

                <div class="nth-mobile-cat-group">
                    <span class="nth-mobile-kicker">KHÔNG GIAN BÀN GỖ</span>
                    @foreach($spaces as $space)
                        <a href="{{ $space['url'] }}" class="nth-mobile-sublink">
                            {{ $space['name'] }}
                        </a>
                    @endforeach
                </div>

                <a href="{{ route('user.home') }}#khong-gian" class="nth-mobile-link">Không gian sống</a>
                <a href="{{ route('user.home') }}#xuong-go" class="nth-mobile-link">Câu chuyện xưởng mộc</a>
                <a href="{{ route('user.home') }}#tu-van" class="nth-mobile-link">Tư vấn trực tiếp</a>

                <div class="nth-mobile-divider"></div>

                @auth
                    <a href="{{ route('user.orders.index') }}" class="nth-mobile-link">Đơn hàng của tôi</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nth-mobile-link" style="background:none;border:none;color:#9b3327;padding:12px 0;width:100%;text-align:left;cursor:pointer;">Đăng xuất</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nth-mobile-link">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="nth-mobile-link">Đăng ký tài khoản</a>
                @endauth
            </nav>

            <div class="nth-mobile-contact">
                <p>Hotline: <a href="tel:{{ str_replace(' ', '', config('shop.hotline', '0123456789')) }}">{{ config('shop.hotline', '0123 456 789') }}</a></p>
                <p>Địa chỉ: {{ config('shop.address', 'Hồ Chí Minh, Việt Nam') }}</p>
            </div>
        </div>
    </div>
    <div class="nth-backdrop" id="nth-backdrop"></div>
</header>

<style>
/* ========================================================
   HEADER STYLES — NỘI THẤT TINH HOA
   Palette: #F3E9DC, #5A4536, #3F2F24, #3A2E26
   ======================================================== */
.nth-header {
    background: #FAF6F0;
    border-bottom: 1px solid #E6D8C8;
    position: sticky;
    top: 0;
    z-index: 1000;
    font-family: 'Manrope', sans-serif;
    color: #3A2E26;
    transition: background 0.3s ease, box-shadow 0.3s ease;
}
.nth-header.is-scrolled {
    background: rgba(250, 246, 240, 0.96);
    backdrop-filter: blur(8px);
    box-shadow: 0 4px 20px rgba(58, 46, 38, 0.05);
}

.nth-container {
    max-width: 1320px;
    margin: 0 auto;
    padding: 0 clamp(20px, 4vw, 48px);
}

/* Topbar */
.nth-topbar {
    background: #3F2F24;
    color: #F3E9DC;
    font-size: 11px;
    letter-spacing: 0.06em;
    padding: 7px 0;
}
.nth-topbar__inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.nth-topbar__text {
    opacity: 0.9;
}
.nth-topbar__links a {
    color: #F3E9DC;
    text-decoration: none;
    opacity: 0.85;
    transition: opacity 0.2s;
}
.nth-topbar__links a:hover { opacity: 1; text-decoration: underline; }
.nth-topbar__divider { margin: 0 8px; opacity: 0.5; }

/* Navbar */
.nth-navbar__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 76px;
    position: relative;
}

/* Brand */
.nth-brand {
    text-decoration: none;
    color: #3A2E26;
    display: flex;
    flex-direction: column;
    line-height: 1.15;
}
.nth-brand__title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 24px;
    font-weight: 500;
    letter-spacing: 0.04em;
    color: #3A2E26;
}
.nth-brand__sub {
    font-size: 8.5px;
    letter-spacing: 0.26em;
    color: #7E7065;
    margin-top: 2px;
}

/* Nav */
.nth-nav__list {
    display: flex;
    align-items: center;
    gap: 32px;
    list-style: none;
    margin: 0;
    padding: 0;
}
.nth-nav__link {
    font-size: 13.5px;
    font-weight: 500;
    letter-spacing: 0.04em;
    color: #3A2E26;
    text-decoration: none;
    padding: 26px 0;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    position: relative;
    transition: color 0.25s ease;
}
.nth-nav__link:hover,
.nth-nav__link.is-active {
    color: #5A4536;
}
.nth-nav__link::after {
    content: '';
    position: absolute;
    bottom: 20px;
    left: 0;
    width: 0;
    height: 1px;
    background: #5A4536;
    transition: width 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
}
.nth-nav__link:hover::after,
.nth-nav__link.is-active::after {
    width: 100%;
}
.nth-caret {
    transition: transform 0.3s ease;
}
.nth-has-mega:hover .nth-caret {
    transform: rotate(180deg);
}

/* Mega menu */
.nth-has-mega {
    position: static;
}
.nth-mega {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    width: 100%;
    background: #FAF6F0;
    border-bottom: 1px solid #E6D8C8;
    box-shadow: 0 16px 36px rgba(58, 46, 38, 0.08);
    opacity: 0;
    visibility: hidden;
    transform: translateY(8px);
    transition: all 0.35s cubic-bezier(0.22, 0.61, 0.36, 1);
    pointer-events: none;
}
.nth-has-mega:hover .nth-mega,
.nth-has-mega:focus-within .nth-mega {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    pointer-events: auto;
}
.nth-mega__inner {
    max-width: 1320px;
    margin: 0 auto;
    padding: 32px clamp(20px, 4vw, 48px) 40px;
}
.nth-mega__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 16px;
    margin-bottom: 24px;
    border-bottom: 1px solid #E6D8C8;
}
.nth-mega__kicker {
    font-size: 11px;
    letter-spacing: 0.18em;
    color: #7E7065;
    font-weight: 600;
}
.nth-mega__all {
    font-size: 12.5px;
    color: #5A4536;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: gap 0.25s ease;
}
.nth-mega__all:hover {
    gap: 12px;
}
.nth-mega__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}
.nth-mega-card {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    group: hover;
}
.nth-mega-card__img-wrap {
    aspect-ratio: 4 / 3;
    overflow: hidden;
    background: #EFE3D3;
    border-radius: 2px;
    margin-bottom: 12px;
}
.nth-mega-card__img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.22, 0.61, 0.36, 1);
}
.nth-mega-card:hover .nth-mega-card__img-wrap img {
    transform: scale(1.05);
}
.nth-mega-card__title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 19px;
    font-weight: 500;
    margin: 0 0 4px;
    color: #3A2E26;
    transition: color 0.2s;
}
.nth-mega-card:hover .nth-mega-card__title {
    color: #5A4536;
}
.nth-mega-card__desc {
    font-size: 12px;
    color: #7E7065;
    line-height: 1.5;
    margin: 0 0 8px;
}
.nth-mega-card__cta {
    font-size: 11px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #5A4536;
    font-weight: 600;
}

/* Actions */
.nth-actions {
    display: flex;
    align-items: center;
    gap: 14px;
}
.nth-search {
    position: relative;
    display: flex;
    align-items: center;
}
.nth-search__input {
    width: 170px;
    height: 36px;
    background: #F3E9DC;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    padding: 0 32px 0 12px;
    font-size: 12.5px;
    font-family: inherit;
    color: #3A2E26;
    outline: none;
    transition: all 0.3s ease;
}
.nth-search__input:focus {
    width: 220px;
    background: #fff;
    border-color: #5A4536;
}
.nth-search__btn {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    padding: 0;
    color: #7E7065;
    cursor: pointer;
    display: flex;
    align-items: center;
}
.nth-search__btn:hover { color: #3A2E26; }

.nth-action-btn {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #3A2E26;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 2px;
    text-decoration: none;
    position: relative;
    transition: all 0.25s ease;
    cursor: pointer;
}
.nth-action-btn:hover {
    background: #F3E9DC;
    border-color: #E6D8C8;
    color: #5A4536;
}
.nth-cart-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    background: #5A4536;
    color: #fff;
    font-size: 9.5px;
    font-weight: 600;
    border-radius: 99px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* User dropdown */
.nth-account-dropdown {
    position: relative;
}
.nth-account-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 200px;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    box-shadow: 0 10px 24px rgba(58, 46, 38, 0.08);
    padding: 10px 0;
    opacity: 0;
    visibility: hidden;
    transform: translateY(6px);
    transition: all 0.25s ease;
    z-index: 1020;
}
.nth-account-dropdown:hover .nth-account-menu,
.nth-account-dropdown:focus-within .nth-account-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.nth-account-menu__header {
    padding: 6px 16px 10px;
    border-bottom: 1px solid #E6D8C8;
    margin-bottom: 6px;
}
.nth-account-menu__header strong { display: block; font-size: 13px; color: #3A2E26; }
.nth-account-menu__header small { font-size: 11px; color: #7E7065; }
.nth-account-menu__item {
    display: block;
    padding: 7px 16px;
    font-size: 12.5px;
    color: #3A2E26;
    text-decoration: none;
    transition: background 0.2s;
}
.nth-account-menu__item:hover { background: #F3E9DC; }
.nth-account-menu__logout { border-top: 1px solid #E6D8C8; margin-top: 6px; padding-top: 4px; }
.nth-account-menu__logout button {
    width: 100%;
    text-align: left;
    padding: 7px 16px;
    background: none;
    border: none;
    font-size: 12.5px;
    color: #9b3327;
    cursor: pointer;
}
.nth-account-menu__logout button:hover { background: #F3E9DC; }

/* Mobile toggler */
.nth-mobile-toggle {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 6px;
    width: 36px;
    height: 36px;
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
}
.nth-bar {
    width: 22px;
    height: 1.5px;
    background: #3A2E26;
    transition: all 0.3s;
}

/* Mobile Drawer */
.nth-mobile-drawer {
    position: fixed;
    top: 0;
    right: -320px;
    width: 300px;
    height: 100vh;
    background: #FAF6F0;
    box-shadow: -8px 0 32px rgba(58, 46, 38, 0.15);
    z-index: 2000;
    transition: right 0.4s cubic-bezier(0.22, 0.61, 0.36, 1);
    display: flex;
    flex-direction: column;
}
.nth-mobile-drawer.is-open {
    right: 0;
}
.nth-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(58, 46, 38, 0.35);
    z-index: 1999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}
.nth-backdrop.is-open {
    opacity: 1;
    visibility: visible;
}
.nth-mobile-drawer__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid #E6D8C8;
}
.nth-mobile-close {
    background: none;
    border: none;
    font-size: 18px;
    color: #3A2E26;
    cursor: pointer;
}
.nth-mobile-drawer__body {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}
.nth-mobile-nav {
    display: flex;
    flex-direction: column;
}
.nth-mobile-link {
    padding: 12px 0;
    color: #3A2E26;
    text-decoration: none;
    font-size: 14.5px;
    font-weight: 500;
    border-bottom: 1px solid #EFE3D3;
}
.nth-mobile-cat-group {
    padding: 14px 0 6px;
}
.nth-mobile-kicker {
    display: block;
    font-size: 10.5px;
    letter-spacing: 0.16em;
    color: #7E7065;
    margin-bottom: 8px;
    font-weight: 600;
}
.nth-mobile-sublink {
    display: block;
    padding: 8px 12px;
    font-size: 13px;
    color: #5A4536;
    text-decoration: none;
}
.nth-mobile-divider {
    height: 1px;
    background: #E6D8C8;
    margin: 16px 0;
}
.nth-mobile-contact {
    margin-top: 24px;
    font-size: 12px;
    color: #7E7065;
    line-height: 1.6;
}
.nth-mobile-contact a { color: #5A4536; text-decoration: none; }

/* Responsive */
@media (max-width: 991px) {
    .nth-topbar { display: none; }
    .nth-nav { display: none; }
    .nth-search { display: none; }
    .nth-mobile-toggle { display: flex; }
    .nth-navbar__inner { height: 64px; }
    .nth-brand__title { font-size: 21px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('nth-mobile-toggle');
    const drawer = document.getElementById('nth-mobile-drawer');
    const close  = document.getElementById('nth-mobile-close');
    const bdrop  = document.getElementById('nth-backdrop');
    const header = document.getElementById('site-header');

    function openMenu() {
        drawer.classList.add('is-open');
        bdrop.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function closeMenu() {
        drawer.classList.remove('is-open');
        bdrop.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    if (toggle) toggle.addEventListener('click', openMenu);
    if (close)  close.addEventListener('click', closeMenu);
    if (bdrop)  bdrop.addEventListener('click', closeMenu);

    window.addEventListener('scroll', function () {
        if (window.scrollY > 30) {
            header.classList.add('is-scrolled');
        } else {
            header.classList.remove('is-scrolled');
        }
    }, { passive: true });
});
</script>
