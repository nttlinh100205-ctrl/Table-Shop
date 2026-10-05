@extends('layouts.admin')

@section('title', 'Quản lý Khuyến mãi & Voucher')

@section('content')
<style>
    .stat-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e6d8c8;
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }
    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }
    .stat-val { font-size: 1.4rem; font-weight: 800; color: #3f2f24; line-height: 1.2; }
    .stat-lbl { font-size: 0.78rem; font-weight: 600; color: #7e7065; text-transform: uppercase; letter-spacing: 0.04em; }

    .coupon-code-badge {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.88rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        background: #faf3e8;
        color: #5a4536;
        border: 1px dashed #c29d62;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .btn-copy-code {
        cursor: pointer;
        border: none;
        background: transparent;
        padding: 0;
        color: #60a5fa;
        font-size: 0.82rem;
        transition: color 0.15s;
    }
    .btn-copy-code:hover { color: #5a4536; }

    .discount-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 0.25rem 0.55rem;
        border-radius: 6px;
    }
    .discount-pill-percent { background: #fef3c7; color: #b45309; }
    .discount-pill-fixed { background: #dcfce7; color: #15803d; }

    .usage-progress {
        width: 100px;
        height: 6px;
        border-radius: 4px;
        background: #e6d8c8;
        overflow: hidden;
        margin-top: 4px;
    }
    .usage-progress-bar {
        height: 100%;
        background: #8b6544;
        border-radius: 4px;
    }
</style>

<div class="container-fluid px-0">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1" style="color:#3f2f24;">Quản lý Khuyến mãi & Voucher</h2>
            <p class="text-muted mb-0" style="font-size:0.82rem;">Tạo và quản lý các chương trình giảm giá theo %, tiền mặt, lượt dùng và hạn sử dụng</p>
        </div>
        <a href="{{ route('admin.promotions.create') }}"
           class="btn btn-primary d-flex align-items-center gap-2"
           style="border-radius:8px; font-size:0.875rem; font-weight:600; padding:0.5rem 1rem;">
            <i class="bi bi-plus-lg"></i> Thêm mã khuyến mãi
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#faf3e8; color:#765338;">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
                <div>
                    <div class="stat-val">{{ number_format($stats['total'] ?? 0) }}</div>
                    <div class="stat-lbl">Tổng số mã</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="stat-val">{{ number_format($stats['active'] ?? 0) }}</div>
                    <div class="stat-lbl">Đang hoạt động</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef2f2; color:#dc2626;">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <div class="stat-val">{{ number_format($stats['expired'] ?? 0) }}</div>
                    <div class="stat-lbl">Hết hạn sử dụng</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#faf5ff; color:#9333ea;">
                    <i class="bi bi-bag-check"></i>
                </div>
                <div>
                    <div class="stat-val">{{ number_format($stats['used_sum'] ?? 0) }}</div>
                    <div class="stat-lbl">Tổng lượt đã dùng</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.promotions.index') }}" class="admin-card mb-4">
        <div class="admin-card-body" style="padding:1rem 1.25rem;">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" style="font-size:0.75rem;font-weight:700;color:#7e7065;margin-bottom:0.25rem;">TÌM KIẾM MÃ / TÊN</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-right:none;border-color:#e6d8c8;">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control"
                               placeholder="VD: SALE20, GIAM50K, Khai trương..."
                               style="border-left:none;border-color:#e6d8c8;font-size:0.875rem;">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:0.75rem;font-weight:700;color:#7e7065;margin-bottom:0.25rem;">LOẠI GIẢM GIÁ</label>
                    <select name="discount_type" class="form-select" style="border-color:#e6d8c8;font-size:0.875rem;">
                        <option value="">Tất cả loại</option>
                        <option value="percent" @selected(request('discount_type') === 'percent')>Giảm theo phần trăm (%)</option>
                        <option value="fixed" @selected(request('discount_type') === 'fixed')>Giảm theo số tiền (VNĐ)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:0.75rem;font-weight:700;color:#7e7065;margin-bottom:0.25rem;">TRẠNG THÁI</label>
                    <select name="status" class="form-select" style="border-color:#e6d8c8;font-size:0.875rem;">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" @selected(request('status') === 'active')>Đang diễn ra (Hợp lệ)</option>
                        <option value="expired" @selected(request('status') === 'expired')>Hết hạn sử dụng</option>
                        <option value="exhausted" @selected(request('status') === 'exhausted')>Hết số lượt dùng</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Tạm tắt</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1" style="font-size:0.875rem;font-weight:600;height:38px;">
                        <i class="bi bi-funnel me-1"></i> Lọc
                    </button>
                    @if(request()->hasAny(['keyword', 'discount_type', 'status']))
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary" title="Đặt lại bộ lọc" style="font-size:0.875rem;height:38px;">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- Promotions Table --}}
    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="min-width:180px;">Mã / Tên chương trình</th>
                        <th>Mức giảm giá</th>
                        <th>Đơn tối thiểu</th>
                        <th>Số lượt sử dụng</th>
                        <th>Hạn sử dụng (HSD)</th>
                        <th>Trạng thái</th>
                        <th class="text-end" style="width:110px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $promo)
                        @php
                            $statusInfo = $promo->status_info;
                            $pctUsed = $promo->usage_limit ? min(100, round(($promo->used_count / $promo->usage_limit) * 100)) : 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="coupon-code-badge">
                                        <i class="bi bi-tag-fill" style="font-size:0.75rem;"></i>
                                        {{ $promo->code }}
                                    </span>
                                    <button type="button" class="btn-copy-code" title="Sao chép mã" onclick="copyCouponCode('{{ $promo->code }}')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                                <div class="fw-bold" style="font-size:0.88rem; color:#3f2f24;">{{ $promo->name }}</div>
                                @if($promo->description)
                                    <div class="text-muted" style="font-size:0.75rem; max-width:240px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        {{ $promo->description }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if($promo->discount_type === 'percent')
                                    <span class="discount-pill discount-pill-percent">
                                        <i class="bi bi-percent"></i> Giảm {{ (int) $promo->discount_value }}%
                                    </span>
                                    @if($promo->max_discount_amount > 0)
                                        <div class="text-muted mt-1" style="font-size:0.75rem;">
                                            Tối đa: <strong>{{ number_format($promo->max_discount_amount, 0, ',', '.') }}đ</strong>
                                        </div>
                                    @endif
                                @else
                                    <span class="discount-pill discount-pill-fixed">
                                        <i class="bi bi-cash-stack"></i> Giảm {{ number_format($promo->discount_value, 0, ',', '.') }}đ
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($promo->min_order_amount > 0)
                                    <span style="font-weight:600; color:#5a4536;">{{ number_format($promo->min_order_amount, 0, ',', '.') }}đ</span>
                                @else
                                    <span class="text-muted" style="font-size:0.8rem;">Không yêu cầu</span>
                                @endif
                            </td>

                            <td>
                                @if(is_null($promo->usage_limit))
                                    <div><strong>{{ number_format($promo->used_count) }}</strong> lượt</div>
                                    <span class="badge bg-light text-muted border" style="font-size:0.7rem;">Không giới hạn</span>
                                @else
                                    <div>
                                        <strong>{{ number_format($promo->used_count) }}</strong>
                                        <span class="text-muted">/ {{ number_format($promo->usage_limit) }}</span>
                                    </div>
                                    <div class="usage-progress" title="{{ $pctUsed }}% đã dùng">
                                        <div class="usage-progress-bar {{ $pctUsed >= 100 ? 'bg-danger' : ($pctUsed >= 80 ? 'bg-warning' : 'bg-primary') }}"
                                             style="width: {{ $pctUsed }}%;"></div>
                                    </div>
                                    <small class="text-muted" style="font-size:0.7rem;">Còn lại: {{ number_format($promo->remaining_uses) }}</small>
                                @endif
                            </td>

                            <td>
                                @if($promo->start_date || $promo->end_date)
                                    <div style="font-size:0.8rem;">
                                        @if($promo->start_date)
                                            <div class="text-muted" style="font-size:0.75rem;">
                                                Từ: <span>{{ $promo->start_date->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @endif
                                        @if($promo->end_date)
                                            <div class="{{ $promo->hasExpired() ? 'text-danger fw-bold' : '' }}" style="font-size:0.78rem;">
                                                Đến: <span>{{ $promo->end_date->format('d/m/Y H:i') }}</span>
                                            </div>
                                            @if(!$promo->hasExpired() && $promo->end_date->diffInDays(now()) <= 3)
                                                <span class="badge bg-warning text-dark mt-1" style="font-size:0.68rem;">Sắp hết hạn</span>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border" style="font-size:0.72rem;">Vô thời hạn</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $statusInfo['badge'] }}" style="font-size:0.72rem; padding:0.3rem 0.55rem;">
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </div>
                                <form action="{{ route('admin.promotions.toggle', $promo) }}" method="POST" class="mt-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none" style="font-size:0.72rem; color:{{ $promo->is_active ? '#dc2626' : '#16a34a' }};">
                                        {{ $promo->is_active ? 'Tắt mã' : 'Bật lại' }}
                                    </button>
                                </form>
                            </td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.promotions.edit', $promo) }}" class="action-btn action-btn-edit" title="Chỉnh sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.promotions.destroy', $promo) }}" method="POST"
                                          onsubmit="return confirm('Bạn có chắc muốn xóa mã khuyến mãi {{ $promo->code }}?');"
                                          style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn action-btn-delete" title="Xóa">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted mb-2">
                                    <i class="bi bi-ticket-perforated" style="font-size:2.5rem; color:#d9c7b3;"></i>
                                </div>
                                <div class="fw-bold" style="color:#6b5848;">Chưa có mã khuyến mãi nào</div>
                                <p class="text-muted small mb-3">Tạo mã giảm giá để thu hút khách hàng và gia tăng đơn hàng.</p>
                                <a href="{{ route('admin.promotions.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i> Tạo mã đầu tiên
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($promotions->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $promotions->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function copyCouponCode(code) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(code).then(() => {
            alert('Đã sao chép mã: ' + code);
        });
    } else {
        prompt('Sao chép mã khuyến mãi:', code);
    }
}
</script>
@endpush
@endsection
