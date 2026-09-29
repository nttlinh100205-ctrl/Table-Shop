<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Store') — Cửa hàng trực tuyến</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            -webkit-font-smoothing: antialiased;
        }
        /* ===== STORE NAVBAR ===== */
        .navbar-store {
            background: linear-gradient(180deg, #edd9a8 0%, #e4cd92 100%) !important;
            box-shadow: 0 1px 0 rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.04);
            min-height: 56px;
            padding-top: 0;
            padding-bottom: 0;
        }
        .navbar-store .navbar-brand {
            color: #2c2416 !important;
            font-weight: 800;
            font-size: 1.05rem;
            white-space: nowrap;
            letter-spacing: -0.02em;
            padding: 0;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            height: 56px;
        }
        .navbar-store .navbar-brand i {
            color: #8b6914;
        }
        .navbar-store .nav-link {
            color: #2c2416 !important;
            font-weight: 600;
            font-size: 0.82rem !important;
            white-space: nowrap !important;
            padding: 0 0.65rem !important;
            line-height: 1.25;
            border-radius: 0.4rem;
            height: 56px;
            display: flex;
            align-items: center;
            transition: background .15s ease, color .15s ease;
        }
        .navbar-store .nav-link:hover,
        .navbar-store .nav-link.active {
            color: #1a1408 !important;
            background: rgba(255,255,255,.55);
        }
        .navbar-store .navbar-nav {
            align-items: center;
            flex-wrap: nowrap;
            gap: 0.1rem;
        }
        .navbar-store .navbar-toggler {
            color: #2c2416 !important;
            padding: 0.25rem 0.5rem;
            border-color: rgba(0,0,0,.12);
        }
        .navbar-store .navbar-toggler-icon {
            filter: invert(1) brightness(0.2);
        }
        .navbar-store .form-control {
            border-color: #d4c08a;
            background: #fffdf8;
            border-radius: 2rem 0 0 2rem;
            font-size: 0.82rem;
            min-width: 160px;
        }
        .navbar-store .form-control:focus {
            box-shadow: 0 0 0 0.15rem rgba(139,105,20,.15);
            border-color: #b8963e;
        }
        .navbar-store .btn-search {
            border-color: #d4c08a;
            background: #fffdf8;
            color: #5c4a1f;
            border-radius: 0 2rem 2rem 0;
            border-left: 0;
        }
        .navbar-store .btn-search:hover {
            background: #fff;
            color: #2c2416;
        }
        .navbar-store .dropdown-menu {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 10px 28px rgba(15,23,42,.12);
            padding: 0.4rem;
            margin-top: 0.35rem !important;
        }
        .navbar-store .dropdown-item {
            border-radius: 0.35rem;
            font-size: 0.88rem;
            padding: 0.45rem 0.75rem;
        }
        .navbar-store .nav-icon-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            padding: 0 !important;
            border-radius: 50%;
        }
        .navbar-store .nav-icon-link:hover,
        .navbar-store .nav-icon-link.active {
            background: rgba(255,255,255,.65);
        }
        @media (max-width: 991px) {
            .navbar-store .form-control {
                border-radius: 0.5rem;
                min-width: 0;
            }
            .navbar-store .btn-search {
                border-radius: 0.5rem;
                border-left: 1px solid #d4c08a;
            }
            .navbar-store .navbar-nav {
                flex-wrap: wrap;
                align-items: stretch;
            }
        }

        /* ===== Multi-level category menu ===== */
        .nav-cat-item {
            position: relative;
        }
        .nav-cat-item > .nav-link {
            white-space: nowrap !important;
            font-size: 0.82rem !important;
        }

        /* Level 2 panel */
        .cat-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 240px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
            padding: 6px 0;
            z-index: 1050;
            list-style: none;
            margin: 0;
        }
        .nav-cat-item:hover > .cat-menu,
        .nav-cat-item:focus-within > .cat-menu {
            display: block;
        }

        .cat-menu > li {
            position: relative;
        }
        .cat-menu a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 16px;
            color: #334155;
            text-decoration: none;
            font-size: 0.92rem;
            white-space: nowrap;
        }
        .cat-menu a:hover,
        .cat-menu a.active {
            background: #f1f5f9;
            color: #0284c7;
        }
        .cat-menu .has-children > a::after {
            content: "›";
            font-size: 1.1rem;
            color: #94a3b8;
            margin-left: 12px;
        }

        /* Level 3 flyout */
        .cat-submenu {
            display: none;
            position: absolute;
            top: 0;
            left: 100%;
            min-width: 220px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
            padding: 6px 0;
            list-style: none;
            margin: 0 0 0 2px;
            z-index: 1060;
        }
        .cat-menu > li.has-children:hover > .cat-submenu,
        .cat-menu > li.has-children:focus-within > .cat-submenu {
            display: block;
        }
        .cat-submenu a {
            padding: 8px 16px;
            font-size: 0.9rem;
        }

        /* Mobile: stack menus */
        @media (max-width: 991.98px) {
            .cat-menu {
                position: static;
                box-shadow: none;
                border: none;
                border-radius: 0;
                display: none;
                background: #1e293b;
                padding-left: 0.5rem;
            }
            .nav-cat-item.open > .cat-menu {
                display: block;
            }
            .cat-menu a {
                color: #e2e8f0;
            }
            .cat-menu a:hover {
                background: #334155;
                color: #fff;
            }
            .cat-submenu {
                position: static;
                box-shadow: none;
                border: none;
                margin: 0;
                padding-left: 1rem;
                background: #0f172a;
                display: none;
            }
            .cat-menu > li.has-children.open > .cat-submenu {
                display: block;
            }
            .cat-menu .has-children > a::after {
                content: "▾";
            }
        }
    </style>
</head>
<body>
    @php
        $navCategories = \App\Models\Category::with(['subCategories.subSubCategories'])->orderBy('name')->get();
    @endphp

    <nav class="navbar navbar-expand-lg navbar-store">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ auth()->check() && !auth()->user()->isAdmin() ? route('user.home') : url('/') }}">
                <i class="bi bi-shop me-1"></i>Store
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                   href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                                   href="{{ route('admin.categories.index') }}">Categories</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                                   href="{{ route('admin.products.index') }}">Products</a>
                            </li>
                        @else
                            {{-- Danh mục 3 cấp (logo Store = trang chủ) --}}
                            @foreach($navCategories as $root)
                                <li class="nav-item nav-cat-item">
                                    <a class="nav-link {{ request('category') == $root->id ? 'active' : '' }}"
                                       href="{{ route('user.home', ['category' => $root->id]) }}">
                                        {{ $root->name }}
                                        @if($root->subCategories->count())
                                            <i class="bi bi-chevron-down" style="font-size:0.7rem;"></i>
                                        @endif
                                    </a>

                                    @if($root->subCategories->count())
                                        <ul class="cat-menu">
                                            @foreach($root->subCategories as $child)
                                                <li class="{{ $child->subSubCategories->count() ? 'has-children' : '' }}">
                                                    <a class="{{ request('sub_category') == $child->id ? 'active' : '' }}"
                                                       href="{{ route('user.home', ['sub_category' => $child->id]) }}">
                                                        {{ $child->name }}
                                                    </a>
                                                    @if($child->subSubCategories->count())
                                                        <ul class="cat-submenu">
                                                            @foreach($child->subSubCategories as $grand)
                                                                <li>
                                                                    <a class="{{ request('sub_sub_category') == $grand->id ? 'active' : '' }}"
                                                                       href="{{ route('user.home', ['sub_sub_category' => $grand->id]) }}">
                                                                        {{ $grand->name }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav align-items-lg-center">
                    @auth
                        @if(!auth()->user()->isAdmin())
                            <li class="nav-item me-lg-2 mb-2 mb-lg-0">
                                <form action="{{ route('user.home') }}" method="GET" class="d-flex">
                                    <div class="input-group input-group-sm">
                                        <input type="search"
                                               name="q"
                                               class="form-control"
                                               placeholder="Tìm sản phẩm..."
                                               value="{{ request('q') }}"
                                               aria-label="Tìm kiếm">
                                        <button class="btn btn-search" type="submit" title="Tìm">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </li>

                            @php
                                $cartCount = collect(session('cart', []))->sum('quantity');
                            @endphp
                            <li class="nav-item">
                                <a href="{{ route('user.orders.index') }}"
                                   class="nav-link nav-icon-link {{ request()->routeIs('user.orders.*') ? 'active' : '' }}"
                                   title="Đơn hàng">
                                    <i class="bi bi-bag-check fs-5"></i>
                                </a>
                            </li>
                            <li class="nav-item me-lg-1">
                                <a href="{{ route('user.cart.index') }}"
                                   class="nav-link nav-icon-link position-relative {{ request()->routeIs('user.cart.*') ? 'active' : '' }}"
                                   title="Giỏ hàng">
                                    <i class="bi bi-cart3 fs-5"></i>
                                    <span id="cart-count-badge"
                                          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                          style="font-size:0.65rem; {{ $cartCount == 0 ? 'display:none' : '' }}">
                                        {{ $cartCount }}
                                    </span>
                                </a>
                            </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                @if(auth()->user()->isAdmin())
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('user.orders.index') }}">
                                        <i class="bi bi-bag-check me-1"></i>Đơn hàng của tôi
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('user.cart.index') }}">
                                        <i class="bi bi-cart3 me-1"></i>Giỏ hàng
                                    </a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">Đăng xuất</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-3">
        @yield('content')
    </main>

    {{-- ===== FOOTER ===== --}}
    <footer style="background:#1a1408; color:#c9a84c; margin-top:3rem; padding:2.5rem 0 1.25rem;">
        <div class="container">
            <div class="row g-4 mb-3">
                <div class="col-md-4">
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                        <span style="font-size:1.3rem;">&#128722;</span>
                        <span style="font-weight:800;font-size:1.05rem;color:#e4cd92;">Store</span>
                    </div>
                    <p style="font-size:0.82rem;color:#9c8640;line-height:1.65;margin:0;">
                        Chuyên cung cấp sản phẩm chất lượng cao.
                        Giao hàng nhanh · Bảo hành đảm bảo.
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 style="color:#e4cd92;font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.75rem;">Danh mục</h6>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0.4rem;">
                        <li><a href="{{ route('user.home') }}" style="color:#9c8640;font-size:0.82rem;text-decoration:none;" class="footer-link">Trang chủ</a></li>
                        <li><a href="{{ route('user.cart.index') }}" style="color:#9c8640;font-size:0.82rem;text-decoration:none;" class="footer-link">Giỏ hàng</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 style="color:#e4cd92;font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.75rem;">Liên hệ</h6>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0.5rem;">
                        <li style="display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:#9c8640;">
                            <i class="bi bi-telephone"></i> 0123 456 789
                        </li>
                        <li style="display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:#9c8640;">
                            <i class="bi bi-envelope"></i> support@store.vn
                        </li>
                        <li style="display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:#9c8640;">
                            <i class="bi bi-geo-alt"></i> Hồ Chí Minh, Việt Nam
                        </li>
                    </ul>
                </div>
            </div>
            <div style="border-top:1px solid #2c2416;padding-top:1rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
                <span style="font-size:0.75rem;color:#6b5821;">&copy; {{ date('Y') }} Store. All rights reserved.</span>
                <div style="display:flex;gap:0.75rem;">
                    <a href="#" style="color:#6b5821;font-size:1rem;"><i class="bi bi-facebook"></i></a>
                    <a href="#" style="color:#6b5821;font-size:1rem;"><i class="bi bi-instagram"></i></a>
                    <a href="#" style="color:#6b5821;font-size:1rem;"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <style>
        .footer-link:hover { color: #e4cd92 !important; }
    </style>

    {{-- Toast giỏ hàng dùng chung (góc phải dưới, tự tắt) --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="cart-toast" class="toast align-items-center text-bg-success border-0 shadow" role="alert" aria-live="polite" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="cart-toast-body">
                    <div class="fw-semibold mb-0">Thông báo</div>
                    <div id="cart-toast-msg">Thêm giỏ hàng thành công.</div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Đóng"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toast thêm giỏ — không cần bấm OK
        window.showCartToast = function (message, isError) {
            var el = document.getElementById('cart-toast');
            var msg = document.getElementById('cart-toast-msg');
            if (!el || !window.bootstrap) return false;
            el.classList.remove('text-bg-success', 'text-bg-danger');
            el.classList.add(isError ? 'text-bg-danger' : 'text-bg-success');
            if (msg) msg.textContent = message || (isError ? 'Có lỗi xảy ra.' : 'Thêm giỏ hàng thành công.');
            var t = bootstrap.Toast.getOrCreateInstance(el, { delay: 2800, autohide: true });
            t.show();
            return true;
        };
        window.updateCartBadge = function (count) {
            var badge = document.getElementById('cart-count-badge');
            if (!badge) return;
            badge.textContent = count;
            badge.style.display = (count && count > 0) ? 'inline-block' : 'none';
        };

        // Mobile: toggle submenu bằng click
        document.querySelectorAll('.nav-cat-item > .nav-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (window.innerWidth < 992 && this.parentElement.querySelector('.cat-menu')) {
                    // Cho phép mở menu; lần click thứ 2 mới đi link nếu đã open
                    if (!this.parentElement.classList.contains('open')) {
                        e.preventDefault();
                        document.querySelectorAll('.nav-cat-item.open').forEach(el => {
                            if (el !== this.parentElement) el.classList.remove('open');
                        });
                        this.parentElement.classList.add('open');
                    }
                }
            });
        });
        document.querySelectorAll('.cat-menu > li.has-children > a').forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (window.innerWidth < 992) {
                    e.preventDefault();
                    this.parentElement.classList.toggle('open');
                }
            });
        });
    </script>
    @stack('scripts')

    {{-- ===== USER LIVECHAT ===== --}}
    @auth
        @if(!auth()->user()->isAdmin())
        <style>
            #chat-box { position: fixed; bottom: 24px; right: 24px; z-index: 2000; }
            #chat-toggle {
                width: 52px; height: 52px;
                background: linear-gradient(135deg, #2563eb, #7c3aed);
                border: none;
                border-radius: 50%;
                color: #fff;
                font-size: 1.2rem;
                box-shadow: 0 4px 16px rgba(37,99,235,0.35);
                transition: transform 0.15s, box-shadow 0.15s;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer;
            }
            #chat-toggle:hover {
                transform: scale(1.07);
                box-shadow: 0 6px 24px rgba(37,99,235,0.45);
            }
            #chat-popup {
                display: none;
                position: absolute;
                bottom: 64px;
                right: 0;
                width: 340px;
                max-height: 460px;
                border-radius: 14px;
                overflow: hidden;
                flex-direction: column;
                box-shadow: 0 16px 48px rgba(0,0,0,0.18), 0 4px 12px rgba(0,0,0,0.1);
                border: 1px solid #e2e8f0;
                background: #fff;
            }
            #chat-popup.open { display: flex !important; }
            #chat-header {
                background: linear-gradient(135deg, #0f172a, #1e3a5f);
                padding: 0.75rem 1rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            #chat-header .chat-title {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: #fff;
                font-weight: 700;
                font-size: 0.875rem;
            }
            #chat-header .online-dot {
                width: 8px; height: 8px;
                background: #22c55e;
                border-radius: 50%;
                display: inline-block;
            }
            #chat-close-btn {
                background: rgba(255,255,255,0.12);
                border: none;
                color: #fff;
                width: 26px; height: 26px;
                border-radius: 6px;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer;
                font-size: 0.8rem;
                transition: background 0.15s;
            }
            #chat-close-btn:hover { background: rgba(255,255,255,0.22); }
            #chat-messages {
                flex: 1;
                overflow-y: auto;
                padding: 1rem;
                background: #f8fafc;
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                min-height: 200px;
                max-height: 280px;
            }
            .chat-bubble {
                max-width: 80%;
                padding: 0.45rem 0.75rem;
                border-radius: 10px;
                font-size: 0.85rem;
                line-height: 1.45;
                word-break: break-word;
            }
            .chat-bubble.me {
                align-self: flex-end;
                background: #2563eb;
                color: #fff;
                border-bottom-right-radius: 3px;
            }
            .chat-bubble.admin {
                align-self: flex-start;
                background: #fff;
                color: #1e293b;
                border: 1px solid #e2e8f0;
                border-bottom-left-radius: 3px;
            }
            .chat-sender-label {
                font-size: 0.7rem;
                font-weight: 600;
                margin-bottom: 2px;
                color: #64748b;
            }
            .chat-sender-label.me { text-align: right; color: #3b82f6; }
            #chat-footer {
                padding: 0.625rem 0.75rem;
                border-top: 1px solid #f1f5f9;
                background: #fff;
                display: flex;
                gap: 0.5rem;
            }
            #chat-input {
                flex: 1;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 0.45rem 0.75rem;
                font-size: 0.85rem;
                outline: none;
                font-family: inherit;
            }
            #chat-input:focus { border-color: #3b82f6; }
            #chat-send {
                background: #2563eb;
                border: none;
                color: #fff;
                border-radius: 8px;
                width: 36px;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer;
                font-size: 0.9rem;
                transition: background 0.15s;
                flex-shrink: 0;
            }
            #chat-send:hover { background: #1d4ed8; }
        </style>
        <div id="chat-box">
            <button id="chat-toggle" title="Chat hỗ trợ">
                <i class="bi bi-chat-dots"></i>
            </button>
            <div id="chat-popup">
                <div id="chat-header">
                    <div class="chat-title">
                        <span class="online-dot"></span>
                        Hỗ trợ khách hàng
                    </div>
                    <button id="chat-close-btn" aria-label="Đóng chat">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div id="chat-messages">
                    <div style="text-align:center;margin:auto;color:#94a3b8;font-size:0.82rem;">
                        <i class="bi bi-chat-square-text d-block mb-1" style="font-size:1.5rem;opacity:0.4;"></i>
                        Bắt đầu trò chuyện với Admin
                    </div>
                </div>
                <div id="chat-footer">
                    <input type="text" id="chat-input" placeholder="Nhập tin nhắn..." autocomplete="off" maxlength="2000">
                    <button id="chat-send" title="Gửi">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('chat-toggle');
            const chatPopup = document.getElementById('chat-popup');
            const closeBtn  = document.getElementById('chat-close-btn');
            const sendBtn   = document.getElementById('chat-send');
            const input     = document.getElementById('chat-input');
            const chatBox   = document.getElementById('chat-messages');
            if (!toggleBtn) return;

            const myId = {{ (int) auth()->id() }};
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

            toggleBtn.onclick = () => {
                chatPopup.classList.add('open');
                toggleBtn.style.display = 'none';
                loadMessages();
            };
            closeBtn.onclick = () => {
                chatPopup.classList.remove('open');
                toggleBtn.style.display = 'flex';
            };

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
            }

            function loadMessages() {
                fetch('{{ route('user.chat.messages') }}', { headers: { Accept: 'application/json' } })
                    .then(r => r.json())
                    .then(messages => {
                        if (!messages || messages.length === 0) {
                            chatBox.innerHTML = `<div style="text-align:center;margin:auto;color:#94a3b8;font-size:0.82rem;"><i class="bi bi-chat-square-text d-block mb-1" style="font-size:1.5rem;opacity:0.4;"></i>Bắt đầu trò chuyện với Admin</div>`;
                            return;
                        }
                        let html = '';
                        messages.forEach(msg => {
                            const isMe = msg.sender_id == myId;
                            const label = isMe ? 'Bạn' : 'Admin';
                            html += `<div>
                                <div class="chat-sender-label ${isMe ? 'me' : ''}">` + label + `</div>
                                <div class="chat-bubble ${isMe ? 'me' : 'admin'}">${escapeHtml(msg.content)}</div>
                            </div>`;
                        });
                        chatBox.innerHTML = html;
                        chatBox.scrollTop = chatBox.scrollHeight;
                    })
                    .catch(err => console.error('Lỗi tải tin nhắn:', err));
            }

            function sendMessage() {
                const message = input.value.trim();
                if (!message) return;
                input.disabled = true;
                sendBtn.disabled = true;
                fetch('{{ route('user.chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message }),
                })
                    .then(r => r.json())
                    .then(() => {
                        input.value = '';
                        input.disabled = false;
                        sendBtn.disabled = false;
                        input.focus();
                        loadMessages();
                    })
                    .catch(err => {
                        console.error(err);
                        input.disabled = false;
                        sendBtn.disabled = false;
                    });
            }

            sendBtn.onclick = sendMessage;
            input.addEventListener('keypress', e => { if (e.key === 'Enter') sendMessage(); });
            setInterval(() => {
                if (chatPopup.classList.contains('open')) loadMessages();
            }, 3000);
        });
        </script>
        @endif
    @endauth
</body>
</html>
