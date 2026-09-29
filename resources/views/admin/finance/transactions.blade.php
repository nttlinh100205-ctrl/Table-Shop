@extends('layouts.admin')

@section('title', 'Giao dịch thanh toán')

@section('content')
<style>
/* ========================================================
   FINANCE TRANSACTIONS — MODERN DESIGN SYSTEM
======================================================== */
.fn-page { padding-bottom: 2.5rem; }
.fn-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; }
.fn-title-wrap { display: flex; align-items: center; gap: 0.85rem; }
.fn-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #059669, #10b981); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(16,185,129,0.25); }
.fn-title { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.015em; }
.fn-subtitle { font-size: 0.82rem; color: #64748b; margin: 0.15rem 0 0; }

/* Tabs Navigation */
.fn-nav-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; }
.fn-nav-link { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.5rem 1.15rem; border-radius: 9px; font-size: 0.84rem; font-weight: 600; color: #64748b; text-decoration: none; transition: all 0.15s ease; background: #fff; border: 1.5px solid #e2e8f0; }
.fn-nav-link:hover { color: #0f172a; background: #f8fafc; border-color: #cbd5e1; }
.fn-nav-link.active { color: #fff; background: #0f172a; border-color: #0f172a; box-shadow: 0 2px 6px rgba(15,23,42,0.2); }

/* Filter Card */
.fn-filter-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
.fn-filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.85rem; align-items: flex-end; }
.fn-field-group { display: flex; flex-direction: column; gap: 0.35rem; }
.fn-label { font-size: 0.74rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.03em; }
.fn-input, .fn-select { height: 38px; border-radius: 8px; border: 1.5px solid #e2e8f0; padding: 0 0.75rem; font-size: 0.84rem; color: #0f172a; background: #fafbfc; outline: none; transition: all 0.15s ease; width: 100%; }
.fn-input:focus, .fn-select:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.12); background: #fff; }
.fn-btn-submit { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; height: 38px; padding: 0 1.25rem; border-radius: 8px; background: #059669; color: #fff; border: none; font-size: 0.84rem; font-weight: 600; cursor: pointer; transition: background 0.15s; white-space: nowrap; }
.fn-btn-submit:hover { background: #047857; }
.fn-btn-clear { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; height: 38px; padding: 0 1rem; border-radius: 8px; border: 1.5px solid #e2e8f0; background: #fff; color: #64748b; font-size: 0.84rem; font-weight: 600; text-decoration: none; transition: all 0.15s; white-space: nowrap; }
.fn-btn-clear:hover { border-color: #ef4444; color: #dc2626; background: #fef2f2; }

/* Notice Banner */
.fn-notice { font-size: 0.82rem; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 9px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; }
.fn-notice strong { color: #0f172a; }
.fn-notice-sub { font-size: 0.76rem; color: #94a3b8; margin-top: 0.2rem; }

/* Table Container */
.fn-table-card { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
.fn-table-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; }
.fn-table-title { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0; }
.fn-table { width: 100%; margin: 0; border-collapse: collapse; }
.fn-table thead th { background: #f8fafc; padding: 0.85rem 1rem; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
.fn-table tbody td { padding: 0.9rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: 0.84rem; color: #334155; vertical-align: middle; }
.fn-table tbody tr:last-child td { border-bottom: none; }
.fn-table tbody tr:hover { background: #f8fafc; }

/* Cells */
.fn-order-link { font-weight: 800; font-size: 0.88rem; color: #0f172a; text-decoration: none; }
.fn-order-link:hover { color: #2563eb; }
.fn-date { font-size: 0.76rem; color: #64748b; margin-top: 0.1rem; }
.fn-customer-name { font-weight: 700; color: #0f172a; font-size: 0.86rem; }
.fn-customer-phone { font-size: 0.76rem; color: #64748b; margin-top: 0.1rem; }
.fn-price { font-weight: 800; font-size: 0.92rem; color: #0f172a; white-space: nowrap; }

/* Method Badges */
.fn-badge-cod  { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.22rem 0.55rem; border-radius: 6px; font-weight: 700; font-size: 0.74rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; white-space: nowrap; }
.fn-badge-momo { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.22rem 0.55rem; border-radius: 6px; font-weight: 700; font-size: 0.74rem; background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8; white-space: nowrap; }
.fn-badge-unk  { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.22rem 0.55rem; border-radius: 6px; font-weight: 700; font-size: 0.74rem; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; white-space: nowrap; }

/* Status Pastel Badges (No more "đậm lè") */
.fn-pill { display: inline-flex; align-items: center; gap: 0.38rem; padding: 0.28rem 0.68rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; white-space: nowrap; border: 1px solid transparent; }
.fn-pill-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

.fn-pill-paid      { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
.fn-pill-paid .fn-pill-dot { background-color: #10b981; }

.fn-pill-pending   { background: #fffbeb; color: #b45309; border-color: #fde68a; }
.fn-pill-pending .fn-pill-dot { background-color: #f59e0b; }

.fn-pill-initiated { background: #fdf2f8; color: #be185d; border-color: #fbcfe8; }
.fn-pill-initiated .fn-pill-dot { background-color: #ec4899; }

.fn-pill-failed    { background: #fef2f2; color: #991b1b; border-color: #fecdd3; }
.fn-pill-failed .fn-pill-dot { background-color: #ef4444; }

.fn-pill-cancelled { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
.fn-pill-cancelled .fn-pill-dot { background-color: #94a3b8; }

.fn-pill-refund-p  { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
.fn-pill-refund-p .fn-pill-dot { background-color: #ea580c; }

.fn-pill-refunded  { background: #ecfeff; color: #0e7490; border-color: #a5f3fc; }
.fn-pill-refunded .fn-pill-dot { background-color: #06b6d4; }

/* COD Quick Update Form */
.fn-cod-form { display: inline-flex; align-items: center; gap: 0.4rem; }
.fn-status-select { height: 32px; border-radius: 7px; border: 1.5px solid #e2e8f0; padding: 0 0.5rem; font-size: 0.78rem; color: #0f172a; background: #fff; outline: none; transition: border-color 0.15s; }
.fn-status-select:focus { border-color: #10b981; }
.fn-btn-save { height: 32px; padding: 0 0.75rem; border-radius: 7px; background: #059669; color: #fff; border: none; font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: background 0.15s; white-space: nowrap; }
.fn-btn-save:hover { background: #047857; }
.fn-locked { font-size: 0.74rem; color: #94a3b8; display: inline-flex; align-items: center; gap: 0.3rem; }

/* Empty state & Pagination */
.fn-empty { padding: 4rem 1rem; text-align: center; color: #64748b; font-size: 0.88rem; }
.fn-pagination { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; padding: 0.85rem 1.25rem; border-top: 1px solid #f1f5f9; background: #fafbfc; }
</style>

<div class="fn-page">

{{-- Header --}}
<div class="fn-header">
    <div class="fn-title-wrap">
        <div class="fn-icon"><i class="bi bi-wallet2"></i></div>
        <div>
            <h1 class="fn-title">Giao dịch thanh toán</h1>
            <p class="fn-subtitle">Tra cứu thanh toán theo đơn hàng và cập nhật trạng thái COD.</p>
        </div>
    </div>
</div>

{{-- Navigation Tabs --}}
<div class="fn-nav-tabs">
    <a href="{{ route('admin.finance.index') }}" class="fn-nav-link">
        <i class="bi bi-bar-chart-fill"></i> Thống kê chỉ số
    </a>
    <a href="{{ route('admin.finance.transactions') }}" class="fn-nav-link active">
        <i class="bi bi-receipt"></i> Giao dịch thanh toán
    </a>
</div>

{{-- Filter Form --}}
<div class="fn-filter-card">
    <form method="GET" action="{{ route('admin.finance.transactions') }}">
        <div class="fn-filter-row">
            <div class="fn-field-group">
                <label class="fn-label">Tìm đơn hàng</label>
                <input type="text" name="search" class="fn-input" placeholder="Mã đơn, tên hoặc số điện thoại..." value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="fn-field-group">
                <label class="fn-label">Từ ngày tạo đơn</label>
                <input type="date" name="date_from" class="fn-input" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="fn-field-group">
                <label class="fn-label">Đến ngày tạo đơn</label>
                <input type="date" name="date_to" class="fn-input" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="fn-field-group">
                <label class="fn-label">Số tiền từ</label>
                <input type="number" step="any" name="min_amount" class="fn-input" placeholder="Không giới hạn" value="{{ $filters['min_amount'] ?? '' }}">
            </div>
            <div class="fn-field-group">
                <label class="fn-label">Số tiền đến</label>
                <input type="number" step="any" name="max_amount" class="fn-input" placeholder="Không giới hạn" value="{{ $filters['max_amount'] ?? '' }}">
            </div>
            <div class="fn-field-group">
                <label class="fn-label">Phương thức</label>
                <select name="gateway" class="fn-select">
                    <option value="">Tất cả</option>
                    @foreach($methods as $key => $name)
                        <option value="{{ $key }}" @selected(($filters['gateway'] ?? '') === $key)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fn-field-group">
                <label class="fn-label">Trạng thái thanh toán</label>
                <select name="payment_status" class="fn-select">
                    <option value="">Tất cả</option>
                    @foreach($statuses as $key => $name)
                        <option value="{{ $key }}" @selected(($filters['payment_status'] ?? '') === $key)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fn-field-group">
                <label class="fn-label">Sắp xếp</label>
                <select name="sort" class="fn-select">
                    <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Mới nhất</option>
                    <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Cũ nhất</option>
                    <option value="amount_asc" @selected(($filters['sort'] ?? '') === 'amount_asc')>Số tiền tăng dần</option>
                    <option value="amount_desc" @selected(($filters['sort'] ?? '') === 'amount_desc')>Số tiền giảm dần</option>
                </select>
            </div>
            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="fn-btn-submit">
                    <i class="bi bi-funnel-fill"></i> Áp dụng bộ lọc
                </button>
                @if(array_filter($filters))
                    <a href="{{ route('admin.finance.transactions') }}" class="fn-btn-clear" title="Xóa bộ lọc">
                        <i class="bi bi-x-circle"></i> Xóa
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Notification Banner --}}
<div class="fn-notice">
    <div>
        Có <strong>{{ number_format($orders->total()) }}</strong> đơn phù hợp bộ lọc. Số tiền bao gồm phí vận chuyển; ngày lọc là ngày tạo đơn.
    </div>
    <div class="fn-notice-sub">
        <i class="bi bi-lightbulb-fill text-warning me-1"></i>
        COD: xác nhận thu tiền hoặc thất bại; đơn đã thu có thể chuyển sang chờ hoàn tiền rồi xác nhận đã hoàn tiền.
    </div>
</div>

{{-- Table Card --}}
<div class="fn-table-card">
    <div class="fn-table-header">
        <h3 class="fn-table-title"><i class="bi bi-list-check me-1"></i> Danh sách giao dịch ({{ $orders->total() }} đơn)</h3>
    </div>
    <div style="overflow-x:auto;">
        <table class="fn-table">
            <thead>
                <tr>
                    <th style="width:130px;">Đơn hàng</th>
                    <th style="min-width:160px;">Khách hàng</th>
                    <th style="width:130px;">Phương thức</th>
                    <th style="width:140px; text-align:right;">Số tiền</th>
                    <th style="width:160px;">Thanh toán</th>
                    <th style="min-width:210px;">Cập nhật COD</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $isCod = $order->gateway === 'cod' || in_array($order->status, ['cod_ordered', 'cod_paid'], true);
                        $transitions = $codTransitions[$order->payment_status] ?? [];
                        $canUpdateCod = $isCod && count($transitions) > 0 && !in_array($order->status, ['cancelled', 'cancel_requested'], true) && !in_array($order->shipping_status ?? '', ['cancelled', 'return', 'returned'], true);

                        $pillTheme = match($order->payment_status) {
                            'paid'           => 'paid',
                            'pending'        => 'pending',
                            'initiated'      => 'initiated',
                            'failed'         => 'failed',
                            'cancelled'      => 'cancelled',
                            'refund_pending' => 'refund-p',
                            'refunded'       => 'refunded',
                            default          => 'cancelled',
                        };
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="fn-order-link">
                                #{{ $order->id }}
                            </a>
                            <div class="fn-date">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            <div class="fn-customer-name">{{ $order->name }}</div>
                            <div class="fn-customer-phone"><i class="bi bi-telephone" style="font-size:0.7rem;"></i> {{ $order->phone }}</div>
                        </td>
                        <td>
                            @if($order->gateway === 'cod')
                                <span class="fn-badge-cod"><i class="bi bi-cash-stack"></i> COD</span>
                            @elseif($order->gateway === 'momo')
                                <span class="fn-badge-momo"><i class="bi bi-wallet2"></i> MoMo</span>
                            @else
                                <span class="fn-badge-unk"><i class="bi bi-question-circle"></i> Chưa xác định</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <span class="fn-price">{{ number_format($order->total_price, 0, ',', '.') }}đ</span>
                        </td>
                        <td>
                            <span class="fn-pill fn-pill-{{ $pillTheme }}">
                                <span class="fn-pill-dot"></span>
                                <span>{{ $statuses[$order->payment_status] ?? $order->payment_status }}</span>
                            </span>
                        </td>
                        <td>
                            @if($canUpdateCod)
                                <form method="POST" action="{{ route('admin.finance.update-status', $order->id) }}" class="fn-cod-form" onsubmit="return confirm('Cập nhật trạng thái thanh toán COD cho đơn #{{ $order->id }}?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="current_payment_status" value="{{ $order->payment_status }}">
                                    <input type="hidden" name="current_order_status" value="{{ $order->status }}">
                                    <input type="hidden" name="current_payment_id" value="{{ (int)($order->payment_id ?? 0) }}">
                                    <select name="payment_status" class="fn-status-select" required>
                                        @foreach($transitions as $nextStatus)
                                            <option value="{{ $nextStatus }}" @selected($nextStatus === $order->payment_status)>
                                                {{ $statuses[$nextStatus] ?? $nextStatus }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="fn-btn-save">Lưu</button>
                                </form>
                            @elseif(!$isCod)
                                <span class="fn-locked"><i class="bi bi-lock-fill"></i> Tự động qua MoMo</span>
                            @else
                                <span class="text-muted small">Không thể cập nhật</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="fn-empty">
                            <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; color:#94a3b8;"></i>
                            Không có đơn hàng nào phù hợp với bộ lọc.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="fn-pagination">
            <div style="font-size: 0.8rem; color: #64748b;">
                Hiển thị <strong>{{ $orders->firstItem() }}-{{ $orders->lastItem() }}</strong> trong <strong>{{ $orders->total() }}</strong> đơn
            </div>
            <div>
                {{ $orders->links() }}
            </div>
        </div>
    @endif
</div>

</div>
@endsection
