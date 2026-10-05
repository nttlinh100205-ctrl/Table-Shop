{{-- resources/views/components/header.blade.php --}}
@php
    $categories = \App\Models\Category::with(['subCategories.subSubCategories'])->orderBy('name')->get();
    $cartCount = collect(session('cart', []))->sum('quantity');
@endphp

<header class="nth-header" id="site-header">
    {{-- Thanh điều hướng chính (Menu ngang kiểu cũ + Nút Menu bên trái chi tiết) --}}
    <div class="nth-navbar">
        <div class="nth-container nth-navbar__inner">
            
            {{-- Cụm bên trái: Nút Menu Drawer chi tiết + Logo thương hiệu --}}
            <div class="nth-navbar__left">
                {{-- Nút Menu Bên Trái — Bấm để mở toàn bộ Danh Mục Chi Tiết --}}
                <button type="button" class="nth-sidebar-toggle" id="nth-sidebar-toggle" aria-label="Mở danh mục bên trái" title="Xem danh mục">
                    <span class="nth-hamburger">
                        <span class="nth-bar"></span>
                        <span class="nth-bar"></span>
                        <span class="nth-bar"></span>
                    </span>
                    <span class="nth-sidebar-toggle__label">Danh mục</span>
                </button>

                {{-- Logo chữ Serif Nội Thất Tinh Hoa (Được ngăn cách bằng vạch dọc thanh lịch) --}}
                <a href="{{ route('user.home') }}" class="nth-brand">
                    <span class="nth-brand__title">Nội Thất Tinh Hoa</span>
                    <span class="nth-brand__sub">GỖ ĐẸP CHO NHÀ</span>
                </a>
            </div>

            {{-- Menu ngang chung trên cùng — Font chữ Garamond đồng điệu, rút gọn đầy đủ nghĩa không mất chữ --}}
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
                        {{-- 4 Chuyên mục & Dịch vụ chính rút gọn thanh tao, không bao giờ bị tràn/cắt chữ --}}
                        <li class="nth-nav__item">
                            <a href="{{ route('user.home') }}#khong-gian" class="nth-nav__link">
                                Không Gian Sống
                            </a>
                        </li>

                        <li class="nth-nav__item">
                            <a href="{{ route('user.home') }}#triet-ly" class="nth-nav__link">
                                Triết Lý Tinh Hoa
                            </a>
                        </li>

                        <li class="nth-nav__item">
                            <a href="{{ route('user.home') }}#trai-nghiem" class="nth-nav__link">
                                Trải Nghiệm Khách Hàng
                            </a>
                        </li>

                        <li class="nth-nav__item">
                            <a href="{{ route('user.home') }}#tu-van" class="nth-nav__link">
                                Tư Vấn May Đo
                            </a>
                        </li>
                    @endif
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
                            @if(!empty(auth()->user()->avatar))
                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" style="width: 26px; height: 26px; border-radius: 50%; object-fit: cover; border: 1px solid #C29D62;">
                            @else
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>
                                </svg>
                            @endif
                        </button>
                        <div class="nth-account-menu" id="nth-user-menu">
                            <div class="nth-account-menu__header">
                                <strong>{{ auth()->user()->name }}</strong>
                                <small>{{ auth()->user()->email }}</small>
                                @if(!auth()->user()->isAdmin())
                                    <div class="mt-1" style="font-size: 0.75rem; color: #C29D62;">
                                        <i class="bi bi-shield-fill-check me-1"></i>{{ auth()->user()->tier['name'] }} · {{ number_format(auth()->user()->points_balance ?? 0, 0, ',', '.') }} điểm
                                    </div>
                                @endif
                            </div>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="nth-account-menu__item">Trang quản trị</a>
                            @else
                                <a href="{{ route('user.orders.index') }}" class="nth-account-menu__item">Đơn hàng của tôi</a>
                                <a href="{{ route('user.points.index') }}" class="nth-account-menu__item">
                                    <span>Hồ sơ</span>
                                </a>
                                <a href="{{ route('user.check-in.index') }}" class="nth-account-menu__item"><i class="bi bi-calendar-check me-2" aria-hidden="true"></i>Điểm danh nhận xu</a>
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
         MENU BÊN TRÁI CHI TIẾT (Sidebar Drawer)
         - Chứa TOÀN BỘ CÂY DANH MỤC CHI TIẾT TỪ DATABASE
         - Accordion mở rộng/thu gọn danh mục cấp 1, cấp 2, cấp 3
         - Nằm hoàn toàn ở mép trái trong màn hình, không lệch trang
         ======================================================== --}}
    <div class="nth-sidebar-drawer" id="nth-sidebar-drawer" aria-hidden="true">
        <div class="nth-sidebar-drawer__head">
            <div class="nth-brand">
                <span class="nth-brand__title" style="font-size: 21px;">Nội Thất Tinh Hoa</span>
                <span class="nth-brand__sub">DANH MỤC CHI TIẾT</span>
            </div>
            <button type="button" class="nth-sidebar-close" id="nth-sidebar-close" aria-label="Đóng menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="nth-sidebar-drawer__body">
            {{-- Ô tìm kiếm ngay trong drawer chi tiết --}}
            <form action="{{ route('user.home') }}" method="GET" class="nth-sidebar-search">
                <input type="search" name="q" placeholder="Tìm mẫu bàn, chất liệu gỗ..." value="{{ request('q') }}" aria-label="Tìm kiếm trong danh mục">
                <button type="submit" aria-label="Tìm kiếm">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </button>
            </form>

            {{-- Lối tắt nhanh --}}
            <nav class="nth-sidebar-nav">
                <a href="{{ route('user.home') }}" class="nth-sidebar-link {{ request()->routeIs('user.home') && !request()->hasAny(['category','q','sub_category','sub_sub_category']) ? 'is-active' : '' }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <span>Trang chủ chính</span>
                </a>

                {{-- CỤM DANH MỤC CHI TIẾT (ACCORDION ĐẦY ĐỦ TỪ DATABASE) --}}
                <div class="nth-sidebar-cat-group">
                    <div class="nth-sidebar-kicker-wrap">
                        <span class="nth-sidebar-kicker">TOÀN BỘ DANH MỤC SẢN PHẨM</span>
                        <small class="nth-sidebar-kicker-sub">Bấm vào để xem phân loại chi tiết</small>
                    </div>

                    @foreach($categories as $root)
                        <div class="nth-accordion-item {{ request('category') == $root->id ? 'is-expanded' : '' }}">
                            <div class="nth-accordion-header">
                                <a href="{{ route('user.home', ['category' => $root->id]) }}#product-section"
                                   class="nth-accordion-title {{ request('category') == $root->id ? 'is-active' : '' }}">
                                    <span class="nth-accordion-icon">◈</span>
                                    <span class="nth-accordion-name">{{ $root->name }}</span>
                                </a>

                                @if($root->subCategories->count())
                                    <button type="button" class="nth-accordion-toggle" aria-label="Mở danh mục con">
                                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="1.4">
                                            <path d="M1 1L5 5L9 1"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            @if($root->subCategories->count())
                                <div class="nth-accordion-content" style="{{ request('category') == $root->id ? 'display:block;' : '' }}">
                                    <div class="nth-child-menu">
                                        <a href="{{ route('user.home', ['category' => $root->id]) }}#product-section" class="nth-child-all-link">
                                            ↳ Xem tất cả mẫu {{ $root->name }}
                                        </a>

                                        @foreach($root->subCategories as $child)
                                            <div class="nth-child-item">
                                                <a href="{{ route('user.home', ['sub_category' => $child->id]) }}#product-section"
                                                   class="nth-child-link {{ request('sub_category') == $child->id ? 'is-active' : '' }}">
                                                    <span>• {{ $child->name }}</span>
                                                    @if($child->subSubCategories->count())
                                                        <span class="nth-child-tag">{{ $child->subSubCategories->count() }} loại</span>
                                                    @endif
                                                </a>

                                                {{-- Danh mục cấp 3 (cháu) --}}
                                                @if($child->subSubCategories->count())
                                                    <div class="nth-grand-menu">
                                                        @foreach($child->subSubCategories as $grand)
                                                            <a href="{{ route('user.home', ['sub_sub_category' => $grand->id]) }}#product-section"
                                                               class="nth-grand-link {{ request('sub_sub_category') == $grand->id ? 'is-active' : '' }}">
                                                                - {{ $grand->name }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="nth-sidebar-divider"></div>

                {{-- Chuyên mục & Dịch vụ --}}
                <div class="nth-sidebar-section-title">CHUYÊN MỤC &amp; DỊCH VỤ</div>
                <a href="{{ route('user.home') }}#khong-gian" class="nth-sidebar-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    <span>Không gian nội thất</span>
                </a>
                <a href="{{ route('user.home') }}#triet-ly" class="nth-sidebar-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
                    <span>Triết lý Nội Thất Tinh Hoa</span>
                </a>
                <a href="{{ route('user.home') }}#trai-nghiem" class="nth-sidebar-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <span>Trải nghiệm khách hàng</span>
                </a>
                <a href="{{ route('user.home') }}#tu-van" class="nth-sidebar-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <span>Tư vấn may đo theo yêu cầu</span>
                </a>

                <div class="nth-sidebar-divider"></div>

                {{-- Tài khoản & Đơn hàng --}}
                @auth
                    @if(!auth()->user()->isAdmin())
                        <a href="{{ route('user.points.index') }}" class="nth-sidebar-link {{ request()->routeIs('user.points.*') ? 'is-active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="8" r="5"/><path d="M8 12l-1 10 5-3 5 3-1-10"/></svg>
                            <span>Hạng thành viên</span>
                        </a>
                        <a href="{{ route('user.check-in.index') }}" class="nth-sidebar-link {{ request()->routeIs('user.check-in.*') ? 'is-active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18M8 15l3 3 5-5"/></svg>
                            <span>Điểm danh nhận xu</span>
                        </a>
                    @endif
                    <a href="{{ route('user.orders.index') }}" class="nth-sidebar-link {{ request()->routeIs('user.orders.*') ? 'is-active' : '' }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <span>Đơn hàng của tôi</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nth-sidebar-link" style="background:none;border:none;color:#9b3327;width:100%;text-align:left;cursor:pointer;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            <span>Đăng xuất ({{ auth()->user()->name }})</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nth-sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                        <span>Đăng nhập tài khoản</span>
                    </a>
                    <a href="{{ route('register') }}" class="nth-sidebar-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                        <span>Đăng ký thành viên</span>
                    </a>
                @endauth
            </nav>

            <div class="nth-sidebar-contact">
                <div class="nth-sidebar-contact__item">
                    <small>Hotline xưởng tư vấn:</small>
                    <a href="tel:{{ str_replace(' ', '', config('shop.hotline', '0123456789')) }}">{{ config('shop.hotline', '0123 456 789') }}</a>
                </div>
                <div class="nth-sidebar-contact__item">
                    <small>Showroom &amp; Xưởng sản xuất:</small>
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
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 clamp(16px, 2.5vw, 40px);
}
.nth-navbar .nth-container {
    max-width: 100%;
    padding: 0 clamp(8px, 1.4vw, 24px); /* Đặt sát mép bên trái */
}

/* Navbar Container */
.nth-navbar__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 72px;
    position: relative;
    gap: 16px;
    width: 100%;
}

/* Cụm bên trái: Nút Menu Chi Tiết + Logo Thương hiệu (Đặt sát mép trái) */
.nth-navbar__left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
    padding-right: 18px;
    border-right: 1px solid #E6D8C8; /* Vạch ngăn cách rõ rệt giữa Logo và Trang chủ */
}

/* Nút mở Menu bên trái (Đặt sát mép trái, nổi bật tông nâu gỗ) */
.nth-sidebar-toggle {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 12px;
    background: #5A4536;
    border: 1px solid #5A4536;
    border-radius: 2px;
    color: #FAF6F0;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Manrope', sans-serif;
    letter-spacing: 0.02em;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.nth-sidebar-toggle:hover {
    background: #3F2F24;
    border-color: #3F2F24;
    color: #FAF6F0;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(90, 69, 54, 0.22);
}
.nth-sidebar-toggle .nth-bar {
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
    background: #FAF6F0;
    border-radius: 1px;
    transition: background 0.2s ease;
}
.nth-sidebar-toggle__label {
    display: inline-block;
    line-height: 1;
    font-weight: 600;
}

/* Brand Logo (Được ngăn cách bằng vạch dọc thanh lịch, không bao giờ dính chữ vào Trang chủ) */
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
    font-size: 22px;
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

/* Menu ngang chính (Desktop Navbar — Dùng font Cormorant Garamond đồng điệu Logo, hiển thị trọn vẹn không mất chữ) */
.nth-nav {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    margin-left: 18px;
}
.nth-nav__list {
    display: flex;
    align-items: center;
    gap: clamp(14px, 1.8vw, 26px);
    list-style: none;
    margin: 0;
    padding: 0;
    flex-wrap: nowrap;
}
.nth-nav__link {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 17.5px;
    font-weight: 600;
    letter-spacing: 0.025em;
    color: #3A2E26;
    text-decoration: none;
    padding: 24px 2px;
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

/* Dropdown menu ngang cấp 2 */
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

/* Sub-dropdown menu ngang cấp 3 */
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

/* Các nút thao tác góc phải */
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
    width: 135px;
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
    width: 190px;
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
   MENU BÊN TRÁI CHI TIẾT (Sidebar Drawer)
   - Hoạt động 100% cả Desktop và Mobile
   - Thiết kế Accordion cây danh mục chi tiết
   ======================================================== */
.nth-sidebar-drawer {
    position: fixed;
    top: 0;
    left: 0;
    width: min(360px, 88vw);
    height: 100vh;
    height: 100dvh;
    background: #FAF6F0;
    box-shadow: 18px 0 54px rgba(58, 46, 38, 0.28);
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
.nth-sidebar-search {
    position: relative;
    margin-bottom: 16px;
}
.nth-sidebar-search input {
    width: 100%;
    height: 40px;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    padding: 0 38px 0 14px;
    font-size: 13px;
    color: #3A2E26;
    outline: none;
    transition: all 0.2s ease;
    font-family: inherit;
}
.nth-sidebar-search input:focus {
    border-color: #5A4536;
    background: #FFFFFF;
    box-shadow: 0 2px 8px rgba(90, 69, 54, 0.08);
}
.nth-sidebar-search button {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #7E7065;
    cursor: pointer;
    display: flex;
    align-items: center;
    padding: 0;
}
.nth-sidebar-search button:hover {
    color: #5A4536;
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

/* Accordion Cây danh mục chi tiết bên trái */
.nth-sidebar-cat-group {
    padding: 14px 0 8px;
}
.nth-sidebar-kicker-wrap {
    margin-bottom: 12px;
}
.nth-sidebar-kicker {
    display: block;
    font-size: 10.5px;
    letter-spacing: 0.14em;
    color: #5A4536;
    font-weight: 700;
}
.nth-sidebar-kicker-sub {
    display: block;
    font-size: 11px;
    color: #7E7065;
    margin-top: 2px;
}
.nth-accordion-item {
    border-bottom: 1px solid #EFE3D3;
    padding: 2px 0;
}
.nth-accordion-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.nth-accordion-title {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 6px;
    font-size: 13.5px;
    color: #3A2E26;
    font-weight: 600;
    text-decoration: none;
    flex: 1;
    transition: color 0.15s;
}
.nth-accordion-title:hover,
.nth-accordion-title.is-active {
    color: #5A4536;
}
.nth-accordion-icon {
    font-size: 9px;
    color: #A69282;
}
.nth-accordion-toggle {
    background: transparent;
    border: none;
    padding: 8px 10px;
    color: #7E7065;
    cursor: pointer;
    transition: transform 0.2s ease, color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.nth-accordion-toggle:hover {
    color: #5A4536;
}
.nth-accordion-item.is-expanded .nth-accordion-toggle {
    transform: rotate(180deg);
    color: #5A4536;
}
.nth-accordion-content {
    display: none;
    padding: 4px 0 10px 18px;
}
.nth-child-menu {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.nth-child-all-link {
    font-size: 12px;
    font-weight: 600;
    color: #5A4536;
    text-decoration: none;
    padding: 4px 6px;
    background: #F3E9DC;
    border-radius: 2px;
    display: inline-block;
    margin-bottom: 4px;
}
.nth-child-item {
    margin-bottom: 4px;
}
.nth-child-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 6px;
    font-size: 12.5px;
    color: #5C4F44;
    text-decoration: none;
    transition: color 0.15s;
}
.nth-child-link:hover,
.nth-child-link.is-active {
    color: #5A4536;
    font-weight: 600;
}
.nth-child-tag {
    font-size: 9.5px;
    background: #EFE3D3;
    color: #7E7065;
    padding: 1px 5px;
    border-radius: 2px;
}
.nth-grand-menu {
    padding-left: 14px;
    margin-top: 2px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.nth-grand-link {
    font-size: 11.5px;
    color: #8C7B6E;
    text-decoration: none;
    padding: 2px 6px;
    transition: color 0.15s;
}
.nth-grand-link:hover,
.nth-grand-link.is-active {
    color: #5A4536;
}

.nth-sidebar-divider {
    height: 1px;
    background: #E6D8C8;
    margin: 14px 0;
}
.nth-sidebar-section-title {
    font-size: 10px;
    letter-spacing: 0.14em;
    color: #7E7065;
    font-weight: 700;
    margin-bottom: 6px;
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
@media (max-width: 1199px) {
    .nth-nav__list { gap: 12px; }
    .nth-nav__link { font-size: 12.5px; }
}
@media (max-width: 991px) {
    .nth-nav { display: none; }
    .nth-search { display: none; }
    .nth-navbar__left { border-right: none; padding-right: 0; }
    .nth-sidebar-toggle__label { display: none; }
    .nth-sidebar-toggle { padding: 6px 9px; }
    .nth-navbar__inner { height: 60px; }
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

    // Accordion click mở rộng / thu gọn danh mục con trong drawer bên trái
    document.querySelectorAll('.nth-accordion-toggle').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const item = this.closest('.nth-accordion-item');
            if (!item) return;
            const content = item.querySelector('.nth-accordion-content');
            if (!content) return;

            const isExpanded = item.classList.contains('is-expanded');
            if (isExpanded) {
                item.classList.remove('is-expanded');
                content.style.display = 'none';
            } else {
                item.classList.add('is-expanded');
                content.style.display = 'block';
            }
        });
    });

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
