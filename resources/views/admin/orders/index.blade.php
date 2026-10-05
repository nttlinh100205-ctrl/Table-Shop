@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@section('content')
<style>
/* ========================================================
   ORDER MANAGEMENT — MODERN CLEAN DESIGN SYSTEM
======================================================== */
.om-page { padding-bottom: 2.5rem; }
.om-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
.om-title-wrap { display: flex; align-items: center; gap: 0.85rem; }
.om-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #765338, #8b6544); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(118,83,56,0.25); }
.om-title { font-size: 1.35rem; font-weight: 800; color: #3f2f24; margin: 0; letter-spacing: -0.015em; }
.om-subtitle { font-size: 0.82rem; color: #7e7065; margin: 0.15rem 0 0; }
.om-refresh-btn { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.95rem; border: 1.5px solid #e6d8c8; border-radius: 9px; font-size: 0.8rem; font-weight: 600; color: #6b5848; background: #fff; text-decoration: none; transition: all 0.15s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
.om-refresh-btn:hover { border-color: #8b6544; color: #765338; background: #faf3e8; }

/* Alerts */
.om-alert { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.85rem 1.15rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.85rem; font-weight: 500; border: none; animation: om-slide-in 0.25s ease; transition: opacity 0.5s; }
@keyframes om-slide-in { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
.om-alert-success { background: #f0fdf4; color: #166534; border-left: 4px solid #22c55e; }
.om-alert-danger  { background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }
.om-alert i { font-size: 1.05rem; flex-shrink: 0; margin-top: 0.05rem; }
.om-alert-close { margin-left: auto; background: none; border: none; padding: 0; cursor: pointer; color: inherit; opacity: 0.6; font-size: 0.95rem; transition: opacity 0.15s; }
.om-alert-close:hover { opacity: 1; }

/* Status Tabs */
.om-tabs-wrap { background: #fff; border: 1px solid #e6d8c8; border-radius: 12px; padding: 0.5rem 0.75rem; margin-bottom: 1.25rem; overflow-x: auto; scrollbar-width: none; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
.om-tabs-wrap::-webkit-scrollbar { display: none; }
.om-tabs { display: flex; gap: 0.4rem; flex-wrap: nowrap; white-space: nowrap; list-style: none; margin: 0; padding: 0.1rem 0; }
.om-tab a { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.4rem 0.85rem; border-radius: 8px; font-size: 0.82rem; font-weight: 600; color: #6b5848; background: #faf6f0; border: 1.5px solid #e6d8c8; text-decoration: none; transition: all 0.15s ease; white-space: nowrap; }
.om-tab a:hover { background: #f3e9dc; border-color: #d9c7b3; color: #3f2f24; }
.om-tab a.active { background: #765338; color: #fff; border-color: #765338; box-shadow: 0 2px 8px rgba(118,83,56,0.25); }
.om-tab-count { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 18px; font-size: 0.7rem; font-weight: 700; border-radius: 9999px; background: rgba(0,0,0,0.06); color: inherit; padding: 0 5px; line-height: 1; }
.om-tab a.active .om-tab-count { background: rgba(255,255,255,0.25); color: #fff; }

/* Toolbar */
.om-toolbar { background: #fff; border: 1px solid #e6d8c8; border-radius: 12px; padding: 0.9rem 1.15rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
.om-toolbar-top { display: flex; flex-wrap: wrap; align-items: center; gap: 0.65rem; }
.om-toolbar-divider { height: 1px; background: #f3e9dc; margin: 0.85rem 0; }
.om-toolbar-bottom { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.65rem; }
.om-search-wrap { position: relative; flex: 1; min-width: 220px; }
.om-search-icon { position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); color: #9c8875; font-size: 0.85rem; pointer-events: none; }
.om-input { height: 38px; border-radius: 8px; border: 1.5px solid #e6d8c8; padding: 0 0.75rem 0 2.25rem; font-size: 0.83rem; color: #3f2f24; width: 100%; transition: all 0.15s ease; outline: none; background: #faf6f0; }
.om-input:focus { border-color: #8b6544; box-shadow: 0 0 0 3px rgba(139,101,68,0.1); background: #fff; }
.om-select { height: 38px; border-radius: 8px; border: 1.5px solid #e6d8c8; padding: 0 0.65rem; font-size: 0.83rem; color: #3f2f24; background: #faf6f0; transition: all 0.15s ease; outline: none; }
.om-select:focus { border-color: #8b6544; box-shadow: 0 0 0 3px rgba(139,101,68,0.1); background: #fff; }
.om-date-input { height: 38px; border-radius: 8px; border: 1.5px solid #e6d8c8; padding: 0 0.7rem; font-size: 0.83rem; color: #3f2f24; background: #faf6f0; outline: none; width: 145px; transition: all 0.15s ease; }
.om-date-input:focus { border-color: #8b6544; box-shadow: 0 0 0 3px rgba(139,101,68,0.1); background: #fff; }
.om-btn-filter { display: inline-flex; align-items: center; gap: 0.45rem; height: 38px; padding: 0 1.1rem; border-radius: 8px; background: #765338; color: #fff; border: none; font-size: 0.83rem; font-weight: 600; cursor: pointer; transition: background 0.15s; white-space: nowrap; }
.om-btn-filter:hover { background: #5a4536; }
.om-btn-clear { display: inline-flex; align-items: center; gap: 0.4rem; height: 38px; padding: 0 0.95rem; border-radius: 8px; border: 1.5px solid #e6d8c8; background: #fff; color: #7e7065; font-size: 0.83rem; font-weight: 600; text-decoration: none; transition: all 0.15s; white-space: nowrap; }
.om-btn-clear:hover { border-color: #ef4444; color: #dc2626; background: #fef2f2; }

/* Bulk Action */
.om-bulk-left { display: flex; align-items: center; gap: 0.65rem; }
.om-bulk-pill { display: inline-flex; align-items: center; gap: 0.4rem; background: #faf3e8; color: #5a4536; font-size: 0.8rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 8px; border: 1px solid #dfc8a8; }
.om-bulk-hint { font-size: 0.8rem; color: #7e7065; }
.om-bulk-right { display: flex; align-items: center; gap: 0.65rem; }
.om-bulk-select-wrap { display: flex; flex-direction: column; gap: 0.2rem; }
.om-bulk-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #7e7065; }
.om-bulk-select { height: 36px; min-width: 220px; border-radius: 8px; border: 1.5px solid #e6d8c8; padding: 0 0.75rem; font-size: 0.83rem; color: #3f2f24; background: #faf6f0; outline: none; }
.om-bulk-select:focus { border-color: #8b6544; box-shadow: 0 0 0 3px rgba(139,101,68,0.1); background: #fff; }
.om-btn-apply { display: inline-flex; align-items: center; gap: 0.45rem; height: 36px; padding: 0 1.15rem; border-radius: 8px; background: #3f2f24; color: #fff; border: none; font-size: 0.83rem; font-weight: 600; cursor: pointer; transition: all 0.15s; white-space: nowrap; align-self: flex-end; }
.om-btn-apply:hover:not(:disabled) { background: #3a2e26; }
.om-btn-apply:disabled { opacity: 0.4; cursor: not-allowed; }

/* Table Container & Base */
.om-table-card { background: #fff; border-radius: 14px; border: 1px solid #e6d8c8; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03), 0 4px 16px rgba(0,0,0,0.02); }
.om-table { width: 100%; margin: 0; border-collapse: collapse; }
.om-table thead th { background: #faf6f0; padding: 0.85rem 1rem; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #7e7065; border-bottom: 1px solid #e6d8c8; white-space: nowrap; }
.om-table tbody td { padding: 0.95rem 1rem; border-bottom: 1px solid #f3e9dc; font-size: 0.84rem; color: #5a4536; vertical-align: middle; }
.om-table tbody tr:last-child td { border-bottom: none; }
.om-table tbody tr { transition: background 0.12s ease; }
.om-table tbody tr:hover { background: #faf6f0; }
.om-table tbody tr.om-row-urgent { background: #fffcfc; border-left: 3.5px solid #ef4444; }
.om-check { width: 16px; height: 16px; border-radius: 4px; cursor: pointer; accent-color: #765338; }

/* Order Columns */
.om-order-id { font-weight: 800; font-size: 0.88rem; color: #3f2f24; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem; }
.om-order-id:hover { color: #765338; }
.om-flag { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.68rem; font-weight: 700; padding: 0.18rem 0.5rem; border-radius: 6px; margin-top: 0.25rem; white-space: nowrap; }
.om-flag-cancel { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.om-flag-return { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
.om-date { font-size: 0.83rem; color: #3a2e26; font-weight: 600; }
.om-time { font-size: 0.74rem; color: #9c8875; margin-top: 0.05rem; }
.om-customer-name { font-weight: 700; color: #3f2f24; font-size: 0.86rem; }
.om-customer-phone { font-size: 0.76rem; color: #7e7065; margin-top: 0.15rem; display: flex; align-items: center; gap: 0.3rem; }
.om-products { font-size: 0.82rem; color: #6b5848; line-height: 1.35; }
.om-price { font-weight: 800; font-size: 0.92rem; color: #3f2f24; white-space: nowrap; }
.om-ghn { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.76rem; font-weight: 600; background: #f3e9dc; padding: 0.25rem 0.6rem; border-radius: 6px; color: #5a4536; border: 1px solid #e6d8c8; white-space: nowrap; letter-spacing: 0.02em; }
.om-btn-ghn { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.28rem 0.65rem; border-radius: 6px; border: 1.5px solid #fde68a; color: #b45309; background: #fffbeb; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.15s; }
.om-btn-ghn:hover { background: #fef3c7; border-color: #f59e0b; }

/* ========================================================
   MODERN SOFT PASTEL BADGES (NO MORE "ĐẬM LÈ")
======================================================== */
.om-status-stack { display: flex; flex-direction: column; align-items: flex-start; gap: 0.32rem; }

/* The Soft Pastel Pill */
.om-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.42rem;
    padding: 0.3rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.77rem;
    font-weight: 600;
    line-height: 1.25;
    white-space: nowrap;
    letter-spacing: 0.01em;
    border: 1px solid transparent;
}
.om-pill-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
    background-color: currentColor;
}

/* Color Themes (Gentle, Pastel, Eye-friendly, High Contrast) */
/* 1. Success / Delivered (Xanh lá pastel) */
.om-pill-success { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
.om-pill-success .om-pill-dot { background-color: #10b981; }

/* 2. Shipping / Transit (Xanh dương pastel) */
.om-pill-shipping { background: #faf3e8; color: #1e40af; border-color: #dfc8a8; }
.om-pill-shipping .om-pill-dot { background-color: #8b6544; }

/* 3. Ready / Picking / Processing (Xanh ngọc / Teal pastel) */
.om-pill-ready { background: #f0fdfa; color: #0f766e; border-color: #99f6e4; }
.om-pill-ready .om-pill-dot { background-color: #14b8a6; }

/* 4. Pending / Not shipped (Vàng hổ phách pastel) */
.om-pill-pending { background: #fffbeb; color: #b45309; border-color: #fde68a; }
.om-pill-pending .om-pill-dot { background-color: #f59e0b; }

/* 5. Return / Hoàn hàng (Cam pastel) */
.om-pill-return { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
.om-pill-return .om-pill-dot { background-color: #ea580c; }

/* 6. Danger / Cancelled (Đỏ hồng pastel dịu mắt) */
.om-pill-danger { background: #fef2f2; color: #991b1b; border-color: #fecdd3; }
.om-pill-danger .om-pill-dot { background-color: #ef4444; }

/* 7. Neutral / Default (Xám slate pastel) */
.om-pill-neutral { background: #faf6f0; color: #6b5848; border-color: #e6d8c8; }
.om-pill-neutral .om-pill-dot { background-color: #9c8875; }

/* Global Override: Any Bootstrap badge classes in admin orders will NEVER be "đậm lè" */
.badge.bg-success, .om-badge.bg-success { background-color: #ecfdf5 !important; color: #065f46 !important; border: 1px solid #a7f3d0 !important; }
.badge.bg-primary, .om-badge.bg-primary { background-color: #faf3e8 !important; color: #1e40af !important; border: 1px solid #dfc8a8 !important; }
.badge.bg-danger,  .om-badge.bg-danger  { background-color: #fef2f2 !important; color: #991b1b !important; border: 1px solid #fecdd3 !important; }
.badge.bg-warning, .om-badge.bg-warning { background-color: #fffbeb !important; color: #b45309 !important; border: 1px solid #fde68a !important; }
.badge.bg-info,    .om-badge.bg-info    { background-color: #f0fdfa !important; color: #0f766e !important; border: 1px solid #99f6e4 !important; }
.badge.bg-secondary, .om-badge.bg-secondary { background-color: #faf6f0 !important; color: #6b5848 !important; border: 1px solid #e6d8c8 !important; }

/* Sub-status metadata (Clean text, small icon, no clash) */
.om-status-meta { font-size: 0.75rem; color: #7e7065; display: flex; align-items: center; gap: 0.35rem; line-height: 1.2; margin-top: 0.15rem; }
.om-meta-cancel-req { display: inline-flex; align-items: center; gap: 0.25rem; color: #dc2626; font-weight: 700; background: #fef2f2; padding: 0.18rem 0.5rem; border-radius: 5px; border: 1px dashed #f87171; }
.om-meta-muted { color: #9c8875; display: inline-flex; align-items: center; gap: 0.25rem; }
.om-meta-normal { display: inline-flex; align-items: center; gap: 0.35rem; }
.om-meta-text { color: #6b5848; font-weight: 500; }

/* Payment method mini badges */
.om-pay-tag { font-size: 0.66rem; font-weight: 800; padding: 0.12rem 0.42rem; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.03em; }
.om-pay-momo { background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8; }
.om-pay-cod { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

/* Action Buttons */
.om-btn-view { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; border-radius: 7px; border: 1.5px solid #e6d8c8; background: #fff; color: #6b5848; font-size: 0.78rem; font-weight: 600; text-decoration: none; transition: all 0.15s; white-space: nowrap; }
.om-btn-view:hover { border-color: #8b6544; color: #765338; background: #faf3e8; }
.om-btn-next { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.35rem 0.7rem; border-radius: 7px; border: 1.5px solid #86efac; background: #f0fdf4; color: #15803d; font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.15s; white-space: nowrap; }
.om-btn-next:hover { background: #dcfce7; border-color: #4ade80; color: #166534; }

/* Empty state & Pagination */
.om-empty { padding: 4rem 1rem; text-align: center; }
.om-empty-icon { width: 72px; height: 72px; border-radius: 50%; background: #f3e9dc; color: #9c8875; font-size: 1.8rem; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; }
.om-empty h5 { font-size: 1rem; font-weight: 700; color: #3f2f24; margin-bottom: 0.35rem; }
.om-empty p { font-size: 0.82rem; color: #7e7065; margin-bottom: 1.25rem; }
.om-pagination-footer { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; padding: 0.85rem 1.25rem; border-top: 1px solid #f3e9dc; background: #faf6f0; }
.om-pagination-info { font-size: 0.8rem; color: #7e7065; }
@media (max-width: 991.98px) {
    .om-bulk-right { flex-wrap: wrap; }
    .om-bulk-select { min-width: 160px; }
    .om-table thead th:nth-child(5),
    .om-table tbody td:nth-child(5) { display: none; }
}
</style>

<div class="om-page">

{{-- Header --}}
<div class="om-header">
    <div class="om-title-wrap">
        <div class="om-icon"><i class="bi bi-bag-check-fill"></i></div>
        <div>
            <h1 class="om-title">Quản lý đơn hàng</h1>
            <p class="om-subtitle">Tổng cộng <strong>{{ $orders->total() }}</strong> đơn hàng</p>
        </div>
    </div>
    <a href="{{ request()->url() }}" class="om-refresh-btn">
        <i class="bi bi-arrow-clockwise"></i> Làm mới
    </a>
</div>

{{-- Alerts --}}
@if(session('success'))
    <div class="om-alert om-alert-success" id="om-alert-success">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('success') }}</span>
        <button class="om-alert-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
    </div>
@endif
@if(session('error'))
    <div class="om-alert om-alert-danger" id="om-alert-error">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>{{ session('error') }}</span>
        <button class="om-alert-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
    </div>
@endif

{{-- Status Tabs --}}
<div class="om-tabs-wrap">
    <ul class="om-tabs">
        @foreach($tabs as $key => $tab)
        <li class="om-tab">
            <a class="{{ $activeTab === $key ? 'active' : '' }}"
               href="{{ request()->fullUrlWithQuery(['tab' => $key, 'page' => null]) }}">
                {{ $tab['label'] }}
                <span class="om-tab-count">{{ $tab['count'] }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>

{{-- Toolbar --}}
<div class="om-toolbar">
    <form method="GET" id="om-filter-form">
        <input type="hidden" name="tab" value="{{ $activeTab }}">
        <div class="om-toolbar-top">
            <div class="om-search-wrap">
                <i class="bi bi-search om-search-icon"></i>
                <input type="text" name="search" class="om-input"
                       placeholder="Tên khách, SĐT, mã ĐH, mã GHN, sản phẩm..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>
            <div style="display:flex;align-items:center;gap:0.4rem;">
                <span style="font-size:0.75rem;color:#9c8875;font-weight:600;">Từ</span>
                <input type="date" name="date_from" class="om-date-input" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div style="display:flex;align-items:center;gap:0.4rem;">
                <span style="font-size:0.75rem;color:#9c8875;font-weight:600;">Đến</span>
                <input type="date" name="date_to" class="om-date-input" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <select name="sort" class="om-select" style="width:145px;">
                <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Mới nhất</option>
                <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Cũ nhất</option>
                <option value="amount_desc" @selected(($filters['sort'] ?? '') === 'amount_desc')>Giá cao → thấp</option>
                <option value="amount_asc" @selected(($filters['sort'] ?? '') === 'amount_asc')>Giá thấp → cao</option>
            </select>
            <select name="per_page" class="om-select" style="width:80px;" onchange="this.closest('form').submit()">
                @foreach([25, 50, 100] as $pp)
                    <option value="{{ $pp }}" @selected((int)($filters['per_page'] ?? 25) === $pp)>{{ $pp }}/tr</option>
                @endforeach
            </select>
            <button type="submit" class="om-btn-filter">
                <i class="bi bi-funnel-fill"></i> Lọc
            </button>
            @if(!empty($filters['search']) || !empty($filters['date_from']) || !empty($filters['date_to']) || (!empty($filters['sort']) && $filters['sort'] !== 'newest'))
                <a href="{{ request()->fullUrlWithQuery(['search' => null, 'date_from' => null, 'date_to' => null, 'sort' => null, 'page' => null]) }}" class="om-btn-clear">
                    <i class="bi bi-x-circle"></i> Xóa lọc
                </a>
            @endif
        </div>
    </form>

    <div class="om-toolbar-divider"></div>

    <form id="bulk-status-form" method="POST" action="{{ route('admin.orders.bulkUpdateStatus') }}" onsubmit="return validateBulkForm()">
        @csrf
        <div class="om-toolbar-bottom">
            <div class="om-bulk-left">
                <div class="om-bulk-pill">
                    <i class="bi bi-check2-square"></i>
                    <span id="selected-order-count">0</span> đơn được chọn
                </div>
                <span class="om-bulk-hint d-none d-md-inline">Chọn các đơn cần cập nhật rồi bấm Áp dụng</span>
            </div>
            <div class="om-bulk-right">
                <div class="om-bulk-select-wrap">
                    <span class="om-bulk-label">Vận chuyển hàng loạt</span>
                    <select name="shipping_status" id="bulk-ship-status" class="om-bulk-select" required aria-label="Trạng thái vận chuyển áp dụng">
                        <option value="">-- Chọn trạng thái vận chuyển --</option>
                        @foreach($bulkShipLabels as $shipStatus => $shipLabel)
                            <option value="{{ $shipStatus }}" @if($shipStatus === 'delivered') data-auto-complete="1" @endif>
                                {{ $shipLabel }}@if($shipStatus === 'delivered') (tự Hoàn thành đơn)@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <button id="bulk-status-submit" type="submit" class="om-btn-apply" disabled>
                    <i class="bi bi-check2-all"></i> Áp dụng
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Table Card --}}
<div class="om-table-card">
    <div style="overflow-x:auto;">
        <table class="om-table">
            <thead>
                <tr>
                    <th style="width:44px;text-align:center;padding-left:1.1rem;">
                        <input type="checkbox" class="om-check" id="select-all-orders" aria-label="Chọn tất cả">
                    </th>
                    <th style="width:105px;">Mã ĐH</th>
                    <th style="width:115px;">Ngày đặt</th>
                    <th style="min-width:150px;">Khách hàng</th>
                    <th style="min-width:200px;">Sản phẩm</th>
                    <th style="width:125px;text-align:right;">Tiền hàng</th>
                    <th style="width:125px;">Mã GHN</th>
                    <th style="min-width:185px;">Trạng thái vận chuyển</th>
                    <th style="width:170px;text-align:right;padding-right:1.1rem;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                @php
                    $itemNames = $order->items->map(fn($i) => ($i->product->name ?? 'SP') . ' ×' . $i->quantity)->take(2)->implode(', ');
                    $moreCount = $order->items->count() - 2;
                    if ($moreCount > 0) $itemNames .= ' (+' . $moreCount . ')';

                    $shipStatus = $order->shipping_status ?? 'pending';
                    $shipLabel  = $shippingLabels[$shipStatus] ?? $shipStatus;
                    $ordLabel   = $orderLabels[$order->status] ?? $order->status;
                    $nextShip   = \App\Support\OrderStatus::nextShip($shipStatus);
                    $isUrgent   = $order->status === 'cancel_requested';

                    // Theme pastel dịu mắt theo trạng thái vận chuyển
                    $shipTheme = match($shipStatus) {
                        'delivered' => 'success',
                        'delivering', 'transporting', 'sorting', 'picked', 'storing' => 'shipping',
                        'ready_to_pick', 'picking', 'processing' => 'ready',
                        'pending', 'not_shipped' => 'pending',
                        'return', 'returning', 'return_transporting', 'return_sorting' => 'return',
                        'returned' => 'neutral',
                        'cancelled' => 'danger',
                        default => 'neutral',
                    };
                @endphp
                <tr class="{{ $isUrgent ? 'om-row-urgent' : '' }}">
                    <td style="text-align:center;padding-left:1.1rem;">
                        <input type="checkbox" class="om-check order-checkbox"
                               name="order_ids[]" value="{{ $order->id }}"
                               form="bulk-status-form" aria-label="Chọn đơn #{{ $order->id }}">
                    </td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="om-order-id">#{{ $order->id }}</a>
                        @if($order->status === 'cancel_requested')
                            <div><span class="om-flag om-flag-cancel">⚠ Chờ hủy</span></div>
                        @elseif($order->return_status)
                            <div><span class="om-flag om-flag-return">↩ {{ $order->return_status }}</span></div>
                        @endif
                    </td>
                    <td>
                        <div class="om-date">{{ $order->created_at?->format('d/m/Y') }}</div>
                        <div class="om-time">{{ $order->created_at?->format('H:i') }}</div>
                    </td>
                    <td>
                        <div class="om-customer-name">{{ $order->name }}</div>
                        <div class="om-customer-phone"><i class="bi bi-telephone" style="font-size:0.7rem;"></i> {{ $order->phone }}</div>
                    </td>
                    <td>
                        <div class="om-products text-truncate" style="max-width:240px;" title="{{ $itemNames ?: '—' }}">{{ $itemNames ?: '—' }}</div>
                    </td>
                    <td style="text-align:right;">
                        <span class="om-price">{{ number_format($order->total_price, 0, ',', '.') }}đ</span>
                    </td>
                    <td>
                        @if($order->ghn_order_code)
                            <span class="om-ghn">{{ $order->ghn_order_code }}</span>
                        @elseif($order->status !== 'cancelled' && empty($order->return_status))
                            <form method="POST" action="{{ route('admin.orders.ghnRetry', $order) }}" style="display:inline;" onsubmit="return confirm('Tạo mã GHN cho đơn #{{ $order->id }}?')">
                                @csrf
                                <button type="submit" class="om-btn-ghn"><i class="bi bi-truck"></i> Tạo GHN</button>
                            </form>
                        @else
                            <span style="color:#d9c7b3;">—</span>
                        @endif
                    </td>
                    <td>
                        {{-- Cột Vận chuyển & Trạng thái: Pastel mềm mại, không bị đậm lè --}}
                        <div class="om-status-stack">
                            <span class="om-pill om-pill-{{ $shipTheme }}">
                                <span class="om-pill-dot"></span>
                                <span class="om-pill-text">{{ $shipLabel }}</span>
                            </span>

                            <div class="om-status-meta">
                                @if($order->status === 'cancel_requested')
                                    <span class="om-meta-cancel-req">
                                        <i class="bi bi-exclamation-circle-fill"></i> Chờ duyệt hủy
                                    </span>
                                @elseif($order->status === 'cancelled')
                                    <span class="om-meta-muted">
                                        <i class="bi bi-x-circle"></i> Đơn đã hủy
                                    </span>
                                @else
                                    <span class="om-meta-normal">
                                        @php
                                            $isMomo = str_contains($order->status, 'momo') || $order->paymentTransactions->contains(fn($t) => $t->gateway === 'momo');
                                            $isCod  = str_contains($order->status, 'cod') || $order->paymentTransactions->contains(fn($t) => $t->gateway === 'cod');
                                        @endphp
                                        @if($isMomo)
                                            <span class="om-pay-tag om-pay-momo">MoMo</span>
                                        @elseif($isCod)
                                            <span class="om-pay-tag om-pay-cod">COD</span>
                                        @endif
                                        <span class="om-meta-text">{{ $ordLabel }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="text-align:right;padding-right:1.1rem;">
                        <div style="display:inline-flex;align-items:center;gap:0.4rem;">
                            <a href="{{ route('admin.orders.show', $order) }}" class="om-btn-view">
                                <i class="bi bi-eye"></i> Chi tiết
                            </a>
                            @if($nextShip && empty($order->return_status) && $order->status !== 'cancelled')
                                <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="shipping_status" value="{{ $nextShip }}">
                                    <button type="submit" class="om-btn-next" title="Chuyển bước → {{ $shippingLabels[$nextShip] ?? $nextShip }}">
                                        <i class="bi bi-arrow-right-circle"></i> {{ $shippingLabels[$nextShip] ?? $nextShip }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <div class="om-empty">
                            <div class="om-empty-icon"><i class="bi bi-inbox"></i></div>
                            <h5>Không có đơn hàng nào</h5>
                            <p>Không tìm thấy đơn phù hợp với bộ lọc hiện tại.</p>
                            <a href="{{ route('admin.orders.index') }}" class="om-btn-filter" style="text-decoration:none;height:auto;padding:0.5rem 1.25rem;">
                                <i class="bi bi-arrow-clockwise"></i> Xem tất cả
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages() || $orders->total() > 0)
        <div class="om-pagination-footer">
            <div class="om-pagination-info">
                @if($orders->hasPages())
                    Hiển thị <strong>{{ $orders->firstItem() }}-{{ $orders->lastItem() }}</strong> trong <strong>{{ $orders->total() }}</strong> đơn
                @else
                    <strong>{{ $orders->total() }}</strong> đơn hàng
                @endif
            </div>
            @if($orders->hasPages())
                <div>{{ $orders->onEachSide(1)->links() }}</div>
            @endif
        </div>
    @endif
</div>

</div>
@endsection

@push('scripts')
<script>
(() => {
    const selectAll  = document.getElementById('select-all-orders');
    const checkboxes = [...document.querySelectorAll('.order-checkbox')];
    const countEl    = document.getElementById('selected-order-count');
    const submitBtn  = document.getElementById('bulk-status-submit');

    const sync = () => {
        const n = checkboxes.filter(c => c.checked).length;
        if (countEl)   countEl.textContent = n;
        if (submitBtn) submitBtn.disabled  = n === 0;
        if (selectAll) {
            selectAll.checked       = n > 0 && n === checkboxes.length;
            selectAll.indeterminate = n > 0 && n < checkboxes.length;
        }
    };

    selectAll?.addEventListener('change', () => {
        checkboxes.forEach(c => c.checked = selectAll.checked);
        sync();
    });

    checkboxes.forEach(c => c.addEventListener('change', sync));

    ['om-alert-success','om-alert-error'].forEach(id => {
        const el = document.getElementById(id);
        if (el) setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 500); }, 4000);
    });
})();

function validateBulkForm() {
    const sel = document.getElementById('bulk-ship-status');
    const val = sel?.value ?? '';
    if (!val) {
        alert('Vui lòng chọn trạng thái vận chuyển cần áp dụng.');
        return false;
    }
    const n   = document.querySelectorAll('.order-checkbox:checked').length;
    const lbl = sel.options[sel.selectedIndex].text.trim();
    let msg   = 'Áp dụng trạng thái "' + lbl + '" cho ' + n + ' đơn đã chọn.';
    if (val === 'delivered') {
        msg += '\n-> Các đơn đủ điều kiện sẽ tự động chuyển sang Hoàn thành.';
    }
    return confirm(msg + '\n\nXác nhận thực hiện?');
}
</script>
@endpush