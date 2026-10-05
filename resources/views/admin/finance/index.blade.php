@extends('layouts.admin')

@section('title', 'Thống kê tài chính')

@section('content')
<style>
/* ========================================================
   FINANCE DASHBOARD — MODERN DESIGN SYSTEM
======================================================== */
.fn-page { padding-bottom: 2.5rem; }
.fn-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; }
.fn-title-wrap { display: flex; align-items: center; gap: 0.85rem; }
.fn-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #059669, #10b981); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(16,185,129,0.25); }
.fn-title { font-size: 1.35rem; font-weight: 800; color: #3f2f24; margin: 0; letter-spacing: -0.015em; }
.fn-subtitle { font-size: 0.82rem; color: #7e7065; margin: 0.15rem 0 0; }

/* Tabs Navigation */
.fn-nav-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; border-bottom: 1px solid #e6d8c8; padding-bottom: 0.5rem; }
.fn-nav-link { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.5rem 1.15rem; border-radius: 9px; font-size: 0.84rem; font-weight: 600; color: #7e7065; text-decoration: none; transition: all 0.15s ease; background: #fff; border: 1.5px solid #e6d8c8; }
.fn-nav-link:hover { color: #3f2f24; background: #faf6f0; border-color: #d9c7b3; }
.fn-nav-link.active { color: #fff; background: #3f2f24; border-color: #3f2f24; box-shadow: 0 2px 6px rgba(63,47,36,0.2); }

/* Filter Card */
.fn-filter-card { background: #fff; border: 1px solid #e6d8c8; border-radius: 14px; padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
.fn-filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.85rem; align-items: flex-end; }
.fn-field-group { display: flex; flex-direction: column; gap: 0.35rem; }
.fn-label { font-size: 0.76rem; font-weight: 700; color: #6b5848; text-transform: uppercase; letter-spacing: 0.03em; }
.fn-input, .fn-select { height: 38px; border-radius: 8px; border: 1.5px solid #e6d8c8; padding: 0 0.75rem; font-size: 0.84rem; color: #3f2f24; background: #faf6f0; outline: none; transition: all 0.15s ease; width: 100%; }
.fn-input:focus, .fn-select:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.12); background: #fff; }
.fn-btn-submit { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; height: 38px; padding: 0 1.25rem; border-radius: 8px; background: #059669; color: #fff; border: none; font-size: 0.84rem; font-weight: 600; cursor: pointer; transition: background 0.15s; white-space: nowrap; }
.fn-btn-submit:hover { background: #047857; }
.fn-btn-clear { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; height: 38px; padding: 0 1rem; border-radius: 8px; border: 1.5px solid #e6d8c8; background: #fff; color: #7e7065; font-size: 0.84rem; font-weight: 600; text-decoration: none; transition: all 0.15s; white-space: nowrap; }
.fn-btn-clear:hover { border-color: #ef4444; color: #dc2626; background: #fef2f2; }

/* Notice Banner */
.fn-notice { font-size: 0.82rem; color: #7e7065; background: #faf6f0; border: 1px solid #e6d8c8; border-radius: 9px; padding: 0.65rem 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
.fn-notice strong { color: #3f2f24; }

/* Metric KPI Cards (Pastel Tones) */
.fn-metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
.fn-metric-card { background: #fff; border: 1px solid #e6d8c8; border-radius: 12px; padding: 1.15rem; transition: transform 0.15s, box-shadow 0.15s; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
.fn-metric-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.05); }
.fn-metric-title { font-size: 0.78rem; font-weight: 700; color: #7e7065; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.45rem; display: flex; align-items: center; justify-content: space-between; }
.fn-metric-value { font-size: 1.45rem; font-weight: 800; color: #3f2f24; line-height: 1.2; letter-spacing: -0.02em; }
.fn-metric-count { font-size: 0.76rem; font-weight: 600; color: #9c8875; margin-top: 0.35rem; }

/* Themed accents for KPI cards */
.fn-card-total    { border-left: 4px solid #3f2f24; background: linear-gradient(180deg, #fff, #faf6f0); }
.fn-card-pending  { border-left: 4px solid #f59e0b; background: linear-gradient(180deg, #fff, #fffbeb); }
.fn-card-initiated{ border-left: 4px solid #ec4899; background: linear-gradient(180deg, #fff, #fdf2f8); }
.fn-card-paid     { border-left: 4px solid #10b981; background: linear-gradient(180deg, #fff, #ecfdf5); }
.fn-card-failed   { border-left: 4px solid #ef4444; background: linear-gradient(180deg, #fff, #fef2f2); }
.fn-card-cancelled{ border-left: 4px solid #7e7065; background: linear-gradient(180deg, #fff, #faf6f0); }
.fn-card-refund-p { border-left: 4px solid #f97316; background: linear-gradient(180deg, #fff, #fff7ed); }
.fn-card-refunded { border-left: 4px solid #06b6d4; background: linear-gradient(180deg, #fff, #ecfeff); }

/* Table Section */
.fn-section-card { background: #fff; border-radius: 14px; border: 1px solid #e6d8c8; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
.fn-section-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e6d8c8; display: flex; align-items: center; justify-content: space-between; }
.fn-section-title { font-size: 0.95rem; font-weight: 700; color: #3f2f24; margin: 0; }
.fn-table { width: 100%; margin: 0; border-collapse: collapse; }
.fn-table thead th { background: #faf6f0; padding: 0.85rem 1.25rem; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #7e7065; border-bottom: 1px solid #e6d8c8; }
.fn-table tbody td { padding: 1rem 1.25rem; border-bottom: 1px solid #f3e9dc; font-size: 0.85rem; color: #5a4536; vertical-align: middle; }
.fn-table tbody tr:last-child td { border-bottom: none; }
.fn-table tbody tr:hover { background: #faf6f0; }

/* Method Badges */
.fn-badge-cod  { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.65rem; border-radius: 6px; font-weight: 700; font-size: 0.75rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.fn-badge-momo { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.65rem; border-radius: 6px; font-weight: 700; font-size: 0.75rem; background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8; }
.fn-badge-unk  { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.65rem; border-radius: 6px; font-weight: 700; font-size: 0.75rem; background: #faf6f0; color: #6b5848; border: 1px solid #e6d8c8; }
</style>

<div class="fn-page">

{{-- Header --}}
<div class="fn-header">
    <div class="fn-title-wrap">
        <div class="fn-icon"><i class="bi bi-wallet2"></i></div>
        <div>
            <h1 class="fn-title">Thống kê tài chính</h1>
            <p class="fn-subtitle">Tổng hợp giá trị thanh toán theo trạng thái và phương thức.</p>
        </div>
    </div>
</div>

{{-- Navigation Tabs --}}
<div class="fn-nav-tabs">
    <a href="{{ route('admin.finance.index') }}" class="fn-nav-link active">
        <i class="bi bi-bar-chart-fill"></i> Thống kê chỉ số
    </a>
    <a href="{{ route('admin.finance.transactions') }}" class="fn-nav-link">
        <i class="bi bi-receipt"></i> Giao dịch thanh toán
    </a>
</div>

{{-- Filter Form --}}
<div class="fn-filter-card">
    <form method="GET" action="{{ route('admin.finance.index') }}">
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
            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="fn-btn-submit">
                    <i class="bi bi-funnel-fill"></i> Áp dụng bộ lọc
                </button>
                @if(array_filter($filters))
                    <a href="{{ route('admin.finance.index') }}" class="fn-btn-clear" title="Xóa bộ lọc">
                        <i class="bi bi-x-circle"></i> Xóa
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Notification Banner --}}
<div class="fn-notice">
    <i class="bi bi-info-circle-fill text-primary"></i>
    <span>
        Có <strong>{{ number_format($summary->order_count ?? 0) }}</strong> đơn phù hợp.
        Số tiền bao gồm phí vận chuyển; thống kê theo ngày tạo đơn toàn bộ kết quả lọc.
    </span>
</div>

{{-- 8 KPI Metric Cards --}}
<div class="fn-metrics-grid">
    {{-- 1. Tổng giá trị đơn hàng --}}
    <div class="fn-metric-card fn-card-total">
        <div class="fn-metric-title">
            <span>Tổng giá trị đơn hàng</span>
            <i class="bi bi-cash-stack"></i>
        </div>
        <div class="fn-metric-value">{{ number_format($summary->total_amount ?? 0, 0, ',', '.') }}đ</div>
        <div class="fn-metric-count">{{ number_format($summary->order_count ?? 0) }} đơn, bao gồm đơn đã hủy</div>
    </div>

    {{-- 2. Chờ thanh toán --}}
    @php $p = $statusTotals->get('pending'); @endphp
    <div class="fn-metric-card fn-card-pending">
        <div class="fn-metric-title">
            <span>Chờ thanh toán</span>
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="fn-metric-value">{{ number_format($p->total_amount ?? 0, 0, ',', '.') }}đ</div>
        <div class="fn-metric-count">{{ number_format($p->order_count ?? 0) }} đơn</div>
    </div>

    {{-- 3. Đang chờ MoMo --}}
    @php $init = $statusTotals->get('initiated'); @endphp
    <div class="fn-metric-card fn-card-initiated">
        <div class="fn-metric-title">
            <span>Đang chờ MoMo</span>
            <i class="bi bi-phone"></i>
        </div>
        <div class="fn-metric-value">{{ number_format($init->total_amount ?? 0, 0, ',', '.') }}đ</div>
        <div class="fn-metric-count">{{ number_format($init->order_count ?? 0) }} đơn</div>
    </div>

    {{-- 4. Đã thu tiền (Đã thanh toán) --}}
    @php $paid = $statusTotals->get('paid'); @endphp
    <div class="fn-metric-card fn-card-paid">
        <div class="fn-metric-title">
            <span>Đã thu tiền</span>
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="fn-metric-value" style="color:#059669;">{{ number_format($paid->total_amount ?? 0, 0, ',', '.') }}đ</div>
        <div class="fn-metric-count">{{ number_format($paid->order_count ?? 0) }} đơn</div>
    </div>

    {{-- 5. Thanh toán thất bại --}}
    @php $failed = $statusTotals->get('failed'); @endphp
    <div class="fn-metric-card fn-card-failed">
        <div class="fn-metric-title">
            <span>Thanh toán thất bại</span>
            <i class="bi bi-x-circle-fill"></i>
        </div>
        <div class="fn-metric-value">{{ number_format($failed->total_amount ?? 0, 0, ',', '.') }}đ</div>
        <div class="fn-metric-count">{{ number_format($failed->order_count ?? 0) }} đơn</div>
    </div>

    {{-- 6. Đã hủy --}}
    @php $can = $statusTotals->get('cancelled'); @endphp
    <div class="fn-metric-card fn-card-cancelled">
        <div class="fn-metric-title">
            <span>Đã hủy</span>
            <i class="bi bi-slash-circle"></i>
        </div>
        <div class="fn-metric-value">{{ number_format($can->total_amount ?? 0, 0, ',', '.') }}đ</div>
        <div class="fn-metric-count">{{ number_format($can->order_count ?? 0) }} đơn</div>
    </div>

    {{-- 7. Chờ hoàn tiền --}}
    @php $rfp = $statusTotals->get('refund_pending'); @endphp
    <div class="fn-metric-card fn-card-refund-p">
        <div class="fn-metric-title">
            <span>Chờ hoàn tiền</span>
            <i class="bi bi-arrow-return-left"></i>
        </div>
        <div class="fn-metric-value">{{ number_format($rfp->total_amount ?? 0, 0, ',', '.') }}đ</div>
        <div class="fn-metric-count">{{ number_format($rfp->order_count ?? 0) }} đơn</div>
    </div>

    {{-- 8. Đã hoàn tiền --}}
    @php $rfd = $statusTotals->get('refunded'); @endphp
    <div class="fn-metric-card fn-card-refunded">
        <div class="fn-metric-title">
            <span>Đã hoàn tiền</span>
            <i class="bi bi-check2-all"></i>
        </div>
        <div class="fn-metric-value">{{ number_format($rfd->total_amount ?? 0, 0, ',', '.') }}đ</div>
        <div class="fn-metric-count">{{ number_format($rfd->order_count ?? 0) }} đơn</div>
    </div>
</div>

{{-- Method Breakdown Table --}}
<div class="fn-section-card">
    <div class="fn-section-header">
        <h3 class="fn-section-title"><i class="bi bi-pie-chart me-1"></i> Thống kê theo phương thức</h3>
    </div>
    <div style="overflow-x:auto;">
        <table class="fn-table">
            <thead>
                <tr>
                    <th style="width:250px;">Phương thức</th>
                    <th style="width:180px; text-align:right;">Số đơn</th>
                    <th style="width:250px; text-align:right;">Tổng giá trị</th>
                    <th style="width:250px; text-align:right;">Đã thanh toán</th>
                </tr>
            </thead>
            <tbody>
                @foreach($methods as $gw => $lbl)
                    @php $row = $methodTotals->get($gw); @endphp
                    <tr>
                        <td>
                            @if($gw === 'cod')
                                <span class="fn-badge-cod"><i class="bi bi-cash-stack"></i> COD</span>
                            @elseif($gw === 'momo')
                                <span class="fn-badge-momo"><i class="bi bi-wallet2"></i> MoMo</span>
                            @else
                                <span class="fn-badge-unk"><i class="bi bi-question-circle"></i> Chưa xác định</span>
                            @endif
                        </td>
                        <td style="text-align:right; font-weight:600;">
                            {{ number_format($row->order_count ?? 0) }}
                        </td>
                        <td style="text-align:right; font-weight:700;">
                            {{ number_format($row->total_amount ?? 0, 0, ',', '.') }}đ
                        </td>
                        <td style="text-align:right; font-weight:800; color:#059669;">
                            {{ number_format($row->paid_amount ?? 0, 0, ',', '.') }}đ
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

</div>
@endsection
