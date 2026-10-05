@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
<style>
    .stat-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e6d8c8;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
        padding: 1.25rem 1.35rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        transition: box-shadow 0.15s, transform 0.15s;
        height: 100%;
    }
    .stat-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.10);
        transform: translateY(-2px);
    }
    .stat-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .stat-card-icon {
        width: 44px; height: 44px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .stat-card-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #7e7065;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    .stat-card-value {
        font-size: 1.85rem;
        font-weight: 800;
        color: #3f2f24;
        line-height: 1;
    }
    .stat-card-footer {
        font-size: 0.78rem;
        color: #9c8875;
        border-top: 1px solid #f3e9dc;
        padding-top: 0.65rem;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .stat-card-footer a {
        color: var(--admin-primary, #8b6544);
        text-decoration: none;
        font-weight: 600;
    }
    .stat-card-footer a:hover { text-decoration: underline; }

    /* Icon color variants */
    .icon-blue   { background: #faf3e8; color: #765338; }
    .icon-green  { background: #f0fdf4; color: #16a34a; }
    .icon-violet { background: #f5f3ff; color: #7c3aed; }
    .icon-amber  { background: #fffbeb; color: #d97706; }
    .icon-rose   { background: #fff1f2; color: #e11d48; }

    .quick-action-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e6d8c8;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .quick-action-header {
        padding: 0.875rem 1.25rem;
        border-bottom: 1px solid #f3e9dc;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: #3f2f24;
    }
    .quick-action-body {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .quick-action-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.7rem 0.875rem;
        border-radius: 8px;
        border: 1px solid #e6d8c8;
        text-decoration: none;
        color: #5a4536;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.15s;
    }
    .quick-action-link:hover {
        border-color: #dfc8a8;
        background: #faf3e8;
        color: #5a4536;
    }
    .quick-action-link .link-icon {
        width: 32px; height: 32px;
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .quick-action-link:hover .link-icon { background: #f0e2ce; }

    .account-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e6d8c8;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .account-card-header {
        padding: 0.875rem 1.25rem;
        border-bottom: 1px solid #f3e9dc;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: #3f2f24;
    }
    .account-card-body { padding: 1.25rem; }
    .account-avatar-section {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid #f3e9dc;
    }
    .account-avatar-lg {
        width: 52px; height: 52px;
        background: linear-gradient(135deg, #8b6544, #c29d62);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1.25rem; font-weight: 700;
        flex-shrink: 0;
    }
    .account-name {
        font-size: 1rem;
        font-weight: 700;
        color: #3f2f24;
        margin-bottom: 0.1rem;
    }
    .account-email {
        font-size: 0.82rem;
        color: #7e7065;
    }
    .account-info-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0;
        font-size: 0.875rem;
    }
    .account-info-row:not(:last-child) {
        border-bottom: 1px solid #faf6f0;
    }
    .account-info-label { color: #9c8875; font-weight: 500; }
    .account-info-value { color: #5a4536; font-weight: 600; }
</style>

{{-- Stats Row --}}
<section class="admin-welcome">
    <div>
        <div class="eyebrow">Nội Thất Tinh Hoa · Quản trị cửa hàng</div>
        <h2>Chào {{ auth()->user()->name }},</h2>
        <p>Theo dõi cửa hàng, chăm sóc khách hàng và quản lý công việc mỗi ngày.</p>
    </div>
    <a class="btn btn-primary px-3 py-2" href="{{ route('admin.orders.index') }}"><i class="bi bi-receipt me-2"></i>Quản lý đơn hàng</a>
</section>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Sản phẩm</div>
                    <div class="stat-card-value">{{ number_format($stats['products']) }}</div>
                </div>
                <div class="stat-card-icon icon-blue">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
            <div class="stat-card-footer">
                <i class="bi bi-arrow-right-circle"></i>
                <a href="{{ route('admin.products.index') }}">Xem tất cả</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Danh mục</div>
                    <div class="stat-card-value">{{ number_format($stats['categories']) }}</div>
                </div>
                <div class="stat-card-icon icon-green">
                    <i class="bi bi-tags"></i>
                </div>
            </div>
            <div class="stat-card-footer">
                <i class="bi bi-arrow-right-circle"></i>
                <a href="{{ route('admin.categories.index') }}">Xem tất cả</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Người dùng</div>
                    <div class="stat-card-value">{{ number_format($stats['users']) }}</div>
                </div>
                <div class="stat-card-icon icon-violet">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="stat-card-footer">
                <i class="bi bi-arrow-right-circle"></i>
                <a href="{{ route('admin.users.index') }}">Xem tất cả</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-card-top">
                <div>
                    <div class="stat-card-label">Quản trị viên</div>
                    <div class="stat-card-value">{{ number_format($stats['admins']) }}</div>
                </div>
                <div class="stat-card-icon icon-amber">
                    <i class="bi bi-shield-check"></i>
                </div>
            </div>
            <div class="stat-card-footer">
                <i class="bi bi-info-circle"></i>
                Tài khoản Admin
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions + Account Info --}}
<div class="row g-3">
    <div class="col-md-6">
        <div class="quick-action-card">
            <div class="quick-action-header">
                <i class="bi bi-lightning-charge text-warning"></i>
                Thao tác nhanh
            </div>
            <div class="quick-action-body">
                <a href="{{ route('admin.products.index') }}" class="quick-action-link">
                    <div class="link-icon icon-blue"><i class="bi bi-box-seam"></i></div>
                    <div>
                        <div class="fw-semibold">Quản lý Sản phẩm</div>
                        <div class="text-muted" style="font-size:0.78rem;">Xem danh sách sản phẩm</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted" style="font-size:0.8rem;"></i>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="quick-action-link">
                    <div class="link-icon icon-green"><i class="bi bi-tags"></i></div>
                    <div>
                        <div class="fw-semibold">Quản lý Danh mục</div>
                        <div class="text-muted" style="font-size:0.78rem;">Cấu hình cây danh mục</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted" style="font-size:0.8rem;"></i>
                </a>
                <a href="{{ route('admin.products.create') }}" class="quick-action-link">
                    <div class="link-icon icon-rose"><i class="bi bi-plus-circle"></i></div>
                    <div>
                        <div class="fw-semibold">Thêm sản phẩm mới</div>
                        <div class="text-muted" style="font-size:0.78rem;">Tạo sản phẩm với biến thể</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted" style="font-size:0.8rem;"></i>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="quick-action-link">
                    <div class="link-icon icon-amber"><i class="bi bi-receipt"></i></div>
                    <div>
                        <div class="fw-semibold">Quản lý Đơn hàng</div>
                        <div class="text-muted" style="font-size:0.78rem;">Xử lý đơn hàng khách</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted" style="font-size:0.8rem;"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="account-card">
            <div class="account-card-header">
                <i class="bi bi-person-circle text-primary"></i>
                Tài khoản đang đăng nhập
            </div>
            <div class="account-card-body">
                <div class="account-avatar-section">
                    <div class="account-avatar-lg">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="account-name">{{ auth()->user()->name }}</div>
                        <div class="account-email">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <div class="account-info-row">
                    <span class="account-info-label">Vai trò</span>
                    <span class="admin-role-badge" style="font-size:0.75rem; padding:0.2rem 0.6rem;">{{ auth()->user()->role }}</span>
                </div>
                <div class="account-info-row">
                    <span class="account-info-label">Trạng thái</span>
                    <span style="font-size:0.82rem; display:flex; align-items:center; gap:0.35rem; color:#16a34a; font-weight:600;">
                        <span style="width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
                        Đang hoạt động
                    </span>
                </div>
                <div class="account-info-row">
                    <span class="account-info-label">Phiên đăng nhập</span>
                    <span class="account-info-value">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
