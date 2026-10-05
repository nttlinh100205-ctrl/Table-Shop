@extends('layouts.admin')

@section('title', 'Tạo mã khuyến mãi mới')

@section('content')
<style>
    .form-section-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: #3a2e26;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f3e9dc;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-label-custom {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6b5848;
        margin-bottom: 0.35rem;
    }
    .req-star { color: #dc2626; }

    /* Voucher Live Preview Ticket */
    .voucher-preview-ticket {
        background: linear-gradient(135deg, #1e3a8a, #765338);
        border-radius: 16px;
        color: #fff;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(118,83,56,0.4);
    }
    .voucher-preview-ticket::before,
    .voucher-preview-ticket::after {
        content: '';
        position: absolute;
        width: 24px;
        height: 24px;
        background: #faf6f0;
        border-radius: 50%;
        top: calc(50% - 12px);
    }
    .voucher-preview-ticket::before { left: -12px; }
    .voucher-preview-ticket::after  { right: -12px; }
    .ticket-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        background: rgba(255,255,255,0.18);
        border: 1px dashed rgba(255,255,255,0.4);
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        display: inline-block;
        margin-bottom: 0.6rem;
    }
    .ticket-value { font-size: 1.6rem; font-weight: 900; line-height: 1.2; }
    .ticket-meta { font-size: 0.78rem; opacity: 0.85; margin-top: 0.5rem; }
</style>

<div class="container-fluid px-0" style="max-width: 1050px;">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.promotions.index') }}" class="text-muted text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Khuyến mãi
                </a>
                <span class="text-muted">/</span>
                <span class="fw-bold" style="color:#3f2f24;">Tạo mã mới</span>
            </div>
            <h2 class="h5 fw-bold mb-0" style="color:#3f2f24;">Thêm mã khuyến mãi & voucher</h2>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Có lỗi xảy ra, vui lòng kiểm tra lại:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.promotions.store') }}" id="promoForm">
        @csrf
        <div class="row g-4">
            {{-- Left column: Main Form fields --}}
            <div class="col-lg-7">
                <div class="admin-card mb-4">
                    <div class="admin-card-body p-4">
                        <div class="form-section-title">
                            <i class="bi bi-info-circle text-primary"></i> 1. Thông tin cơ bản
                        </div>

                        {{-- Code --}}
                        <div class="mb-3">
                            <label class="form-label-custom">Mã khuyến mãi (Coupon Code) <span class="req-star">*</span></label>
                            <div class="input-group">
                                <input type="text" name="code" id="input_code"
                                       class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code') }}"
                                       placeholder="VD: SALE20, GIAM50K, TET2026..."
                                       style="text-transform: uppercase; font-weight:700; letter-spacing:0.04em;"
                                       maxlength="50" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateRandomCode()">
                                    <i class="bi bi-dice-5 me-1"></i> Ngẫu nhiên
                                </button>
                            </div>
                            <small class="text-muted">Chữ in hoa, số và dấu gạch nối (-), không dấu, không cách.</small>
                            @error('code') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Name --}}
                        <div class="mb-3">
                            <label class="form-label-custom">Tên chương trình khuyến mãi <span class="req-star">*</span></label>
                            <input type="text" name="name" id="input_name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="VD: Giảm 20% mừng khai trương, Giảm 50K cho đơn từ 500K..."
                                   maxlength="255" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label-custom">Mô tả / Thể lệ chương trình</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3" placeholder="Ghi chú điều kiện áp dụng cho khách hàng...">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-section-title mt-4">
                            <i class="bi bi-tag text-primary"></i> 2. Mức giảm giá & Giá trị
                        </div>

                        {{-- Discount Type --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">Hình thức giảm giá <span class="req-star">*</span></label>
                                <select name="discount_type" id="select_type" class="form-select @error('discount_type') is-invalid @enderror" required onchange="handleTypeChange()">
                                    <option value="percent" @selected(old('discount_type') === 'percent')>Giảm theo phần trăm (%)</option>
                                    <option value="fixed" @selected(old('discount_type', 'fixed') === 'fixed')>Giảm số tiền cố định (VNĐ)</option>
                                </select>
                                @error('discount_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom" id="val_label">Giá trị giảm (VNĐ) <span class="req-star">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="any" name="discount_value" id="input_value"
                                           class="form-control @error('discount_value') is-invalid @enderror"
                                           value="{{ old('discount_value') }}" placeholder="VD: 50000 hoặc 20" required>
                                    <span class="input-group-text" id="val_unit">VNĐ</span>
                                </div>
                                @error('discount_value') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Max discount amount (for percent) & Min order amount --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6" id="max_discount_wrapper">
                                <label class="form-label-custom">Số tiền giảm tối đa (VNĐ)</label>
                                <div class="input-group">
                                    <input type="number" step="any" name="max_discount_amount" id="input_max_discount"
                                           class="form-control @error('max_discount_amount') is-invalid @enderror"
                                           value="{{ old('max_discount_amount') }}" placeholder="VD: 100000 (để trống nếu không giới hạn)">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                                <small class="text-muted">Áp dụng cho giảm theo % (tránh lỗ đơn lớn).</small>
                                @error('max_discount_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6" id="min_order_wrapper">
                                <label class="form-label-custom">Giá trị đơn tối thiểu (VNĐ)</label>
                                <div class="input-group">
                                    <input type="number" step="any" name="min_order_amount" id="input_min_order"
                                           class="form-control @error('min_order_amount') is-invalid @enderror"
                                           value="{{ old('min_order_amount', 0) }}" placeholder="0 = Không yêu cầu">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                                <small class="text-muted">Đơn hàng phải từ mức này trở lên mới được dùng mã.</small>
                                @error('min_order_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-section-title mt-4">
                            <i class="bi bi-calendar-event text-primary"></i> 3. Giới hạn lượt dùng & Hạn sử dụng (HSD)
                        </div>

                        {{-- Usage limit --}}
                        <div class="mb-3">
                            <label class="form-label-custom">Số lượt sử dụng tối đa</label>
                            <input type="number" name="usage_limit" id="input_usage_limit"
                                   class="form-control @error('usage_limit') is-invalid @enderror"
                                   value="{{ old('usage_limit') }}" placeholder="VD: 100 (để trống nếu không giới hạn)">
                            <small class="text-muted">Tổng số lần mã có thể được áp dụng thành công trên toàn hệ thống.</small>
                            @error('usage_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Dates --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">Ngày bắt đầu hiệu lực</label>
                                <input type="datetime-local" name="start_date" id="input_start_date"
                                       class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date') }}">
                                <small class="text-muted">Để trống nếu có hiệu lực ngay lập tức.</small>
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Hạn sử dụng (Ngày kết thúc) <span class="req-star">*</span></label>
                                <input type="datetime-local" name="end_date" id="input_end_date"
                                       class="form-control @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date') }}">
                                <small class="text-muted">Sau thời gian này, mã sẽ tự động hết hạn.</small>
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Is Active --}}
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                   @checked(old('is_active', true))>
                            <label class="form-check-label fw-semibold" for="is_active" style="font-size:0.875rem; color:#3a2e26;">
                                Kích hoạt mã ngay sau khi lưu
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="d-flex align-items-center justify-content-end gap-2 mb-4">
                    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary px-3">Hủy</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-check-lg me-1"></i> Lưu mã khuyến mãi
                    </button>
                </div>
            </div>

            {{-- Right column: Live Interactive Preview --}}
            <div class="col-lg-5">
                <div class="sticky-top" style="top: 20px;">
                    <div class="admin-card mb-3">
                        <div class="admin-card-header">
                            <h6 class="fw-bold mb-0"><i class="bi bi-eye text-primary me-2"></i>Xem trước Voucher</h6>
                        </div>
                        <div class="admin-card-body p-4 text-center">
                            <div class="voucher-preview-ticket text-start">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="ticket-code" id="pv_code">VOUCHER_CODE</span>
                                    <span class="badge bg-white text-primary fw-bold" id="pv_type">Giảm tiền</span>
                                </div>
                                <div class="ticket-value" id="pv_value">Giảm 0đ</div>
                                <div class="fw-semibold mt-1" id="pv_name" style="font-size:0.92rem;">Tên chương trình khuyến mãi</div>
                                <hr style="opacity:0.2; margin:0.8rem 0;">
                                <div class="ticket-meta">
                                    <div id="pv_min_order"><i class="bi bi-cart-check me-1"></i>Đơn tối thiểu: 0đ</div>
                                    <div id="pv_usage"><i class="bi bi-people me-1"></i>Số lượt dùng: Không giới hạn</div>
                                    <div id="pv_date"><i class="bi bi-clock me-1"></i>HSD: Vô thời hạn</div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-3">Thẻ voucher mẫu sẽ cập nhật ngay khi bạn nhập thông tin ở bên trái.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function handleTypeChange() {
    const type = document.getElementById('select_type').value;
    const valLabel = document.getElementById('val_label');
    const valUnit = document.getElementById('val_unit');
    const maxDiscountWrapper = document.getElementById('max_discount_wrapper');

    if (type === 'percent') {
        valLabel.innerHTML = 'Tỷ lệ giảm (%) <span class="req-star">*</span>';
        valUnit.textContent = '%';
        maxDiscountWrapper.style.display = 'block';
    } else {
        valLabel.innerHTML = 'Số tiền giảm (VNĐ) <span class="req-star">*</span>';
        valUnit.textContent = 'VNĐ';
        maxDiscountWrapper.style.display = 'none';
    }
    updatePreview();
}

function generateRandomCode() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let code = 'SALE';
    for (let i = 0; i < 4; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('input_code').value = code;
    updatePreview();
}

function updatePreview() {
    const code = document.getElementById('input_code').value.trim() || 'VOUCHER_CODE';
    const name = document.getElementById('input_name').value.trim() || 'Tên chương trình khuyến mãi';
    const type = document.getElementById('select_type').value;
    const value = parseFloat(document.getElementById('input_value').value) || 0;
    const maxDiscount = parseFloat(document.getElementById('input_max_discount')?.value) || 0;
    const minOrder = parseFloat(document.getElementById('input_min_order')?.value) || 0;
    const usage = document.getElementById('input_usage_limit')?.value;
    const endDate = document.getElementById('input_end_date')?.value;

    document.getElementById('pv_code').textContent = code.toUpperCase();
    document.getElementById('pv_name').textContent = name;

    if (type === 'percent') {
        document.getElementById('pv_type').textContent = 'Giảm %';
        let valStr = 'Giảm ' + value + '%';
        if (maxDiscount > 0) {
            valStr += ' (Tối đa ' + maxDiscount.toLocaleString('vi-VN') + 'đ)';
        }
        document.getElementById('pv_value').textContent = valStr;
    } else {
        document.getElementById('pv_type').textContent = 'Giảm tiền';
        document.getElementById('pv_value').textContent = 'Giảm ' + value.toLocaleString('vi-VN') + 'đ';
    }

    document.getElementById('pv_min_order').innerHTML = '<i class="bi bi-cart-check me-1"></i>Đơn tối thiểu: ' +
        (minOrder > 0 ? minOrder.toLocaleString('vi-VN') + 'đ' : '0đ (Mọi đơn hàng)');

    document.getElementById('pv_usage').innerHTML = '<i class="bi bi-people me-1"></i>Số lượt dùng: ' +
        (usage ? parseInt(usage).toLocaleString('vi-VN') + ' lượt' : 'Không giới hạn');

    document.getElementById('pv_date').innerHTML = '<i class="bi bi-clock me-1"></i>HSD: ' +
        (endDate ? new Date(endDate).toLocaleString('vi-VN') : 'Vô thời hạn');
}

document.addEventListener('DOMContentLoaded', function () {
    handleTypeChange();
    document.querySelectorAll('#promoForm input, #promoForm select').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });
    updatePreview();
});
</script>
@endpush
@endsection
