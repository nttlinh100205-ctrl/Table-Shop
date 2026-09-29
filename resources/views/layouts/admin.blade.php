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

        /* ===== LIVE CHAT ===== */
        #admin-chat-box { position: fixed; bottom: 24px; right: 24px; z-index: 2000; }
        #admin-chat-box #chat-toggle {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.6rem 1.1rem;
            background: #0f172a; color: #fff; border: none;
            border-radius: 100px; font-size: 0.875rem; font-weight: 600;
            cursor: pointer; box-shadow: 0 4px 16px rgba(0,0,0,.25);
            transition: background 0.15s, transform 0.15s; font-family: inherit;
        }
        #admin-chat-box #chat-toggle:hover { background: #1e293b; transform: translateY(-1px); }
        #admin-chat-box #chat-popup {
            display: none; position: absolute; bottom: 58px; right: 0;
            width: 380px; height: 500px; border-radius: 14px; overflow: hidden;
            flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }
        #admin-chat-box #chat-popup.open { display: flex !important; }
        #admin-chat-box #user-list {
            max-height: 160px; overflow-y: auto;
            border-bottom: 1px solid #f1f5f9; background: #fafafa;
        }
        #admin-chat-box .user-item {
            padding: 8px 14px; cursor: pointer;
            border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; transition: background 0.1s;
        }
        #admin-chat-box .user-item .chat-preview { font-size: 0.75rem; color: #94a3b8; }
        #admin-chat-box .user-item:hover { background: #eff6ff; }
        #admin-chat-box .user-item.active { background: #eff6ff; border-left: 3px solid #3b82f6; }
        #admin-chat-box #chat-messages {
            flex: 1; overflow-y: auto; padding: 14px; font-size: 0.875rem; background: #fff;
        }
        #admin-chat-box .msg-row {
            margin-bottom: 10px; padding: 6px 10px;
            border-radius: 8px; font-size: 0.85rem; line-height: 1.4;
        }
        #admin-chat-box .msg-me   { background: #eff6ff; color: #1e40af; text-align: right; margin-left: 20%; }
        #admin-chat-box .msg-other{ background: #f8fafc; color: #334155; margin-right: 20%; }

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
        <button id="chat-toggle">
            <i class="bi bi-chat-dots"></i>
            Chat khách
            <span id="chat-unread-count" class="badge rounded-pill text-bg-danger d-none">0</span>
        </button>
        <div id="chat-popup" class="card border-0">
            <div class="card-header d-flex justify-content-between align-items-center py-2"
                 style="background:#0f172a; border:none;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:8px;height:8px;background:#22c55e;border-radius:50%;"></div>
                    <strong class="text-white" style="font-size:0.875rem;">Hỗ trợ trực tuyến</strong>
                </div>
                <button id="chat-close" class="btn btn-sm text-white py-0 px-2"
                        style="background:rgba(255,255,255,0.1);border-radius:6px;border:none;">
                    <i class="bi bi-x-lg" style="font-size:0.75rem;"></i>
                </button>
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
        const chatPopup = document.getElementById('chat-popup');
        const chatMessages = document.getElementById('chat-messages');
        const chatInput = document.getElementById('chat-input');
        const userList = document.getElementById('user-list');
        const userSearch = document.getElementById('chat-user-search');
        const unreadCount = document.getElementById('chat-unread-count');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        document.getElementById('chat-toggle').onclick = () => {
            chatPopup.classList.add('open');
            loadUsers();
        };
        document.getElementById('chat-close').onclick = () => chatPopup.classList.remove('open');

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
                });
        }

        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }

        function sendMessage() {
            const message = chatInput.value.trim();
            if (!message || !currentUserId) return;
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
                .then(() => { chatInput.value = ''; loadMessages(); })
                .catch(err => console.error(err));
        }

        document.getElementById('send-btn').onclick = sendMessage;
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
