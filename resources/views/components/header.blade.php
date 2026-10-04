{{-- resources/views/components/header.blade.php --}}
@php
    $categories = \App\Models\Category::with(['subCategories.subSubCategories'])->orderBy('name')->get();
    $cartCount = collect(session('cart', []))->sum('quantity');
@endphp

<header class="nth-header" id="site-header">
    {{-- Thanh điều hướng chính (Đã bỏ thanh header trên cùng theo yêu cầu) --}}
    <div class="nth-navbar">
        <div class="nth-container nth-navbar__inner">
            {{-- Nút menu mobile đặt bên TRÁI --}}
            <button type="button" class="nth-mobile-toggle" id="nth-mobile-toggle" aria-label="Mở menu">
                <span class="nth-bar"></span>
                <span class="nth-bar"></span>
            </button>

            {{-- Logo chữ Serif --}}
            <a href="{{ route('user.home') }}" class="nth-brand">
                <span class="nth-brand__title">Nội Thất Tinh Hoa</span>
                <span class="nth-brand__sub">GỖ ĐẸP CHO NHÀ</span>
            </a>

            {{-- Menu chính (Desktop) — Thống nhất 100% với Database --}}
            <nav class="nth-nav" aria-label="Menu chính">
                <ul class="nth-nav__list">
                    <li class="nth-nav__item">
                        <a href="{{ route('user.home') }}" class="nth-nav__link {{ request()->routeIs('user.home') && !request()->hasAny(['category','q','sub_category','sub_sub_category']) ? 'is-active' : '' }}">
                            Trang chủ
                        </a>
                    </li>

                    @if(auth()->check() && auth()->user()->isAdmin())
                        <li class="nth-nav__item">
                            <a href="{{ route('admin.dashboard') }}" class="nth-nav__link">Dashboard Admin</a>
                        </li>
                        <li class="nth-nav__item">
                            <a href="{{ route('admin.categories.index') }}" class="nth-nav__link">Categories</a>
                        </li>
                        <li class="nth-nav__item">
                            <a href="{{ route('admin.products.index') }}" class="nth-nav__link">Products</a>
                        </li>
                    @else
                        {{-- Danh mục tải trực tiếp từ Database --}}
                        @foreach($categories as $root)
                            <li class="nth-nav__item {{ $root->subCategories->count() ? 'nth-has-dropdown' : '' }}">
                                <a href="{{ route('user.home', ['category' => $root->id]) }}#product-section"
                                   class="nth-nav__link {{ request('category') == $root->id ? 'is-active' : '' }}">
                                    {{ $root->name }}
                                    @if($root->subCategories->count())
                                        <svg class="nth-caret" width="8" height="5" viewBox="0 0 8 5" fill="none" stroke="currentColor" stroke-width="1.3">
                                            <path d="M1 1L4 4L7 1"/>
                                        </svg>
                                    @endif
                                </a>

                                @if($root->subCategories->count())
                                    <div class="nth-dropdown">
                                        <ul class="nth-dropdown__list">
                                            @foreach($root->subCategories as $child)
                                                <li class="nth-dropdown__item {{ $child->subSubCategories->count() ? 'has-sub' : '' }}">
                                                    <a href="{{ route('user.home', ['sub_category' => $child->id]) }}#product-section"
                                                       class="nth-dropdown__link {{ request('sub_category') == $child->id ? 'is-active' : '' }}">
                                                        <span>{{ $child->name }}</span>
                                                        @if($child->subSubCategories->count())
                                                            <svg width="6" height="8" viewBox="0 0 6 8" fill="none" stroke="currentColor" stroke-width="1.2">
                                                                <path d="M1 1L4 4L1 7"/>
                                                            </svg>
                                                        @endif
                                                    </a>

                                                    @if($child->subSubCategories->count())
                                                        <ul class="nth-subdropdown">
                                                            @foreach($child->subSubCategories as $grand)
                                                                <li>
                                                                    <a href="{{ route('user.home', ['sub_sub_category' => $grand->id]) }}#product-section"
                                                                       class="nth-subdropdown__link {{ request('sub_sub_category') == $grand->id ? 'is-active' : '' }}">
                                                                        {{ $grand->name }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    @endif

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
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
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
            </div>
        </div>
    </div>

    {{-- Drawer Menu Mobile — Đặt bên TRÁI, ẩn hoàn toàn trên Desktop --}}
    <div class="nth-mobile-drawer" id="nth-mobile-drawer">
        <div class="nth-mobile-drawer__head">
            <span class="nth-brand__title" style="font-size: 20px;">Nội Thất Tinh Hoa</span>
            <button type="button" class="nth-mobile-close" id="nth-mobile-close" aria-label="Đóng menu">✕</button>
        </div>
        <div class="nth-mobile-drawer__body">
            <nav class="nth-mobile-nav">
                <a href="{{ route('user.home') }}" class="nth-mobile-link">Trang chủ</a>

                <div class="nth-mobile-cat-group">
                    <span class="nth-mobile-kicker">DANH MỤC SẢN PHẨM</span>
                    @foreach($categories as $root)
                        <div class="nth-mobile-cat-item">
                            <a href="{{ route('user.home', ['category' => $root->id]) }}#product-section" class="nth-mobile-sublink font-weight-bold">
                                {{ $root->name }}
                            </a>
                            @if($root->subCategories->count())
                                <div class="nth-mobile-child-list">
                                    @foreach($root->subCategories as $child)
                                        <a href="{{ route('user.home', ['sub_category' => $child->id]) }}#product-section" class="nth-mobile-subchild">
                                            · {{ $child->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('user.home') }}#xuong-go" class="nth-mobile-link">Xưởng gỗ</a>
                <a href="{{ route('user.home') }}#tu-van" class="nth-mobile-link">Tư vấn may đo</a>

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
   HEADER STYLES — NỘI THẤT TINH HOA (Đồng bộ chuẩn Database)
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
    background: rgba(250, 246, 240, 0.98);
    backdrop-filter: blur(8px);
    box-shadow: 0 4px 18px rgba(58, 46, 38, 0.05);
}

.nth-container {
    max-width: 1320px;
    margin: 0 auto;
    padding: 0 clamp(16px, 3.5vw, 40px);
}

/* Navbar */
.nth-navbar__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 70px;
    position: relative;
}

/* Brand */
.nth-brand {
    text-decoration: none;
    color: #3A2E26;
    display: flex;
    flex-direction: column;
    line-height: 1.15;
    flex-shrink: 0;
}
.nth-brand__title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 23px;
    font-weight: 500;
    letter-spacing: 0.04em;
    color: #3A2E26;
}
.nth-brand__sub {
    font-size: 8px;
    letter-spacing: 0.24em;
    color: #7E7065;
    margin-top: 2px;
}

/* Nav */
.nth-nav__list {
    display: flex;
    align-items: center;
    gap: 24px;
    list-style: none;
    margin: 0;
    padding: 0;
}
.nth-nav__link {
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 0.03em;
    color: #3A2E26;
    text-decoration: none;
    padding: 24px 0;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    position: relative;
    transition: color 0.2s ease;
    white-space: nowrap;
}
.nth-nav__link:hover,
.nth-nav__link.is-active {
    color: #5A4536;
}
.nth-nav__link::after {
    content: '';
    position: absolute;
    bottom: 18px;
    left: 0;
    width: 0;
    height: 1.5px;
    background: #5A4536;
    transition: width 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
}
.nth-nav__link:hover::after,
.nth-nav__link.is-active::after {
    width: 100%;
}
.nth-caret {
    transition: transform 0.25s ease;
}
.nth-has-dropdown:hover .nth-caret {
    transform: rotate(180deg);
}

/* Dropdown Menu (Database driven) */
.nth-has-dropdown {
    position: relative;
}
.nth-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 220px;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    box-shadow: 0 12px 32px rgba(58, 46, 38, 0.1);
    opacity: 0;
    visibility: hidden;
    transform: translateY(6px);
    transition: all 0.25s ease;
    pointer-events: none;
    padding: 6px 0;
    z-index: 1050;
}
.nth-has-dropdown:hover > .nth-dropdown,
.nth-has-dropdown:focus-within > .nth-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    pointer-events: auto;
}
.nth-dropdown__list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.nth-dropdown__item {
    position: relative;
}
.nth-dropdown__link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 16px;
    font-size: 13px;
    color: #3A2E26;
    text-decoration: none;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.nth-dropdown__link:hover,
.nth-dropdown__link.is-active {
    background: #F3E9DC;
    color: #5A4536;
    padding-left: 19px;
}

/* Sub-dropdown (Cấp 3) */
.nth-subdropdown {
    position: absolute;
    top: 0;
    left: 100%;
    min-width: 200px;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    box-shadow: 0 12px 32px rgba(58, 46, 38, 0.1);
    opacity: 0;
    visibility: hidden;
    transform: translateX(6px);
    transition: all 0.25s ease;
    pointer-events: none;
    padding: 6px 0;
    list-style: none;
    margin: 0;
}
.nth-dropdown__item.has-sub:hover > .nth-subdropdown {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
    pointer-events: auto;
}
.nth-subdropdown__link {
    display: block;
    padding: 7px 16px;
    font-size: 12.5px;
    color: #3A2E26;
    text-decoration: none;
    transition: all 0.15s ease;
}
.nth-subdropdown__link:hover,
.nth-subdropdown__link.is-active {
    background: #F3E9DC;
    color: #5A4536;
}

/* Actions */
.nth-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}
.nth-search {
    position: relative;
    display: flex;
    align-items: center;
}
.nth-search__input {
    width: 160px;
    height: 35px;
    background: #F3E9DC;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    padding: 0 30px 0 10px;
    font-size: 12px;
    font-family: inherit;
    color: #3A2E26;
    outline: none;
    transition: all 0.25s ease;
}
.nth-search__input:focus {
    width: 200px;
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
.nth-action-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #3A2E26;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 2px;
    text-decoration: none;
    position: relative;
    transition: all 0.2s ease;
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
    min-width: 15px;
    height: 15px;
    padding: 0 3px;
    background: #5A4536;
    color: #fff;
    font-size: 9px;
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
    min-width: 190px;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    box-shadow: 0 10px 24px rgba(58, 46, 38, 0.08);
    padding: 8px 0;
    opacity: 0;
    visibility: hidden;
    transform: translateY(6px);
    transition: all 0.2s ease;
    z-index: 1020;
}
.nth-account-dropdown:hover .nth-account-menu,
.nth-account-dropdown:focus-within .nth-account-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.nth-account-menu__header {
    padding: 6px 14px 8px;
    border-bottom: 1px solid #E6D8C8;
    margin-bottom: 4px;
}
.nth-account-menu__header strong { display: block; font-size: 12.5px; color: #3A2E26; }
.nth-account-menu__header small { font-size: 10.5px; color: #7E7065; }
.nth-account-menu__item {
    display: block;
    padding: 6px 14px;
    font-size: 12px;
    color: #3A2E26;
    text-decoration: none;
}
.nth-account-menu__item:hover { background: #F3E9DC; }
.nth-account-menu__logout { border-top: 1px solid #E6D8C8; margin-top: 4px; padding-top: 4px; }
.nth-account-menu__logout button {
    width: 100%;
    text-align: left;
    padding: 6px 14px;
    background: none;
    border: none;
    font-size: 12px;
    color: #9b3327;
    cursor: pointer;
}

/* Mobile Toggler (ở mép TRÁI) */
.nth-mobile-toggle {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    width: 32px;
    height: 32px;
    background: none;
    border: none;
    padding: 3px;
    cursor: pointer;
}
.nth-bar {
    width: 20px;
    height: 1.5px;
    background: #3A2E26;
    transition: all 0.3s;
}

/* Mobile Drawer — BÊN TRÁI, KHÔNG LỆCH TRANG */
.nth-mobile-drawer {
    position: fixed;
    top: 0;
    left: 0;
    width: min(300px, 85vw);
    height: 100vh;
    height: 100dvh;
    background: #FAF6F0;
    box-shadow: 12px 0 36px rgba(58, 46, 38, 0.2);
    z-index: 2000;
    transform: translateX(-100%);
    visibility: hidden;
    pointer-events: none;
    transition: transform 0.35s cubic-bezier(0.22, 0.61, 0.36, 1),
                visibility 0.35s cubic-bezier(0.22, 0.61, 0.36, 1);
    display: flex;
    flex-direction: column;
}
.nth-mobile-drawer.is-open {
    transform: translateX(0);
    visibility: visible;
    pointer-events: auto;
}
.nth-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(58, 46, 38, 0.4);
    backdrop-filter: blur(2px);
    z-index: 1999;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: all 0.3s ease;
}
.nth-backdrop.is-open {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}
.nth-mobile-drawer__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
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
    padding: 20px;
}
.nth-mobile-nav {
    display: flex;
    flex-direction: column;
}
.nth-mobile-link {
    padding: 10px 0;
    color: #3A2E26;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border-bottom: 1px solid #EFE3D3;
}
.nth-mobile-cat-group {
    padding: 12px 0 6px;
}
.nth-mobile-kicker {
    display: block;
    font-size: 10px;
    letter-spacing: 0.16em;
    color: #7E7065;
    margin-bottom: 8px;
    font-weight: 600;
}
.nth-mobile-sublink {
    display: block;
    padding: 6px 8px;
    font-size: 13px;
    color: #5A4536;
    font-weight: 600;
    text-decoration: none;
}
.nth-mobile-child-list {
    padding-left: 12px;
    margin-bottom: 6px;
}
.nth-mobile-subchild {
    display: block;
    padding: 4px 6px;
    font-size: 12px;
    color: #7E7065;
    text-decoration: none;
}
.nth-mobile-divider {
    height: 1px;
    background: #E6D8C8;
    margin: 14px 0;
}
.nth-mobile-contact {
    margin-top: 20px;
    font-size: 11.5px;
    color: #7E7065;
    line-height: 1.5;
}
.nth-mobile-contact a { color: #5A4536; text-decoration: none; }

/* Responsive Rules */
@media (min-width: 992px) {
    .nth-mobile-drawer,
    .nth-backdrop,
    .nth-mobile-toggle {
        display: none !important;
    }
}
@media (max-width: 991px) {
    .nth-nav { display: none; }
    .nth-search { display: none; }
    .nth-mobile-toggle {
        display: flex;
        margin-right: 10px;
    }
    .nth-navbar__inner {
        height: 60px;
        justify-content: flex-start;
        gap: 8px;
    }
    .nth-actions {
        margin-left: auto;
    }
    .nth-brand__title { font-size: 20px; }
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
