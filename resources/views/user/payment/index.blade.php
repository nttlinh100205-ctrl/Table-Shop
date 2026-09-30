@extends('layouts.app')

@section('title', 'Thanh toán đơn hàng')

@section('content')
<style>
    .checkout-page {
        padding: 2rem 0 4rem;
        background: #f8fafc;
        min-height: 80vh;
    }

    /* ── Progress Stepper ── */
    .checkout-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
        gap: 0.75rem;
    }
    .step-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #94a3b8;
        text-decoration: none;
        transition: color 0.15s;
    }
    .step-item.active {
        color: #0f172a;
        font-weight: 700;
    }
    .step-item.completed {
        color: #10b981;
    }
    .step-item.completed:hover {
        color: #059669;
    }
    .step-badge {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 700;
        background: #e2e8f0;
        color: #64748b;
        transition: all 0.15s;
    }
    .step-item.active .step-badge {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(37,99,235,0.15);
    }
    .step-item.completed .step-badge {
        background: #10b981;
        color: #fff;
    }
    .step-line {
        width: 48px;
        height: 2px;
        background: #e2e8f0;
    }
    .step-line.completed {
        background: #10b981;
    }

    /* ── Cards ── */
    .checkout-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03), 0 6px 18px rgba(0,0,0,0.04);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .checkout-card-header {
        padding: 1.1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .card-header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .card-header-icon.payment-icon {
        background: #fdf2f8;
        color: #db2777;
    }
    .card-header-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .checkout-card-body {
        padding: 1.5rem;
    }

    /* ── Form Controls ── */
    .form-label-custom {
        font-size: 0.83rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.35rem;
        display: block;
    }
    .form-label-custom .req-star {
        color: #ef4444;
    }
    .input-with-icon {
        position: relative;
    }
    .input-icon {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.95rem;
        pointer-events: none;
    }
    .form-control-custom, .form-select-custom {
        height: 44px;
        border-radius: 10px;
        border: 1.5px solid #cbd5e1;
        font-size: 0.9rem;
        color: #0f172a;
        background-color: #fff;
        padding-left: 2.35rem;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-select-custom {
        padding-left: 0.85rem;
    }
    .form-control-custom:focus, .form-select-custom:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        outline: none;
    }
    .form-control-custom:disabled, .form-select-custom:disabled {
        background-color: #f8fafc;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .field-hint {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.25rem;
    }

    /* ── GHN Status Banner ── */
    .ghn-status-box {
        border-radius: 12px;
        padding: 0.85rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .ghn-status-neutral {
        background: #f1f5f9;
        border: 1px dashed #cbd5e1;
        color: #475569;
    }
    .ghn-status-loading {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
    }
    .ghn-status-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    .ghn-status-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .ghn-status-icon {
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .ghn-status-title {
        font-weight: 700;
        line-height: 1.3;
    }
    .ghn-status-desc {
        font-size: 0.78rem;
        opacity: 0.85;
    }

    /* ── Payment Options ── */
    .payment-options-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    @media (max-width: 640px) {
        .payment-options-grid {
            grid-template-columns: 1fr;
        }
    }
    .payment-option-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        background: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }
    .payment-option-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    .payment-option-card.active {
        border-color: #2563eb;
        background: #eff6ff;
        box-shadow: 0 4px 12px rgba(37,99,235,0.08);
    }
    .payment-option-card.active.card-momo {
        border-color: #a50064;
        background: #fdf2f8;
        box-shadow: 0 4px 12px rgba(165,0,100,0.08);
    }
    .pm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.65rem;
    }
    .pm-icon-badge {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .pm-icon-badge.icon-cod {
        background: #ecfdf5;
        color: #059669;
    }
    .pm-icon-badge.icon-momo {
        background: #fdf2f8;
        color: #a50064;
    }
    .pm-tag {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
    }
    .pm-tag-cod {
        background: #dcfce7;
        color: #15803d;
    }
    .pm-tag-momo {
        background: #fce7f3;
        color: #9d174d;
    }
    .pm-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }
    .pm-desc {
        font-size: 0.77rem;
        color: #64748b;
        line-height: 1.4;
        margin: 0;
    }
    .pm-radio-dot {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: auto;
    }
    .payment-option-card.active .pm-radio-dot {
        border-color: #2563eb;
    }
    .payment-option-card.active.card-momo .pm-radio-dot {
        border-color: #a50064;
    }
    .payment-option-card.active .pm-radio-dot::after {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #2563eb;
    }
    .payment-option-card.active.card-momo .pm-radio-dot::after {
        background: #a50064;
    }

    /* ── Action Buttons ── */
    .btn-payment-action {
        width: 100%;
        border-radius: 12px;
        padding: 0.85rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 700;
        font-size: 0.95rem;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    }
    .btn-payment-action:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        box-shadow: none;
        transform: none !important;
    }
    .btn-action-cod {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #fff;
    }
    .btn-action-cod:hover:not(:disabled) {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(5,150,105,0.25);
        color: #fff;
    }
    .btn-action-momo {
        background: linear-gradient(135deg, #d82d8b 0%, #a50064 100%);
        color: #fff;
    }
    .btn-action-momo:hover:not(:disabled) {
        background: linear-gradient(135deg, #a50064 0%, #83004f 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(165,0,100,0.25);
        color: #fff;
    }
    .btn-action-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .btn-action-title {
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .btn-action-sub {
        font-size: 0.75rem;
        font-weight: 500;
        opacity: 0.9;
        margin-top: 0.1rem;
    }

    /* ── Inspection Notice ── */
    .inspection-notice {
        background: #f8fafc;
        border-radius: 12px;
        padding: 0.85rem 1.1rem;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.82rem;
        color: #475569;
        margin-bottom: 1.5rem;
    }
    .inspection-notice i {
        color: #059669;
        font-size: 1.15rem;
        flex-shrink: 0;
        margin-top: 0.1rem;
    }

    /* ── Order Summary Sidebar ── */
    .summary-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03), 0 6px 18px rgba(0,0,0,0.04);
        position: sticky;
        top: 1.5rem;
        overflow: hidden;
    }
    .summary-header {
        padding: 1.1rem 1.4rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .summary-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .summary-items-list {
        padding: 1.25rem 1.4rem;
        max-height: 380px;
        overflow-y: auto;
        border-bottom: 1px solid #f1f5f9;
    }
    .summary-items-list::-webkit-scrollbar {
        width: 5px;
    }
    .summary-items-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .summary-item-row {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f8fafc;
    }
    .summary-item-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .summary-item-row:first-child {
        padding-top: 0;
    }
    .summary-item-media {
        position: relative;
        width: 54px;
        height: 54px;
        flex-shrink: 0;
    }
    .summary-item-img {
        width: 100%;
        height: 100%;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
    }
    .summary-item-img-placeholder {
        width: 100%;
        height: 100%;
        border-radius: 10px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 1.1rem;
    }
    .summary-item-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #2563eb;
        color: #fff;
        font-size: 0.68rem;
        font-weight: 700;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .summary-item-info {
        flex: 1;
        min-width: 0;
    }
    .summary-item-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 0.15rem;
    }
    .summary-item-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        align-items: center;
    }
    .summary-meta-chip {
        font-size: 0.7rem;
        font-weight: 600;
        color: #64748b;
        background: #f1f5f9;
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
    }
    .summary-item-price {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
        text-align: right;
        flex-shrink: 0;
    }

    /* ── Price Calculations ── */
    .summary-calc-box {
        padding: 1.25rem 1.4rem;
        background: #fff;
    }
    .calc-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.65rem;
        font-size: 0.88rem;
        color: #475569;
    }
    .calc-row strong {
        color: #0f172a;
    }
    .calc-row.highlight-shipping {
        color: #0284c7;
    }
    .total-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-top: 1rem;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .total-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.15rem;
    }
    .total-desc {
        font-size: 0.73rem;
        color: #64748b;
    }
    .total-amount {
        font-size: 1.4rem;
        font-weight: 800;
        color: #dc2626;
        letter-spacing: -0.02em;
    }

    /* ── Security Trust ── */
    .trust-strip {
        padding: 1rem 1.4rem;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        font-size: 0.78rem;
        color: #64748b;
    }
    .trust-strip-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .trust-strip-item i {
        color: #2563eb;
        font-size: 0.9rem;
    }

    /* ── Return Link ── */
    .btn-return-cart {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: color 0.15s;
    }
    .btn-return-cart:hover {
        color: #2563eb;
    }
</style>

<div class="checkout-page">
    <div class="container">
        {{-- Stepper header --}}
        <div class="checkout-steps">
            <a href="{{ route('user.cart.index') }}" class="step-item completed">
                <span class="step-badge"><i class="bi bi-check-lg"></i></span>
                <span>1. Giỏ hàng</span>
            </a>
            <div class="step-line completed"></div>
            <div class="step-item active">
                <span class="step-badge">2</span>
                <span>2. Thanh toán & Đặt hàng</span>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <span class="step-badge">3</span>
                <span>3. Hoàn tất</span>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                    <div>
                        <strong class="d-block mb-1">Vui lòng kiểm tra lại thông tin:</strong>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            {{-- Left column: Delivery info & Payment --}}
            <div class="col-lg-7">
                <form method="POST" action="{{ route('user.orders.store') }}" id="checkoutForm">
                    @csrf

                    {{-- Card 1: Delivery information --}}
                    <div class="checkout-card">
                        <div class="checkout-card-header">
                            <div class="card-header-icon"><i class="bi bi-geo-alt-fill"></i></div>
                            <h2 class="card-header-title">Thông tin nhận hàng</h2>
                        </div>
                        <div class="checkout-card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label-custom">Họ tên người nhận <span class="req-star">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="bi bi-person input-icon"></i>
                                        <input type="text" name="name" class="form-control form-control-custom"
                                               value="{{ old('name', auth()->user()->name) }}"
                                               placeholder="Ví dụ: Nguyễn Văn A" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Số điện thoại nhận hàng <span class="req-star">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="bi bi-telephone input-icon"></i>
                                        <input type="tel" name="phone" class="form-control form-control-custom"
                                               value="{{ old('phone') }}"
                                               pattern="0[35789][0-9]{8}"
                                               maxlength="10"
                                               minlength="10"
                                               placeholder="0xxxxxxxxx (10 số)"
                                               title="Số điện thoại Việt Nam 10 số, bắt đầu bằng 03/05/07/08/09"
                                               required>
                                    </div>
                                    <div class="field-hint">Shipper sẽ gọi số này trước khi giao hàng</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom">Địa chỉ chi tiết (Số nhà, tên đường, tòa nhà...) <span class="req-star">*</span></label>
                                <div class="input-with-icon">
                                    <i class="bi bi-house-door input-icon"></i>
                                    <input type="text" name="address" class="form-control form-control-custom"
                                           value="{{ old('address') }}"
                                           placeholder="Ví dụ: 123 Đường Nguyễn Trãi, Tòa nhà Ruby" required>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label-custom">Tỉnh / Thành phố <span class="req-star">*</span></label>
                                    <select id="province_select" class="form-select form-select-custom" required>
                                        <option value="">-- Đang tải --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-custom">Quận / Huyện <span class="req-star">*</span></label>
                                    <select id="district_select" name="to_district_id" class="form-select form-select-custom" required disabled>
                                        <option value="">-- Chọn quận/huyện --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-custom">Phường / Xã <span class="req-star">*</span></label>
                                    <select id="ward_select" name="to_ward_code" class="form-select form-select-custom" required disabled>
                                        <option value="">-- Chọn phường/xã --</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Live GHN Shipping status banner --}}
                            <div id="ghn_status_box" class="ghn-status-box ghn-status-neutral">
                                <div class="ghn-status-icon"><i class="bi bi-truck"></i></div>
                                <div>
                                    <div class="ghn-status-title">Tính cước giao hàng GHN tự động</div>
                                    <div class="ghn-status-desc">Vui lòng chọn Tỉnh/Thành, Quận/Huyện và Phường/Xã để tính phí giao hàng.</div>
                                </div>
                            </div>

                            <input type="hidden" name="shipping_fee" id="shipping_fee_input" value="0">
                        </div>
                    </div>

                    {{-- Card 2: Payment method selection --}}
                    <div class="checkout-card">
                        <div class="checkout-card-header">
                            <div class="card-header-icon payment-icon"><i class="bi bi-credit-card-2-front-fill"></i></div>
                            <h2 class="card-header-title">Chọn hình thức thanh toán</h2>
                        </div>
                        <div class="checkout-card-body">
                            {{-- Interactive Payment Method Selection Cards --}}
                            <div class="payment-options-grid">
                                {{-- Option COD --}}
                                <div class="payment-option-card active card-cod" id="opt_cod" onclick="selectPaymentMethod('cod')">
                                    <div class="pm-header">
                                        <div class="pm-icon-badge icon-cod">
                                            <i class="bi bi-cash-coin"></i>
                                        </div>
                                        <span class="pm-tag pm-tag-cod">Phổ biến</span>
                                        <div class="pm-radio-dot"></div>
                                    </div>
                                    <div>
                                        <div class="pm-title">Thanh toán khi nhận hàng (COD)</div>
                                        <p class="pm-desc">Kiểm tra hàng trước, gửi tiền mặt cho shipper khi nhận hàng tận nơi.</p>
                                    </div>
                                </div>

                                {{-- Option MoMo --}}
                                <div class="payment-option-card card-momo" id="opt_momo" onclick="selectPaymentMethod('momo')">
                                    <div class="pm-header">
                                        <div class="pm-icon-badge icon-momo">
                                            <i class="bi bi-wallet2"></i>
                                        </div>
                                        <span class="pm-tag pm-tag-momo">Nhanh chóng</span>
                                        <div class="pm-radio-dot"></div>
                                    </div>
                                    <div>
                                        <div class="pm-title">Ví MoMo / QR Code</div>
                                        <p class="pm-desc">Quét mã QR MoMo, chuyển khoản VietQR hoặc thanh toán bằng thẻ ATM/Visa.</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Inspection and return assurance note --}}
                            <div class="inspection-notice">
                                <i class="bi bi-shield-check"></i>
                                <div>
                                    <strong>Chính sách đồng kiểm & Bảo vệ người mua:</strong>
                                    Bạn được quyền mở gói hàng kiểm tra ngoại quan (mẫu mã, kích thước, màu sắc, số lượng) trước khi thanh toán. Đổi trả dễ dàng trong vòng 7 ngày nếu lỗi sản xuất.
                                </div>
                            </div>

                            {{-- Action buttons --}}
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <button type="submit" name="payment_method" value="cod" id="btn_cod_submit"
                                        class="btn-payment-action btn-action-cod" disabled>
                                    <div class="btn-action-content">
                                        <i class="bi bi-cash-coin fs-4"></i>
                                        <div class="text-start">
                                            <div class="btn-action-title">Đặt hàng COD</div>
                                            <div class="btn-action-sub">Thanh toán khi nhận hàng</div>
                                        </div>
                                    </div>
                                    <i class="bi bi-arrow-right"></i>
                                </button>

                                <button type="submit" name="payment_method" value="momo" id="btn_momo_submit"
                                        class="btn-payment-action btn-action-momo" disabled>
                                    <div class="btn-action-content">
                                        <i class="bi bi-wallet2 fs-4"></i>
                                        <div class="text-start">
                                            <div class="btn-action-title">Thanh toán MoMo</div>
                                            <div class="btn-action-sub">Cổng thanh toán MoMo</div>
                                        </div>
                                    </div>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>

                            <div class="mt-3 text-center">
                                <a href="{{ route('user.cart.index') }}" class="btn-return-cart">
                                    <i class="bi bi-arrow-left"></i> Quay lại giỏ hàng
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Right column: Order summary sidebar --}}
            <div class="col-lg-5">
                <div class="summary-card">
                    <div class="summary-header">
                        <h3 class="summary-title">
                            <i class="bi bi-bag-check text-primary"></i>
                            Đơn hàng của bạn
                        </h3>
                        <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded-pill">
                            {{ count($cart) }} sản phẩm
                        </span>
                    </div>

                    {{-- Products list --}}
                    <div class="summary-items-list">
                        @foreach ($cart as $cartKey => $item)
                            @php
                                $sizeLabel = $item['size_label'] ?? null;
                                $color = $item['color'] ?? null;
                                if (!$sizeLabel && !$color && str_contains($item['name'] ?? '', ' — ')) {
                                    $tail = trim(explode(' — ', $item['name'], 2)[1] ?? '');
                                    $parts = array_map('trim', explode('/', $tail));
                                    $sizeLabel = $parts[0] ?? null;
                                    $color = $parts[1] ?? null;
                                }
                                $productName = str_contains($item['name'] ?? '', ' — ')
                                    ? trim(explode(' — ', $item['name'], 2)[0])
                                    : ($item['name'] ?? '');
                                $img = !empty($item['image'])
                                    ? asset('storage/' . $item['image'])
                                    : null;
                            @endphp
                            <div class="summary-item-row">
                                <div class="summary-item-media">
                                    @if($img)
                                        <img src="{{ $img }}" alt="{{ $productName }}" class="summary-item-img">
                                    @else
                                        <div class="summary-item-img-placeholder">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                    @endif
                                    <span class="summary-item-badge">{{ $item['quantity'] }}</span>
                                </div>
                                <div class="summary-item-info">
                                    <div class="summary-item-name" title="{{ $productName }}">{{ $productName }}</div>
                                    <div class="summary-item-meta">
                                        @if($sizeLabel)
                                            <span class="summary-meta-chip"><i class="bi bi-rulers"></i> {{ $sizeLabel }}</span>
                                        @endif
                                        @if($color)
                                            <span class="summary-meta-chip"><i class="bi bi-palette"></i> {{ $color }}</span>
                                        @endif
                                        <span class="text-muted" style="font-size:0.75rem;">x{{ $item['quantity'] }}</span>
                                    </div>
                                </div>
                                <div class="summary-item-price">
                                    {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Calculation Breakdown --}}
                    <div class="summary-calc-box">
                        <div class="calc-row">
                            <span>Tạm tính hàng hóa</span>
                            <strong>{{ number_format($totalPrice, 0, ',', '.') }}đ</strong>
                        </div>

                        {{-- Dòng giảm giá khuyến mãi (nếu có) --}}
                        <div class="calc-row" id="discount_row" style="{{ ($discountAmount ?? 0) > 0 ? '' : 'display:none;' }}">
                            <span class="text-success d-flex align-items-center gap-1">
                                <i class="bi bi-tag-fill"></i> Giảm giá khuyến mãi
                                <span class="badge bg-success-subtle text-success border border-success-subtle" id="applied_code_badge">{{ $coupon['code'] ?? '' }}</span>
                            </span>
                            <strong class="text-success" id="discount_amount_text">-{{ number_format($discountAmount ?? 0, 0, ',', '.') }}đ</strong>
                        </div>

                        {{-- Ô nhập mã giảm giá Voucher --}}
                        <div class="coupon-section my-3 pt-2 pb-1 border-top border-bottom">
                            <div id="coupon_input_group" style="{{ ($discountAmount ?? 0) > 0 ? 'display:none;' : '' }}">
                                <label class="form-label mb-1 fw-bold small text-muted">Mã khuyến mãi / Voucher</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="coupon_code_input" class="form-control"
                                           placeholder="Nhập mã voucher (VD: SALE20)..."
                                           style="text-transform:uppercase; font-weight:700; font-size:0.82rem; letter-spacing:0.04em;">
                                    <button type="button" id="btn_apply_coupon" class="btn btn-primary" style="font-weight:600; font-size:0.8rem; padding:0 0.85rem;">
                                        Áp dụng
                                    </button>
                                </div>
                                <div id="coupon_msg" class="small mt-1" style="display:none;"></div>
                            </div>
                            <div id="coupon_applied_pill" class="d-flex align-items-center justify-content-between p-2 rounded-2"
                                 style="background:#f0fdf4; border:1px dashed #86efac; font-size:0.82rem; {{ ($discountAmount ?? 0) > 0 ? '' : 'display:none!important;' }}">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-ticket-perforated text-success fs-5"></i>
                                    <div>
                                        <span class="fw-bold text-success" id="pill_code">{{ $coupon['code'] ?? '' }}</span>
                                        <small class="text-muted d-block" id="pill_desc">Tiết kiệm {{ number_format($discountAmount ?? 0, 0, ',', '.') }}đ</small>
                                    </div>
                                </div>
                                <button type="button" id="btn_remove_coupon" class="btn btn-sm btn-link text-danger p-0 text-decoration-none fw-bold" title="Hủy mã">
                                    Gỡ bỏ
                                </button>
                            </div>
                        </div>

                        <div class="calc-row highlight-shipping">
                            <span>Phí vận chuyển (GHN)</span>
                            <strong id="shipping_fee_text">Chọn địa chỉ</strong>
                        </div>

                        <div class="total-box">
                            <div>
                                <div class="total-title">Tổng cộng</div>
                                <div class="total-desc">Đã bao gồm VAT & phí ship</div>
                            </div>
                            <div class="total-amount" id="final_total_text">
                                {{ number_format(max(0, $totalPrice - ($discountAmount ?? 0)), 0, ',', '.') }}đ
                            </div>
                        </div>
                        <input type="hidden" id="total_price_input" value="{{ (int) $totalPrice }}">
                        <input type="hidden" id="discount_amount_input" value="{{ (int) ($discountAmount ?? 0) }}">
                    </div>

                    {{-- Guarantees strip --}}
                    <div class="trust-strip">
                        <div class="trust-strip-item">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span>Cam kết 100% hàng chính hãng, đóng gói cẩn thận</span>
                        </div>
                        <div class="trust-strip-item">
                            <i class="bi bi-truck text-primary"></i>
                            <span>Giao hàng nhanh toàn quốc qua đối tác GHN</span>
                        </div>
                        <div class="trust-strip-item">
                            <i class="bi bi-shield-lock-fill text-info"></i>
                            <span>Bảo mật giao dịch tuyệt đối, an toàn 100%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const provinceSelect = document.getElementById('province_select');
    const districtSelect = document.getElementById('district_select');
    const wardSelect = document.getElementById('ward_select');
    const shippingFeeText = document.getElementById('shipping_fee_text');
    const finalTotalText = document.getElementById('final_total_text');
    const shippingFeeInput = document.getElementById('shipping_fee_input');
    const totalPriceInput = document.getElementById('total_price_input');
    const ghnStatusBox = document.getElementById('ghn_status_box');
    const checkoutButtons = [...document.querySelectorAll('#checkoutForm button[type="submit"]')];
    const subtotal = parseInt(totalPriceInput ? totalPriceInput.value : 0) || 0;

    const districtsUrl = "{{ url('/user/locations/districts/__PROVINCE__') }}";
    const wardsUrl = "{{ url('/user/locations/wards/__DISTRICT__') }}";
    const feeUrl = "{{ route('user.locations.fee') }}";
    const provincesUrl = "{{ route('user.locations.provinces') }}";
    const csrf = "{{ csrf_token() }}";

    let currentDiscount = parseInt(document.getElementById('discount_amount_input')?.value || 0, 10) || 0;
    let currentFee = 0;

    function fmt(n) {
        return new Intl.NumberFormat('vi-VN').format(n) + 'đ';
    }

    function updateTotals(fee) {
        currentFee = fee;
        const finalTotal = Math.max(0, subtotal - currentDiscount + fee);

        if (fee > 0) {
            shippingFeeText.innerText = fmt(fee);
            shippingFeeInput.value = fee;
            finalTotalText.innerText = fmt(finalTotal);
            checkoutButtons.forEach(button => { button.disabled = false; });

            if (ghnStatusBox) {
                ghnStatusBox.className = 'ghn-status-box ghn-status-success';
                ghnStatusBox.innerHTML = `
                    <div class="ghn-status-icon"><i class="bi bi-check-circle-fill text-success"></i></div>
                    <div>
                        <div class="ghn-status-title">Đã xác nhận phí vận chuyển: ${fmt(fee)}</div>
                        <div class="ghn-status-desc">Dịch vụ Giao hàng tiêu chuẩn GHN • Hỗ trợ đồng kiểm khi nhận hàng.</div>
                    </div>
                `;
            }
        } else {
            shippingFeeText.innerText = fee === 0 ? '0đ' : 'Chọn địa chỉ';
            shippingFeeInput.value = 0;
            finalTotalText.innerText = fmt(Math.max(0, subtotal - currentDiscount));
            checkoutButtons.forEach(button => { button.disabled = true; });

            if (ghnStatusBox) {
                ghnStatusBox.className = 'ghn-status-box ghn-status-neutral';
                ghnStatusBox.innerHTML = `
                    <div class="ghn-status-icon"><i class="bi bi-truck"></i></div>
                    <div>
                        <div class="ghn-status-title">Tính cước giao hàng GHN tự động</div>
                        <div class="ghn-status-desc">Vui lòng chọn Tỉnh/Thành, Quận/Huyện và Phường/Xã để tính phí giao hàng.</div>
                    </div>
                `;
            }
        }
    }

    function showShippingError(message) {
        updateTotals(0);
        shippingFeeText.innerText = 'Lỗi GHN';
        if (ghnStatusBox) {
            ghnStatusBox.className = 'ghn-status-box ghn-status-error';
            ghnStatusBox.innerHTML = `
                <div class="ghn-status-icon"><i class="bi bi-exclamation-triangle-fill text-danger"></i></div>
                <div>
                    <div class="ghn-status-title text-danger">Không thể tính phí vận chuyển</div>
                    <div class="ghn-status-desc">${message}</div>
                </div>
            `;
        }
    }

    // 1. Tải danh sách Tỉnh/Thành
    fetch(provincesUrl, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(res => {
            if (res.data) {
                let options = '<option value="">-- Chọn Tỉnh/Thành --</option>';
                res.data.forEach(p => {
                    options += `<option value="${p.ProvinceID}">${p.ProvinceName}</option>`;
                });
                provinceSelect.innerHTML = options;
            } else {
                provinceSelect.innerHTML = '<option value="">-- Không tải được tỉnh --</option>';
            }
        })
        .catch(() => {
            provinceSelect.innerHTML = '<option value="">-- Lỗi kết nối GHN --</option>';
        });

    // 2. Chọn Tỉnh → Quận/Huyện
    provinceSelect.addEventListener('change', function () {
        districtSelect.innerHTML = '<option value="">-- Đang tải --</option>';
        districtSelect.disabled = true;
        wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
        wardSelect.disabled = true;
        updateTotals(0);
        if (!this.value) return;

        fetch(districtsUrl.replace('__PROVINCE__', this.value), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(res => {
                if (res.data) {
                    let options = '<option value="">-- Chọn Quận/Huyện --</option>';
                    res.data.forEach(d => {
                        options += `<option value="${d.DistrictID}">${d.DistrictName}</option>`;
                    });
                    districtSelect.innerHTML = options;
                    districtSelect.disabled = false;
                } else {
                    districtSelect.innerHTML = '<option value="">-- Không tải được quận --</option>';
                }
            })
            .catch(() => {
                districtSelect.innerHTML = '<option value="">-- Lỗi GHN --</option>';
            });
    });

    // 3. Chọn Quận → Phường/Xã
    districtSelect.addEventListener('change', function () {
        wardSelect.innerHTML = '<option value="">-- Đang tải --</option>';
        wardSelect.disabled = true;
        updateTotals(0);
        if (!this.value) return;

        fetch(wardsUrl.replace('__DISTRICT__', this.value), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(res => {
                if (res.data) {
                    let options = '<option value="">-- Chọn Phường/Xã --</option>';
                    res.data.forEach(w => {
                        options += `<option value="${w.WardCode}">${w.WardName}</option>`;
                    });
                    wardSelect.innerHTML = options;
                    wardSelect.disabled = false;
                } else {
                    wardSelect.innerHTML = '<option value="">-- Không tải được phường --</option>';
                }
            })
            .catch(() => {
                wardSelect.innerHTML = '<option value="">-- Lỗi GHN --</option>';
            });
    });

    // 4. Chọn Phường → Tính cước GHN
    wardSelect.addEventListener('change', function () {
        if (!this.value || !districtSelect.value) return;
        checkoutButtons.forEach(button => { button.disabled = true; });
        shippingFeeInput.value = 0;
        shippingFeeText.innerText = 'Đang tính...';

        if (ghnStatusBox) {
            ghnStatusBox.className = 'ghn-status-box ghn-status-loading';
            ghnStatusBox.innerHTML = `
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <div>
                    <div class="ghn-status-title">Đang kết nối GHN...</div>
                    <div class="ghn-status-desc">Hệ thống đang tính cước giao hàng chính xác cho địa chỉ của bạn.</div>
                </div>
            `;
        }

        fetch(feeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                to_district_id: districtSelect.value,
                to_ward_code: this.value,
            }),
        })
            .then(r => r.json())
            .then(res => {
                if ((res.code === 200 || res.code === '200') && res.data) {
                    const fee = parseInt(res.data.total ?? res.data.total_fee ?? res.data.service_fee ?? 0, 10) || 0;
                    if (fee > 0) {
                        updateTotals(fee);
                    } else {
                        showShippingError('GHN không tính được phí cho địa chỉ này.');
                    }
                } else {
                    const msg = res.data?.code_message_value || res.code_message_value
                        || res.data?.message || res.message || 'GHN chưa hỗ trợ địa chỉ này';
                    showShippingError(typeof msg === 'string' ? msg : 'GHN chưa hỗ trợ địa chỉ này');
                }
            })
            .catch(err => {
                console.error(err);
                showShippingError('Không thể xác nhận địa chỉ với GHN. Vui lòng thử lại sau.');
            });
    });

    // Toggle active card on click
    window.selectPaymentMethod = function(method) {
        const optCod = document.getElementById('opt_cod');
        const optMomo = document.getElementById('opt_momo');
        const btnCod = document.getElementById('btn_cod_submit');
        const btnMomo = document.getElementById('btn_momo_submit');

        if (method === 'cod') {
            optCod.classList.add('active');
            optMomo.classList.remove('active');
            if (!btnCod.disabled) {
                btnCod.focus();
            }
        } else {
            optMomo.classList.add('active');
            optCod.classList.remove('active');
            if (!btnMomo.disabled) {
                btnMomo.focus();
            }
        }
    };

    // Xử lý Áp dụng và Gỡ bỏ mã khuyến mãi (Voucher)
    const applyCouponBtn = document.getElementById('btn_apply_coupon');
    const couponInput = document.getElementById('coupon_code_input');
    const couponMsg = document.getElementById('coupon_msg');
    const couponInputGroup = document.getElementById('coupon_input_group');
    const couponAppliedPill = document.getElementById('coupon_applied_pill');
    const discountRow = document.getElementById('discount_row');
    const discountAmountText = document.getElementById('discount_amount_text');
    const appliedCodeBadge = document.getElementById('applied_code_badge');
    const pillCode = document.getElementById('pill_code');
    const pillDesc = document.getElementById('pill_desc');
    const removeCouponBtn = document.getElementById('btn_remove_coupon');

    if (applyCouponBtn && couponInput) {
        applyCouponBtn.addEventListener('click', function () {
            const code = couponInput.value.trim();
            if (!code) {
                couponMsg.textContent = 'Vui lòng nhập mã giảm giá.';
                couponMsg.className = 'small mt-1 text-danger';
                couponMsg.style.display = 'block';
                return;
            }

            applyCouponBtn.disabled = true;
            applyCouponBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

            fetch("{{ route('user.coupon.apply') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ code: code }),
            })
            .then(r => r.json().then(data => ({ ok: r.ok, body: data })))
            .then(({ ok, body }) => {
                applyCouponBtn.disabled = false;
                applyCouponBtn.textContent = 'Áp dụng';

                if (ok && body.success) {
                    currentDiscount = parseInt(body.discount_amount, 10) || 0;
                    document.getElementById('discount_amount_input').value = currentDiscount;

                    discountAmountText.textContent = '-' + fmt(currentDiscount);
                    appliedCodeBadge.textContent = body.code;
                    pillCode.textContent = body.code;
                    pillDesc.textContent = 'Tiết kiệm ' + fmt(currentDiscount);

                    discountRow.style.display = 'flex';
                    couponInputGroup.style.display = 'none';
                    couponAppliedPill.style.display = 'flex';
                    couponMsg.style.display = 'none';

                    updateTotals(currentFee);
                } else {
                    couponMsg.textContent = body.message || 'Mã giảm giá không hợp lệ.';
                    couponMsg.className = 'small mt-1 text-danger';
                    couponMsg.style.display = 'block';
                }
            })
            .catch(err => {
                applyCouponBtn.disabled = false;
                applyCouponBtn.textContent = 'Áp dụng';
                couponMsg.textContent = 'Không thể kết nối máy chủ. Vui lòng thử lại sau.';
                couponMsg.className = 'small mt-1 text-danger';
                couponMsg.style.display = 'block';
            });
        });

        // Cho phép ấn Enter trong ô input voucher để áp dụng
        couponInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCouponBtn.click();
            }
        });
    }

    if (removeCouponBtn) {
        removeCouponBtn.addEventListener('click', function () {
            removeCouponBtn.disabled = true;
            fetch("{{ route('user.coupon.remove') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
            })
            .then(r => r.json())
            .then(body => {
                removeCouponBtn.disabled = false;
                currentDiscount = 0;
                document.getElementById('discount_amount_input').value = 0;
                discountRow.style.display = 'none';
                couponAppliedPill.style.display = 'none';
                couponInputGroup.style.display = 'block';
                couponInput.value = '';
                couponMsg.style.display = 'none';

                updateTotals(currentFee);
            })
            .catch(() => {
                removeCouponBtn.disabled = false;
            });
        });
    }
});
</script>
@endpush
