{{-- resources/views/components/header.blade.php --}}
@php
    $categories = \App\Models\Category::with(['subCategories.subSubCategories'])->orderBy('name')->get();
    $cartCount = collect(session('cart', []))->sum('quantity');
@endphp

<header class="nth-header" id="site-header">
    {{-- Thanh điều hướng chính (Đã bỏ topbar nâu ở trên cùng) --}}
    <div class="nth-navbar">
        <div class="nth-container nth-navbar__inner">
            
            {{-- Cụm bên trái: Nút Menu Sidebar + Logo --}}
            <div class="nth-navbar__left">
                {{-- Nút Menu Bên Trái (Hiển thị CẢ Desktop & Mobile) --}}
                <button type="button" class="nth-sidebar-toggle" id="nth-sidebar-toggle" aria-label="Mở menu danh mục bên trái" title="Danh mục sản phẩm">
                    <span class="nth-hamburger">
                        <span class="nth-bar"></span>
                        <span class="nth-bar"></span>
                        <span class="nth-bar"></span>
                    </span>
                    <span class="nth-sidebar-toggle__label">Danh mục</span>
                </button>

                {{-- Logo chữ Serif --}}
                <a href="{{ route('user.home') }}" class="nth-brand">
                    <span class="nth-brand__title">Nội Thất Tinh Hoa</span>
                    <span class="nth-brand__sub">GỖ ĐẸP CHO NHÀ</span>
                </a>
            </div>

            {{-- Menu chính giữa (Desktop) — Thống nhất 100% với Database --}}
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

    {{-- ========================================================
         DRAWER MENU BÊN TRÁI (Hoạt động cả Desktop lẫn Mobile)
         - Nằm hoàn toàn ở mép trái trong màn hình
         - Cây danh mục 100% từ Database
         ======================================================== --}}
    <div class="nth-sidebar-drawer" id="nth-sidebar-drawer" aria-hidden="true">
        <div class="nth-sidebar-drawer__head">
            <div class="nth-brand">
                <span class="nth-brand__title" style="font-size: 21px;">Nội Thất Tinh Hoa</span>
                <span class="nth-brand__sub">GỖ ĐẸP CHO NHÀ</span>
            </div>
            <button type="button" class="nth-sidebar-close" id="nth-sidebar-close" aria-label="Đóng menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="nth-sidebar-drawer__body">
            <nav class="nth-sidebar-nav">
                <a href="{{ route('user.home') }}" class="nth-sidebar-link {{ request()->routeIs('user.home') && !request()->hasAny(['category','q','sub_category','sub_sub_category']) ? 'is-active' : '' }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <span>Trang chủ</span>
                </a>

                {{-- Cụm danh mục lấy 100% từ Database --}}
                <div class="nth-sidebar-cat-group">
                    <span class="nth-sidebar-kicker">DANH MỤC NỘI THẤT</span>
                    @foreach($categories as $root)
                        <div class="nth-sidebar-cat-item">
                            <a href="{{ route('user.home', ['category' => $root->id]) }}#product-section"
                               class="nth-sidebar-rootlink {{ request('category') == $root->id ? 'is-active' : '' }}">
                                <span>{{ $root->name }}</span>
                                @if($root->subCategories->count())
                                    <span class="nth-sidebar-count">{{ $root->subCategories->count() }}</span>
                                @endif
                            </a>

                            @if($root->subCategories->count())
                                <div class="nth-sidebar-child-list">
                                    @foreach($root->subCategories as $child)
                                        <div class="nth-sidebar-subitem">
                                            <a href="{{ route('user.home', ['sub_category' => $child->id]) }}#product-section"
                                               class="nth-sidebar-sublink {{ request('sub_category') == $child->id ? 'is-active' : '' }}">
                                                · {{ $child->name }}
                                            </a>
                                            @if($child->subSubCategories->count())
                                                <div class="nth-sidebar-grand-list">
                                                    @foreach($child->subSubCategories as $grand)
                                                        <a href="{{ route('user.home', ['sub_sub_category' => $grand->id]) }}#product-section"
                                                           class="nth-sidebar-grandlink {{ request('sub_sub_category') == $grand->id ? 'is-active' : '' }}">
                                                            - {{ $grand->name }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="nth-sidebar-divider"></div>

                <a href="{{ route('user.home') }}#xuong-go" class="nth-sidebar-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
                    <span>Xưởng mộc thủ công</span>
                </a>
                <a href="{{ route('user.home') }}#tu-van" class="nth-sidebar-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <span>Tư vấn may đo bàn</span>
                </a>

                <div class="nth-sidebar-divider"></div>

                @auth
                    <a href="{{ route('user.orders.index') }}" class="nth-sidebar-link {{ request()->routeIs('user.orders.*') ? 'is-active' : '' }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <span>Đơn hàng của tôi</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nth-sidebar-link" style="background:none;border:none;color:#9b3327;width:100%;text-align:left;cursor:pointer;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nth-sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                        <span>Đăng nhập</span>
                    </a>
                    <a href="{{ route('register') }}" class="nth-sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                        <span>Đăng ký tài khoản</span>
                    </a>
                @endauth
            </nav>

            <div class="nth-sidebar-contact">
                <div class="nth-sidebar-contact__item">
                    <small>Hotline tư vấn:</small>
                    <a href="tel:{{ str_replace(' ', '', config('shop.hotline', '0123456789')) }}">{{ config('shop.hotline', '0123 456 789') }}</a>
                </div>
                <div class="nth-sidebar-contact__item">
                    <small>Địa chỉ xưởng:</small>
                    <span>{{ config('shop.address', 'Hồ Chí Minh, Việt Nam') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Lớp nền mờ khi mở Drawer --}}
    <div class="nth-backdrop" id="nth-backdrop"></div>
</header>

<style>
/* ========================================================
   HEADER STYLES — NỘI THẤT TINH HOA
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
    padding: 0 clamp(14px, 3vw, 40px);
}

/* Navbar */
.nth-navbar__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 70px;
    position: relative;
    gap: 16px;
}

/* Cụm bên trái: Nút Menu + Logo */
.nth-navbar__left {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-shrink: 0;
}

/* Nút mở Menu bên trái (Cực kỳ nổi bật, sang trọng, hoạt động mọi kích thước) */
.nth-sidebar-toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 12px;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    color: #3A2E26;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Manrope', sans-serif;
    letter-spacing: 0.02em;
    transition: all 0.2s ease;
}
.nth-sidebar-toggle:hover {
    background: #5A4536;
    color: #FAF6F0;
    border-color: #5A4536;
}
.nth-sidebar-toggle:hover .nth-bar {
    background: #FAF6F0;
}
.nth-hamburger {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
    width: 16px;
}
.nth-bar {
    display: block;
    width: 16px;
    height: 1.5px;
    background: #3A2E26;
    border-radius: 1px;
    transition: background 0.2s ease;
}
.nth-sidebar-toggle__label {
    display: inline-block;
    line-height: 1;
}

/* Brand Logo */
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
    font-weight: 600;
    letter-spacing: 0.03em;
    color: #3A2E26;
}
.nth-brand__sub {
    font-size: 8px;
    letter-spacing: 0.24em;
    color: #7E7065;
    margin-top: 2px;
}

/* Nav Desktop */
.nth-nav {
    flex: 1;
    display: flex;
    justify-content: center;
}
.nth-nav__list {
    display: flex;
    align-items: center;
    gap: 22px;
    list-style: none;
    margin: 0;
    padding: 0;
}
.nth-nav__link {
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 0.02em;
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
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: #5A4536;
    transform: scaleX(0);
    transition: transform 0.25s cubic-bezier(0.22, 0.61, 0.36, 1);
}
.nth-nav__link:hover::after,
.nth-nav__link.is-active::after {
    transform: scaleX(1);
}
.nth-caret {
    transition: transform 0.2s ease;
    color: #7E7065;
}
.nth-nav__item:hover .nth-caret {
    transform: rotate(180deg);
    color: #5A4536;
}

/* Dropdown cấp 2 */
.nth-has-dropdown {
    position: relative;
}
.nth-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 210px;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    box-shadow: 0 12px 32px rgba(58, 46, 38, 0.1);
    padding: 6px 0;
    opacity: 0;
    visibility: hidden;
    transform: translateY(8px);
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
    z-index: 1010;
}
.nth-has-dropdown:hover .nth-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
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
    padding: 9px 18px;
    font-size: 12.5px;
    color: #3A2E26;
    text-decoration: none;
    transition: all 0.15s ease;
}
.nth-dropdown__link:hover,
.nth-dropdown__link.is-active {
    background: #F3E9DC;
    color: #5A4536;
}

/* Sub-dropdown cấp 3 */
.nth-subdropdown {
    position: absolute;
    top: 0;
    left: 100%;
    min-width: 190px;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    box-shadow: 0 12px 32px rgba(58, 46, 38, 0.1);
    padding: 6px 0;
    opacity: 0;
    visibility: hidden;
    transform: translateX(6px);
    transition: all 0.2s ease;
    list-style: none;
    margin: 0;
}
.nth-dropdown__item.has-sub:hover .nth-subdropdown {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
}
.nth-subdropdown__link {
    display: block;
    padding: 8px 18px;
    font-size: 12px;
    color: #3A2E26;
    text-decoration: none;
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
    gap: 8px;
    flex-shrink: 0;
}
.nth-search {
    position: relative;
    display: flex;
    align-items: center;
}
.nth-search__input {
    width: 140px;
    height: 36px;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    background: #FAF6F0;
    padding: 0 28px 0 12px;
    font-size: 12.5px;
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
    top: 3px;
    right: 3px;
    min-width: 15px;
    height: 15px;
    padding: 0 3px;
    background: #5A4536;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
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

/* ========================================================
   DRAWER MENU BÊN TRÁI — HOẠT ĐỘNG HOÀN HẢO CẢ DESKTOP & MOBILE
   ======================================================== */
.nth-sidebar-drawer {
    position: fixed;
    top: 0;
    left: 0;
    width: min(340px, 86vw);
    height: 100vh;
    height: 100dvh;
    background: #FAF6F0;
    box-shadow: 16px 0 48px rgba(58, 46, 38, 0.25);
    z-index: 2000;
    transform: translateX(-100%);
    visibility: hidden;
    pointer-events: none;
    transition: transform 0.35s cubic-bezier(0.22, 0.61, 0.36, 1),
                visibility 0.35s cubic-bezier(0.22, 0.61, 0.36, 1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.nth-sidebar-drawer.is-open {
    transform: translateX(0);
    visibility: visible;
    pointer-events: auto;
}
.nth-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(58, 46, 38, 0.5);
    backdrop-filter: blur(2px);
    z-index: 1999;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.3s ease, visibility 0.3s;
}
.nth-backdrop.is-open {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}
.nth-sidebar-drawer__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 22px;
    border-bottom: 1px solid #E6D8C8;
    background: #FAF6F0;
}
.nth-sidebar-close {
    background: transparent;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3A2E26;
    cursor: pointer;
    transition: all 0.2s ease;
}
.nth-sidebar-close:hover {
    background: #5A4536;
    color: #FAF6F0;
    border-color: #5A4536;
}
.nth-sidebar-drawer__body {
    flex: 1;
    overflow-y: auto;
    padding: 20px 22px;
}
.nth-sidebar-nav {
    display: flex;
    flex-direction: column;
}
.nth-sidebar-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    color: #3A2E26;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 500;
    transition: color 0.15s ease;
}
.nth-sidebar-link:hover,
.nth-sidebar-link.is-active {
    color: #5A4536;
}
.nth-sidebar-cat-group {
    padding: 14px 0 6px;
}
.nth-sidebar-kicker {
    display: block;
    font-size: 10px;
    letter-spacing: 0.16em;
    color: #7E7065;
    margin-bottom: 10px;
    font-weight: 700;
}
.nth-sidebar-cat-item {
    margin-bottom: 6px;
    border-bottom: 1px solid #FAF6F0;
    padding-bottom: 6px;
}
.nth-sidebar-rootlink {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 8px;
    font-size: 13.5px;
    color: #3A2E26;
    font-weight: 600;
    text-decoration: none;
    border-radius: 2px;
    transition: background 0.15s;
}
.nth-sidebar-rootlink:hover,
.nth-sidebar-rootlink.is-active {
    background: #F3E9DC;
    color: #5A4536;
}
.nth-sidebar-count {
    font-size: 10px;
    background: #E6D8C8;
    color: #5A4536;
    padding: 1px 6px;
    border-radius: 10px;
    font-weight: 600;
}
.nth-sidebar-child-list {
    padding-left: 14px;
    margin-top: 2px;
    margin-bottom: 4px;
}
.nth-sidebar-subitem {
    margin-bottom: 2px;
}
.nth-sidebar-sublink {
    display: block;
    padding: 4px 6px;
    font-size: 12.5px;
    color: #7E7065;
    text-decoration: none;
    border-radius: 2px;
    transition: color 0.15s;
}
.nth-sidebar-sublink:hover,
.nth-sidebar-sublink.is-active {
    color: #5A4536;
}
.nth-sidebar-grand-list {
    padding-left: 14px;
}
.nth-sidebar-grandlink {
    display: block;
    padding: 3px 6px;
    font-size: 11.5px;
    color: #A69282;
    text-decoration: none;
}
.nth-sidebar-grandlink:hover,
.nth-sidebar-grandlink.is-active {
    color: #5A4536;
}
.nth-sidebar-divider {
    height: 1px;
    background: #E6D8C8;
    margin: 12px 0;
}
.nth-sidebar-contact {
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px dashed #E6D8C8;
    font-size: 11.5px;
    color: #7E7065;
    line-height: 1.5;
}
.nth-sidebar-contact__item {
    margin-bottom: 8px;
}
.nth-sidebar-contact__item small {
    display: block;
    color: #A69282;
    font-size: 10px;
}
.nth-sidebar-contact a { color: #5A4536; text-decoration: none; font-weight: 600; }

/* Responsive Rules */
@media (max-width: 991px) {
    .nth-nav { display: none; }
    .nth-search { display: none; }
    .nth-sidebar-toggle__label { display: none; } /* Trên mobile chỉ hiện icon cho gọn */
    .nth-sidebar-toggle { padding: 6px 9px; }
    .nth-navbar__inner {
        height: 60px;
    }
    .nth-brand__title { font-size: 20px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('nth-sidebar-toggle');
    const drawer = document.getElementById('nth-sidebar-drawer');
    const close  = document.getElementById('nth-sidebar-close');
    const bdrop  = document.getElementById('nth-backdrop');
    const header = document.getElementById('site-header');

    function openMenu() {
        if (!drawer || !bdrop) return;
        drawer.classList.add('is-open');
        bdrop.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        if (!drawer || !bdrop) return;
        drawer.classList.remove('is-open');
        bdrop.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (toggle) toggle.addEventListener('click', openMenu);
    if (close)  close.addEventListener('click', closeMenu);
    if (bdrop)  bdrop.addEventListener('click', closeMenu);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && drawer && drawer.classList.contains('is-open')) {
            closeMenu();
        }
    });

    window.addEventListener('scroll', function () {
        if (!header) return;
        if (window.scrollY > 30) {
            header.classList.add('is-scrolled');
        } else {
            header.classList.remove('is-scrolled');
        }
    }, { passive: true });
});
</script>
