<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Quản trị</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* ============================================
           ADMIN PANEL — DESIGN SYSTEM
        ============================================ */
        :root {
            --admin-sidebar-bg: #0f172a;
            --admin-sidebar-hover: rgba(255,255,255,0.06);
            --admin-sidebar-active: #3b82f6;
            --admin-sidebar-text: #94a3b8;
            --admin-sidebar-text-hover: #e2e8f0;
            --admin-sidebar-width: 256px;
            --admin-topbar-h: 60px;
            --admin-content-bg: #f1f5f9;
            --admin-surface: #ffffff;
            --admin-border: #e2e8f0;
            --admin-primary: #3b82f6;
            --admin-radius: 10px;
            --admin-shadow: 0 1px 3px rgba(0,0,0,0.07), 0 4px 12px rgba(0,0,0,0.05);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--admin-content-bg);
            min-height: 100vh;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== SIDEBAR ===== */
        .admin-sidebar {
            width: var(--admin-sidebar-width);
            min-height: 100vh;
            background: var(--admin-sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
        }

        .sidebar-brand {
            padding: 0 1.25rem;
            height: var(--admin-topbar-h);
            display: flex; align-items: center; gap: 0.65rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            text-decoration: none; flex-shrink: 0;
        }
        .sidebar-brand-icon {
            width: 34px; height: 34px;
            background: var(--admin-primary);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1rem; flex-shrink: 0;
        }
        .sidebar-brand-text { display: flex; flex-direction: column; line-height: 1.2; }
        .sidebar-brand-text strong { color: #f8fafc; font-size: 0.9rem; font-weight: 700; }
        .sidebar-brand-text span { color: #64748b; font-size: 0.68rem; font-weight: 500; }

        .sidebar-nav {
            flex: 1; overflow-y: auto;
            padding: 1rem 0.75rem;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 4px; }

        .sidebar-section-label {
            font-size: 0.65rem; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase;
            color: #475569;
            padding: 0.5rem 0.75rem 0.4rem;
            margin-top: 0.5rem;
        }
        .sidebar-section-label:first-child { margin-top: 0; }

        .sidebar-nav-link {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 0.55rem 0.75rem;
            border-radius: 8px;
            color: var(--admin-sidebar-text);
            text-decoration: none;
            font-size: 0.875rem; font-weight: 500;
            transition: background 0.15s, color 0.15s;
            margin-bottom: 2px;
            position: relative;
        }
        .sidebar-nav-link .nav-icon {
            width: 20px; height: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }
        .sidebar-nav-link:hover {
            color: var(--admin-sidebar-text-hover);
            background: var(--admin-sidebar-hover);
        }
        .sidebar-nav-link.active {
            color: #fff;
            background: rgba(59,130,246,0.18);
        }
        .sidebar-nav-link.active .nav-icon { color: var(--admin-primary); }
        .sidebar-nav-link.active::before {
            content: '';
            position: absolute; left: 0; top: 20%; bottom: 20%;
            width: 3px; background: var(--admin-primary);
            border-radius: 0 3px 3px 0;
        }

        .sidebar-footer {
            padding: 0.875rem 0.75rem;
            border-top: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
        }
        .sidebar-user {
            display: flex; align-items: center; gap: 0.65rem;
            padding: 0.6rem 0.75rem;
            border-radius: 8px; margin-bottom: 0.5rem;
        }
        .sidebar-avatar {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 0.8rem; font-weight: 700; flex-shrink: 0;
        }
        .sidebar-user-info strong {
            display: block; color: #e2e8f0; font-size: 0.82rem; font-weight: 600;
            max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-user-info span { color: #64748b; font-size: 0.7rem; }
        .sidebar-logout-btn {
            display: flex; align-items: center; gap: 0.5rem;
            width: 100%; padding: 0.5rem 0.75rem;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.08);
            background: transparent; color: #94a3b8;
            font-size: 0.82rem; font-weight: 500;
            cursor: pointer; transition: all 0.15s;
        }
        .sidebar-logout-btn:hover {
            background: rgba(239,68,68,0.12);
            border-color: rgba(239,68,68,0.3);
            color: #fca5a5;
        }

        /* ===== MAIN ===== */
        .admin-main {
            margin-left: var(--admin-sidebar-width);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        .admin-topbar {
            height: var(--admin-topbar-h);
            background: var(--admin-surface);
            border-bottom: 1px solid var(--admin-border);
            padding: 0 1.5rem;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 1020;
            gap: 1rem;
        }
        .topbar-left { display: flex; align-items: center; gap: 0.75rem; min-width: 0; }
        .topbar-page-title {
            font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .topbar-right { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
        .topbar-icon-btn {
            width: 36px; height: 36px; border-radius: 8px;
            border: 1px solid var(--admin-border);
            background: transparent; color: #64748b;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 1rem;
            transition: all 0.15s; text-decoration: none;
        }
        .topbar-icon-btn:hover { background: #f1f5f9; border-color: #cbd5e1; color: #1e293b; }
        .topbar-mobile-toggle { display: none; }
        .topbar-user-pill {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.3rem 0.65rem 0.3rem 0.4rem;
            background: #f8fafc; border: 1px solid var(--admin-border);
            border-radius: 100px; font-size: 0.82rem; font-weight: 600; color: #334155;
        }
        .topbar-user-pill .mini-avatar {
            width: 26px; height: 26px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 0.7rem; font-weight: 700;
        }
        .admin-role-badge {
            font-size: 0.68rem; font-weight: 700;
            background: #fee2e2; color: #dc2626;
            padding: 0.15rem 0.45rem; border-radius: 6px;
            text-transform: uppercase; letter-spacing: 0.04em;
        }

        .admin-content { padding: 1.5rem; flex: 1; }

        /* ===== ALERTS ===== */
        .admin-alert {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.85rem 1rem;
            border-radius: var(--admin-radius);
            margin-bottom: 1rem;
            font-size: 0.875rem; font-weight: 500; border: none;
        }
        .admin-alert-success { background: #f0fdf4; color: #166534; border-left: 4px solid #22c55e; }
        .admin-alert-error   { background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }
        .admin-alert .alert-icon { font-size: 1.1rem; flex-shrink: 0; }
        .admin-alert .btn-close { margin-left: auto; opacity: 0.5; }

        /* ===== SHARED CARD ===== */
        .admin-card {
            background: var(--admin-surface);
            border-radius: var(--admin-radius);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-shadow);
            overflow: hidden;
        }
        .admin-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--admin-border);
            display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
        }
        .admin-card-header h6 { margin: 0; font-size: 0.875rem; font-weight: 700; color: #0f172a; }
        .admin-card-body { padding: 1.25rem; }

        /* ===== TABLE ===== */
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table thead th {
            font-size: 0.72rem; font-weight: 700;
            letter-spacing: 0.06em; text-transform: uppercase;
            color: #64748b; background: #f8fafc;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--admin-border);
            white-space: nowrap;
        }
        .admin-table tbody td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle; font-size: 0.875rem; color: #334155;
        }
        .admin-table tbody tr:last-child td { border-bottom: none; }
        .admin-table tbody tr { transition: background 0.1s; }
        .admin-table tbody tr:hover { background: #f8fafc; }

        /* Action buttons */
        .action-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 7px;
            border: 1px solid transparent; font-size: 0.875rem;
            transition: all 0.15s; cursor: pointer; text-decoration: none;
        }
        .action-btn-view  { background: #f1f5f9; color: #64748b; border-color: #e2e8f0; }
        .action-btn-view:hover  { background: #e2e8f0; color: #334155; border-color: #cbd5e1; }
        .action-btn-edit  { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
        .action-btn-edit:hover  { background: #dbeafe; color: #1d4ed8; border-color: #93c5fd; }
        .action-btn-delete{ background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .action-btn-delete:hover{ background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }

        /* ===== OVERLAY (mobile) ===== */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 1029;
            backdrop-filter: blur(2px);
        }
        .sidebar-overlay.show { display: block; }

        /* ===== LIVE CHAT (DRAGGABLE) ===== */
        #chat-toggle {
            position: fixed; bottom: 24px; right: 24px; z-index: 2000;
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.65rem 1.15rem;
            background: #0f172a; color: #fff; border: 1px solid rgba(255,255,255,0.15);
            border-radius: 100px; font-size: 0.875rem; font-weight: 600;
            cursor: grab; box-shadow: 0 6px 20px rgba(0,0,0,.3);
            transition: background 0.15s, transform 0.15s; font-family: inherit;
            user-select: none; touch-action: none;
        }
        #chat-toggle:hover { background: #1e293b; transform: translateY(-1px); }
        #chat-toggle.is-dragging {
            cursor: grabbing !important;
            transform: scale(1.08) !important;
            box-shadow: 0 16px 36px rgba(0,0,0,.45), 0 0 0 2px rgba(59, 130, 246, 0.5) !important;
            transition: none !important;
        }
        #chat-popup {
            display: none; position: fixed; bottom: 24px; right: 24px;
            width: 380px; max-width: calc(100vw - 20px); height: 590px; max-height: calc(100vh - 40px);
            border-radius: 14px; overflow: hidden;
            flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,.25);
            z-index: 2001; transition: box-shadow 0.2s ease;
        }
        #chat-popup.open { display: flex !important; }
        #chat-popup.is-dragging {
            cursor: grabbing !important;
            box-shadow: 0 26px 70px rgba(0,0,0,.4), 0 0 0 2px rgba(59, 130, 246, 0.5) !important;
            opacity: 0.98; transition: none !important;
        }
        #chat-popup.is-dragging #chat-messages,
        #chat-popup.is-dragging #user-list {
            pointer-events: none;
        }
        #admin-chat-header {
            cursor: grab; user-select: none; touch-action: none;
        }
        #admin-chat-header:active, #chat-popup.is-dragging #admin-chat-header {
            cursor: grabbing !important;
        }
        #user-list {
            max-height: 160px; overflow-y: auto;
            border-bottom: 1px solid #f1f5f9; background: #fafafa;
        }
        .user-item {
            padding: 8px 14px; cursor: pointer;
            border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; transition: background 0.1s;
        }
        .user-item .chat-preview { font-size: 0.75rem; color: #94a3b8; }
        .user-item:hover { background: #eff6ff; }
        .user-item.active { background: #eff6ff; border-left: 3px solid #3b82f6; }
        #chat-messages {
            flex: 1; overflow-y: auto; padding: 14px; font-size: 0.875rem; background: #fff;
        }
        .msg-row {
            margin-bottom: 10px; padding: 6px 10px;
            border-radius: 8px; font-size: 0.85rem; line-height: 1.4;
        }
        .msg-me   { background: #eff6ff; color: #1e40af; text-align: right; margin-left: 20%; }
        .msg-other{ background: #f8fafc; color: #334155; margin-right: 20%; }

        /* ===== ADMIN QUICK REPLIES ===== */
        #admin-quick-wrap {
            background: #fff; border-top: 1px solid #f1f5f9;
            padding: 6px 10px 4px;
        }
        .admin-quick-head {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 0.68rem; font-weight: 700; letter-spacing: 0.04em;
            text-transform: uppercase; color: #64748b; margin-bottom: 4px;
        }
        #admin-quick-toggle {
            background: none; border: none; color: #64748b; cursor: pointer;
            font-size: 0.75rem; padding: 0 2px; transition: transform 0.2s;
        }
        #admin-quick-wrap.collapsed #admin-quick-toggle { transform: rotate(180deg); }
        #admin-quick-wrap.collapsed #admin-quick-list { display: none; }
        #admin-quick-list {
            display: flex; flex-wrap: wrap; gap: 5px;
            max-height: 66px; overflow-y: auto;
        }
        .admin-quick-chip {
            display: inline-flex; align-items: center; gap: 4px;
            background: #f8fafc; color: #1e293b;
            border: 1px solid #e2e8f0; border-radius: 999px;
            padding: 3px 10px; font-size: 0.75rem; font-weight: 500;
            font-family: inherit; cursor: pointer; white-space: nowrap;
            transition: background 0.15s, color 0.15s, border-color 0.15s, transform 0.15s;
        }
        .admin-quick-chip i { color: #3b82f6; font-size: 0.78rem; }
        .admin-quick-chip:hover { background: #0f172a; color: #fff; border-color: #0f172a; transform: translateY(-1px); }
        .admin-quick-chip:hover i { color: #fff; }
        .admin-quick-chip:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .admin-quick-chip.suggested {
            background: #eff6ff; border-color: #3b82f6; color: #1d4ed8;
            box-shadow: 0 0 0 2px rgba(59,130,246,0.15);
        }
        .admin-quick-chip.suggested::before {
            content: 'Gợi ý'; font-size: 0.6rem; font-weight: 700;
            background: #3b82f6; color: #fff; border-radius: 999px; padding: 0 5px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991.98px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-main { margin-left: 0; }
            .topbar-mobile-toggle { display: flex; }
            .admin-content { padding: 1rem; }
        }
        @media (max-width: 575.98px) {
            .admin-content { padding: 0.75rem; }
            .topbar-user-pill .user-name-text { display: none; }
        }
    </style>
</head>
<body>
    {{-- Sidebar overlay for mobile --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Sidebar --}}
    <aside class="admin-sidebar" id="adminSidebar">
        {{-- Brand --}}
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="bi bi-speedometer2"></i>
            </div>
            <div class="sidebar-brand-text">
                <strong>Admin Panel</strong>
                <span>Quản trị hệ thống</span>
            </div>
        </a>

        {{-- Navigation --}}
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Tổng quan</div>

            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="nav-icon"><i class="bi bi-grid-1x2"></i></span>
                Dashboard
            </a>

            <div class="sidebar-section-label">Quản lý</div>

            <a href="{{ route('admin.categories.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="bi bi-tags"></i></span>
                Danh mục
            </a>

            <a href="{{ route('admin.products.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="bi bi-box-seam"></i></span>
                Sản phẩm
            </a>

            <a href="{{ route('admin.colors.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.colors.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="bi bi-palette2"></i></span>
                Bảng màu
            </a>

            <a href="{{ route('admin.orders.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="bi bi-receipt"></i></span>
                Đơn hàng
            </a>

            <a href="{{ route('admin.promotions.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="bi bi-ticket-perforated"></i></span>
                Khuyến mãi
            </a>

            <a href="{{ route('admin.finance.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.finance.index') ? 'active' : '' }}">
                <span class="nav-icon"><i class="bi bi-wallet2"></i></span>
                Thống kê tài chính
            </a>

            <a href="{{ route('admin.finance.transactions') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.finance.transactions') ? 'active' : '' }}">
                <span class="nav-icon"><i class="bi bi-credit-card-2-front"></i></span>
                Giao dịch thanh toán
            </a>

            <div class="sidebar-section-label">Hệ thống</div>

            <a href="{{ route('admin.users.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="bi bi-people"></i></span>
                Người dùng
            </a>

            <a href="{{ route('admin.reports.index') }}"
               class="sidebar-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="bi bi-bar-chart-line"></i></span>
                Báo cáo
            </a>
        </nav>

        {{-- Footer --}}
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
                    <span>Quản trị viên</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="sidebar-logout-btn">
                    <i class="bi bi-box-arrow-left"></i>
                    Đăng xuất
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="admin-main">
        {{-- Topbar --}}
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-icon-btn topbar-mobile-toggle" type="button" id="sidebarToggle" aria-label="Mở menu">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="topbar-page-title">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="topbar-right">
                <a href="{{ url('/') }}" class="topbar-icon-btn" title="Xem trang web" target="_blank">
                    <i class="bi bi-globe2"></i>
                </a>
                <div class="topbar-user-pill">
                    <div class="mini-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="user-name-text">{{ auth()->user()->name ?? '' }}</span>
                    <span class="admin-role-badge">Admin</span>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <div class="admin-content">
            @if (session('success'))
                <div class="admin-alert admin-alert-success alert-dismissible fade show" role="alert">
                    <span class="alert-icon"><i class="bi bi-check-circle-fill"></i></span>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Đóng"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="admin-alert admin-alert-error alert-dismissible fade show" role="alert">
                    <span class="alert-icon"><i class="bi bi-exclamation-circle-fill"></i></span>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Đóng"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>


    <div id="admin-chat-box">
        <button id="chat-toggle" title="Chat khách" aria-label="Mở chat khách">
            <i class="bi bi-chat-dots"></i>
            Chat khách
            <span id="chat-unread-count" class="badge rounded-pill text-bg-danger d-none">0</span>
        </button>
        <div id="chat-popup" class="card border-0" role="dialog" aria-labelledby="admin-chat-title">
            <div id="admin-chat-header" class="card-header d-flex justify-content-between align-items-center py-2"
                 style="background:#0f172a; border:none;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-grip-vertical text-secondary" style="font-size:1rem;"></i>
                    <div style="width:8px;height:8px;background:#22c55e;border-radius:50%;box-shadow:0 0 6px #22c55e;"></div>
                    <strong id="admin-chat-title" class="text-white" style="font-size:0.875rem;">Hỗ trợ trực tuyến</strong>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button id="admin-chat-minimize" class="btn btn-sm text-white py-0 px-2"
                            title="Thu nhỏ" style="background:rgba(255,255,255,0.12);border-radius:6px;border:none;">
                        <i class="bi bi-dash-lg" style="font-size:0.75rem;"></i>
                    </button>
                    <button id="chat-close" class="btn btn-sm text-white py-0 px-2"
                            title="Đóng chat" style="background:rgba(255,255,255,0.12);border-radius:6px;border:none;">
                        <i class="bi bi-x-lg" style="font-size:0.75rem;"></i>
                    </button>
                </div>
            </div>
            <div class="p-2" style="background:#fff; border-bottom:1px solid #f1f5f9;">
                <input type="search" id="chat-user-search" class="form-control form-control-sm"
                       placeholder="Tìm khách hàng..." aria-label="Tìm khách hàng"
                       style="border-radius:8px; font-size:0.8rem;">
            </div>
            <div id="user-list">
                <div class="p-3 text-center text-muted"><small>Đang tải...</small></div>
            </div>
            <div id="chat-messages">
                <div class="text-center mt-5 text-muted" style="font-size:0.85rem;">
                    <i class="bi bi-chat-square-text d-block mb-2" style="font-size:2rem;opacity:0.3;"></i>
                    Chọn khách hàng để xem tin nhắn
                </div>
            </div>
            <div id="admin-quick-wrap">
                <div class="admin-quick-head">
                    <span><i class="bi bi-lightning-charge-fill text-warning"></i> Trả lời nhanh</span>
                    <button type="button" id="admin-quick-toggle" title="Ẩn/hiện trả lời nhanh" aria-label="Ẩn/hiện trả lời nhanh">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
                <div id="admin-quick-list"></div>
            </div>
            <div class="card-footer p-2" style="background:#fff; border-top:1px solid #f1f5f9;">
                <div class="input-group input-group-sm">
                    <input type="text" id="chat-input" class="form-control"
                           placeholder="Nhập câu trả lời..." autocomplete="off" maxlength="2000"
                           style="border-radius:8px 0 0 8px; font-size:0.85rem;">
                    <button id="send-btn" class="btn btn-primary" style="border-radius:0 8px 8px 0;">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function () {
        let currentUserId = null;
        let chatUsers = [];
        const toggleBtn = document.getElementById('chat-toggle');
        const chatPopup = document.getElementById('chat-popup');
        const chatHeader = document.getElementById('admin-chat-header');
        const chatMessages = document.getElementById('chat-messages');
        const chatInput = document.getElementById('chat-input');
        const userList = document.getElementById('user-list');
        const userSearch = document.getElementById('chat-user-search');
        const unreadCount = document.getElementById('chat-unread-count');
        const closeBtn = document.getElementById('chat-close');
        const minimizeBtn = document.getElementById('admin-chat-minimize');
        const resetBtn = document.getElementById('admin-chat-reset');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        // ===== DRAG & DROP ENGINE =====
        const STORAGE_POPUP_KEY  = 'table_shop_admin_chat_popup_pos';
        const STORAGE_TOGGLE_KEY = 'table_shop_admin_chat_toggle_pos';

        function clampPosition(el, targetLeft, targetTop) {
            const margin = 10;
            const rect = el.getBoundingClientRect();
            const w = rect.width || el.offsetWidth || 380;
            const h = rect.height || el.offsetHeight || 520;
            const maxLeft = Math.max(margin, window.innerWidth - w - margin);
            const maxTop  = Math.max(margin, window.innerHeight - h - margin);

            const clampedX = Math.round(Math.max(margin, Math.min(targetLeft, maxLeft)));
            const clampedY = Math.round(Math.max(margin, Math.min(targetTop, maxTop)));

            el.style.left = clampedX + 'px';
            el.style.top = clampedY + 'px';
            el.style.right = 'auto';
            el.style.bottom = 'auto';

            return { x: clampedX, y: clampedY };
        }

        function setupDraggable(targetEl, handleEl, storageKey, onJustClicked) {
            let startX = 0, startY = 0;
            let origLeft = 0, origTop = 0;
            let isDragging = false;
            let hasMoved = false;
            const threshold = 6;

            try {
                const saved = JSON.parse(localStorage.getItem(storageKey));
                if (saved && typeof saved.x === 'number' && typeof saved.y === 'number') {
                    clampPosition(targetEl, saved.x, saved.y);
                }
            } catch(e) {}

            function onPointerDown(e) {
                if (e.type === 'mousedown' && e.button !== 0) return;
                // Chỉ bỏ qua nếu bấm vào nút con bên trong handle (ví dụ các nút trong header), không bỏ qua chính handleEl
                if (handleEl !== targetEl && e.target.closest('button')) return;

                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;

                const rect = targetEl.getBoundingClientRect();
                startX = clientX;
                startY = clientY;
                origLeft = rect.left;
                origTop = rect.top;
                hasMoved = false;
                isDragging = false;

                document.addEventListener('mousemove', onPointerMove, { passive: false });
                document.addEventListener('mouseup', onPointerUp);
                document.addEventListener('touchmove', onPointerMove, { passive: false });
                document.addEventListener('touchend', onPointerUp);
            }

            function onPointerMove(e) {
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                const dx = clientX - startX;
                const dy = clientY - startY;

                if (!hasMoved && Math.hypot(dx, dy) > threshold) {
                    hasMoved = true;
                    isDragging = true;
                    targetEl.classList.add('is-dragging');
                    document.body.style.userSelect = 'none';
                }

                if (isDragging) {
                    if (e.cancelable) e.preventDefault();
                    clampPosition(targetEl, origLeft + dx, origTop + dy);
                }
            }

            function onPointerUp() {
                document.removeEventListener('mousemove', onPointerMove);
                document.removeEventListener('mouseup', onPointerUp);
                document.removeEventListener('touchmove', onPointerMove);
                document.removeEventListener('touchend', onPointerUp);
                document.body.style.userSelect = '';

                if (isDragging) {
                    targetEl.classList.remove('is-dragging');
                    isDragging = false;
                    const rect = targetEl.getBoundingClientRect();
                    const pos = clampPosition(targetEl, rect.left, rect.top);
                    try {
                        localStorage.setItem(storageKey, JSON.stringify(pos));
                    } catch(e) {}
                } else if (!hasMoved) {
                    if (typeof onJustClicked === 'function') {
                        onJustClicked();
                    }
                }
            }

            handleEl.addEventListener('mousedown', onPointerDown);
            handleEl.addEventListener('touchstart', onPointerDown, { passive: true });
        }

        // Kéo nút toggle admin & mở chat khi nhấp
        setupDraggable(toggleBtn, toggleBtn, STORAGE_TOGGLE_KEY, function() {
            toggleChat();
        });

        // Bổ sung sự kiện click trực tiếp để đảm bảo luôn mở được chat
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleChat();
        });

        function toggleChat() {
            if (chatPopup.classList.contains('open')) {
                closeChat();
            } else {
                openChat();
            }
        }

        // Kéo khung chat admin qua header
        setupDraggable(chatPopup, chatHeader, STORAGE_POPUP_KEY, null);

        window.addEventListener('resize', function () {
            [toggleBtn, chatPopup].forEach(el => {
                if (el.style.left && el.style.left !== 'auto') {
                    const rect = el.getBoundingClientRect();
                    clampPosition(el, rect.left, rect.top);
                }
            });
        });

        function openChat() {
            chatPopup.classList.add('open');
            const saved = localStorage.getItem(STORAGE_POPUP_KEY);
            if (!saved) {
                const toggleRect = toggleBtn.getBoundingClientRect();
                const popupW = 380;
                const popupH = 520;
                let initialX = window.innerWidth - popupW - 24;
                let initialY = window.innerHeight - popupH - 24;
                if (toggleRect.left < window.innerWidth / 2) {
                    initialX = Math.max(10, toggleRect.left);
                }
                clampPosition(chatPopup, initialX, initialY);
            } else {
                const rect = chatPopup.getBoundingClientRect();
                clampPosition(chatPopup, rect.left, rect.top);
            }
            loadUsers();
        }

        function closeChat() {
            chatPopup.classList.remove('open');
        }

        closeBtn.onclick = closeChat;
        if (minimizeBtn) minimizeBtn.onclick = closeChat;

        function resetChatPosition() {
            try {
                localStorage.removeItem(STORAGE_POPUP_KEY);
            } catch(e) {}
            chatPopup.style.left = 'auto';
            chatPopup.style.top = 'auto';
            chatPopup.style.right = '24px';
            chatPopup.style.bottom = '24px';
        }
        if (resetBtn) resetBtn.onclick = resetChatPosition;
        chatHeader.addEventListener('dblclick', function(e) {
            if (!e.target.closest('button')) {
                resetChatPosition();
            }
        });

        // ===== LOGIC CHAT ADMIN =====

        function loadUsers() {
            fetch('{{ route('admin.chat.users') }}', { headers: { Accept: 'application/json' } })
                .then(r => r.json())
                .then(users => {
                    chatUsers = Array.isArray(users) ? users : [];
                    const unreadTotal = chatUsers.reduce((total, user) => total + Number(user.unread || 0), 0);
                    unreadCount.textContent = unreadTotal;
                    unreadCount.classList.toggle('d-none', unreadTotal === 0);
                    renderUsers();
                })
                .catch(err => console.error(err));
        }

        function renderUsers() {
            const query = userSearch.value.trim().toLocaleLowerCase();
            const filteredUsers = chatUsers.filter(user => String(user.name).toLocaleLowerCase().includes(query));
            let html = '';
            filteredUsers.forEach(user => {
                const active = currentUserId == user.id ? 'active' : '';
                const unread = Number(user.unread || 0);
                const preview = user.last_message || 'Chưa có tin nhắn';
                html += `<div class="user-item ${active}" data-id="${user.id}">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <strong class="text-truncate">${escapeHtml(user.name)}</strong>
                        ${unread ? `<span class="badge rounded-pill text-bg-danger">${unread}</span>` : ''}
                    </div>
                    <div class="chat-preview text-muted text-truncate">${escapeHtml(preview)}</div>
                </div>`;
            });
            userList.innerHTML = html || `<div class="p-2 text-muted">${query ? 'Không tìm thấy hội thoại' : 'Chưa có hội thoại'}</div>`;
            userList.querySelectorAll('.user-item').forEach(el => {
                el.onclick = () => selectUser(parseInt(el.dataset.id, 10), el);
            });
        }

        userSearch.addEventListener('input', renderUsers);

        function selectUser(userId, el) {
            currentUserId = userId;
            document.querySelectorAll('#user-list .user-item').forEach(e => e.classList.remove('active'));
            el.classList.add('active');
            loadMessages();
            loadUsers();
        }

        function loadMessages() {
            if (!currentUserId) return;
            fetch(`/admin/chat/messages/${currentUserId}`, { headers: { Accept: 'application/json' } })
                .then(r => r.json())
                .then(messages => {
                    let html = '';
                    const myId = {{ (int) auth()->id() }};
                    (messages || []).forEach(msg => {
                        const isMe = msg.sender_id == myId;
                        const name = isMe ? 'Bạn' : (msg.sender?.name || 'Khách');
                        html += `<div class="msg-row ${isMe ? 'msg-me' : 'msg-other'}">
                            <strong style="font-size:0.72rem;display:block;margin-bottom:2px;opacity:0.7;">${name}</strong>
                            ${escapeHtml(msg.content)}
                        </div>`;
                    });
                    chatMessages.innerHTML = html || '<div class="text-muted text-center" style="margin-top:3rem;font-size:0.85rem;"><i class="bi bi-chat-square-text d-block mb-2" style="font-size:1.5rem;opacity:0.3;"></i>Chưa có tin nhắn</div>';
                    chatMessages.scrollTop = chatMessages.scrollHeight;

                    // Gợi ý câu trả lời khớp với tin nhắn mới nhất của khách
                    const lastCustomer = [...(messages || [])].reverse().find(m => m.sender_id != myId);
                    const lastMsg = messages && messages.length ? messages[messages.length - 1] : null;
                    const needsReply = lastCustomer && lastMsg && lastMsg.sender_id != myId;
                    const text = needsReply ? String(lastCustomer.content).trim() : '';
                    const match = QUICK_REPLIES.find(r => r.q && r.q === text);
                    renderQuickReplies(match ? match.key : null);
                });
        }

        // ===== CÂU TRẢ LỜI MẪU (khớp với câu hỏi mẫu của khách) =====
        const QUICK_REPLIES = [
            { key: 'greet',    icon: 'bi-emoji-smile',  label: 'Chào khách', q: null,
              a: 'Dạ Nội Thất Tinh Hoa xin chào anh/chị! Shop có thể hỗ trợ gì cho anh/chị ạ?' },
            { key: 'advise',   icon: 'bi-hand-thumbs-up', label: 'Tư vấn chọn bàn', q: 'Xin chào, tôi cần tư vấn chọn bàn phù hợp.',
              a: 'Dạ chào anh/chị! Anh/chị cho shop biết kích thước không gian, mục đích sử dụng (làm việc, ăn uống, học tập...) và ngân sách dự kiến để shop tư vấn mẫu bàn phù hợp nhất nhé.' },
            { key: 'stock',    icon: 'bi-box-seam',     label: 'Còn hàng', q: 'Sản phẩm này hiện còn hàng không ạ?',
              a: 'Dạ sản phẩm anh/chị quan tâm hiện vẫn còn hàng ạ. Anh/chị có thể đặt hàng trực tiếp trên website hoặc gửi tên sản phẩm để shop hỗ trợ nhanh nhé.' },
            { key: 'shipping', icon: 'bi-truck',        label: 'Phí & thời gian giao', q: 'Phí vận chuyển và thời gian giao hàng là bao lâu?',
              a: 'Dạ phí vận chuyển được tính tự động theo địa chỉ nhận hàng ở bước thanh toán (giao qua GHN). Thời gian giao thường từ 2–5 ngày làm việc tùy khu vực ạ.' },
            { key: 'promo',    icon: 'bi-tag',          label: 'Khuyến mãi', q: 'Hiện shop có chương trình khuyến mãi hoặc mã giảm giá nào không?',
              a: 'Dạ anh/chị có thể xem và áp dụng các mã giảm giá đang có ngay tại trang Giỏ hàng. Shop sẽ thông báo khi có chương trình mới ạ.' },
            { key: 'order',    icon: 'bi-receipt',      label: 'Kiểm tra đơn', q: 'Tôi muốn kiểm tra tình trạng đơn hàng của mình.',
              a: 'Dạ anh/chị vui lòng gửi giúp shop mã đơn hàng, shop sẽ kiểm tra tình trạng và phản hồi ngay ạ.' },
            { key: 'warranty', icon: 'bi-shield-check', label: 'Bảo hành & đổi trả', q: 'Chính sách bảo hành và đổi trả của shop như thế nào?',
              a: 'Dạ sản phẩm được bảo hành theo chính sách của shop và hỗ trợ đổi trả nếu có lỗi từ nhà sản xuất. Anh/chị gửi giúp shop mã đơn và hình ảnh sản phẩm để được hỗ trợ nhanh nhất ạ.' },
            { key: 'install',  icon: 'bi-tools',        label: 'Lắp đặt', q: 'Shop có hỗ trợ lắp đặt tại nhà không?',
              a: 'Dạ shop có hỗ trợ lắp đặt tại nhà tùy khu vực. Anh/chị cho shop xin địa chỉ để báo chi phí và lịch lắp đặt cụ thể ạ.' },
            { key: 'payment',  icon: 'bi-credit-card',  label: 'Thanh toán', q: 'Shop hỗ trợ những phương thức thanh toán nào?',
              a: 'Dạ shop hỗ trợ thanh toán khi nhận hàng (COD) và thanh toán online qua ví MoMo ạ.' },
            { key: 'wait',     icon: 'bi-hourglass-split', label: 'Chờ một chút', q: null,
              a: 'Dạ anh/chị vui lòng chờ shop một chút để kiểm tra thông tin nhé.' },
            { key: 'thanks',   icon: 'bi-heart',        label: 'Cảm ơn', q: null,
              a: 'Cảm ơn anh/chị đã quan tâm đến Nội Thất Tinh Hoa! Chúc anh/chị một ngày tốt lành ạ.' },
        ];
        const quickWrap = document.getElementById('admin-quick-wrap');
        const quickList = document.getElementById('admin-quick-list');
        const QUICK_COLLAPSE_KEY = 'table_shop_admin_quick_collapsed';
        let lastSuggestedKey = undefined;
        let isSending = false;

        try { if (localStorage.getItem(QUICK_COLLAPSE_KEY) === '1') quickWrap.classList.add('collapsed'); } catch (e) {}
        document.getElementById('admin-quick-toggle').onclick = () => {
            quickWrap.classList.toggle('collapsed');
            try { localStorage.setItem(QUICK_COLLAPSE_KEY, quickWrap.classList.contains('collapsed') ? '1' : '0'); } catch (e) {}
        };

        function renderQuickReplies(suggestedKey) {
            if (suggestedKey === lastSuggestedKey && quickList.childElementCount) {
                updateQuickDisabled();
                return;
            }
            lastSuggestedKey = suggestedKey;
            const ordered = suggestedKey
                ? [QUICK_REPLIES.find(r => r.key === suggestedKey), ...QUICK_REPLIES.filter(r => r.key !== suggestedKey)]
                : QUICK_REPLIES;
            quickList.innerHTML = ordered.map(r =>
                `<button type="button" class="admin-quick-chip ${r.key === suggestedKey ? 'suggested' : ''}" data-key="${r.key}" title="${escapeHtml(r.a)}"><i class="bi ${r.icon}"></i>${escapeHtml(r.label)}</button>`
            ).join('');
            quickList.scrollTop = 0;
            quickList.querySelectorAll('.admin-quick-chip').forEach(btn => {
                btn.onclick = () => {
                    const reply = QUICK_REPLIES.find(r => r.key === btn.dataset.key);
                    if (reply) sendMessage(reply.a);
                };
            });
            updateQuickDisabled();
        }

        function updateQuickDisabled() {
            quickList.querySelectorAll('.admin-quick-chip').forEach(b => b.disabled = !currentUserId || isSending);
        }

        renderQuickReplies(null);

        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }

        function sendMessage(preset) {
            const fromPreset = typeof preset === 'string';
            const message = (fromPreset ? preset : chatInput.value).trim();
            if (!message || !currentUserId || isSending) return;
            isSending = true;
            updateQuickDisabled();
            fetch('{{ route('admin.chat.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ message, user_id: currentUserId }),
            })
                .then(r => r.json())
                .then(() => { if (!fromPreset) chatInput.value = ''; loadMessages(); })
                .catch(err => console.error(err))
                .finally(() => { isSending = false; updateQuickDisabled(); });
        }

        document.getElementById('send-btn').onclick = () => sendMessage();
        chatInput.onkeypress = e => { if (e.key === 'Enter') sendMessage(); };

        setInterval(() => {
            if (chatPopup.classList.contains('open')) {
                loadMessages();
                loadUsers();
            }
        }, 3000);
    })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const adminSidebar  = document.getElementById('adminSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            adminSidebar.classList.add('show');
            sidebarOverlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            adminSidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.style.overflow = '';
        }
        sidebarToggle?.addEventListener('click', openSidebar);
        sidebarOverlay?.addEventListener('click', closeSidebar);
    </script>
    @stack('scripts')
</body>
</html>
