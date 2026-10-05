@extends('layouts.app')

@section('title', 'Thanh toán đơn hàng')

@section('content')
<style>
    .checkout-page {
        padding: 2rem 0 5rem;
        background: #FAF6F0;
        min-height: 80vh;
        font-family: 'Manrope', sans-serif;
        color: #3A2E26;
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
        color: #7E7065;
        text-decoration: none;
        transition: color 0.15s;
    }
    .step-item.active {
        color: #3A2E26;
        font-weight: 700;
    }
    .step-item.completed {
        color: #5A4536;
    }
    .step-item.completed:hover {
        color: #3F2F24;
    }
    .step-badge {
        width: 26px;
        height: 26px;
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 700;
        background: #F3E9DC;
        color: #7E7065;
        border: 1px solid #E6D8C8;
        transition: all 0.15s;
    }
    .step-item.active .step-badge {
        background: #5A4536;
        color: #FAF6F0;
        border-color: #5A4536;
        box-shadow: 0 0 0 3px rgba(90,69,54,0.15);
    }
    .step-item.completed .step-badge {
        background: #5A4536;
        color: #FAF6F0;
        border-color: #5A4536;
    }
    .step-line {
        width: 48px;
        height: 1px;
        background: #E6D8C8;
    }
    .step-line.completed {
        background: #5A4536;
    }

    /* ── Cards ── */
    .checkout-card {
        background: #fff;
        border-radius: 2px;
        border: 1px solid #E6D8C8;
        box-shadow: 0 4px 20px rgba(90, 69, 54, 0.04);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .checkout-card-header {
        padding: 1.1rem 1.5rem;
        border-bottom: 1px solid #E6D8C8;
        background: #FAF6F0;
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }
    .card-header-icon {
        width: 32px;
        height: 32px;
        border-radius: 2px;
        background: #F3E9DC;
        color: #5A4536;
        border: 1px solid #E6D8C8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .card-header-icon.payment-icon {
        background: #F3E9DC;
        color: #5A4536;
    }
    .card-header-title {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.25rem;
        font-weight: 600;
        color: #3A2E26;
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

    /* ── Ticket Voucher Styles ── */
    .quick-promo-chip {
        font-size: 0.78rem;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        padding: 0.2rem 0.55rem;
        color: #334155;
        transition: all 0.15s ease;
    }
    .quick-promo-chip:hover {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #1d4ed8;
        transform: translateY(-1px);
    }

    .voucher-ticket-card {
        display: flex;
        align-items: stretch;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .voucher-ticket-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59,130,246,0.1);
        transform: translateY(-1px);
    }
    .voucher-ticket-card.is-applied {
        border-color: #22c55e;
        background: #f0fdf4;
        box-shadow: 0 0 0 1px #22c55e;
    }
    .voucher-ticket-card.is-ineligible {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        opacity: 0.68;
        box-shadow: none;
        cursor: not-allowed;
    }
    .voucher-ticket-card.is-ineligible:hover {
        transform: none;
        box-shadow: none;
        border-color: #cbd5e1;
    }

    .voucher-stub {
        width: 82px;
        flex-shrink: 0;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        border-right: 1px dashed rgba(255,255,255,0.4);
    }
    .voucher-ticket-card.is-applied .voucher-stub {
        background: linear-gradient(135deg, #16a34a, #15803d);
    }
    .voucher-ticket-card.is-ineligible .voucher-stub {
        background: linear-gradient(135deg, #94a3b8, #64748b);
    }

    .voucher-stub-badge {
        padding: 0.5rem 0.25rem;
    }
    .voucher-stub .stub-val {
        display: block;
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.02em;
    }
    .voucher-stub .stub-tag {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        opacity: 0.9;
        margin-top: 2px;
    }

    .voucher-body {
        flex: 1;
        padding: 0.65rem 0.85rem;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .voucher-code {
        font-size: 0.82rem;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: 0.04em;
        background: #f1f5f9;
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }
    .voucher-saving-badge {
        font-size: 0.72rem;
        font-weight: 700;
        color: #15803d;
        background: #dcfce7;
        padding: 0.1rem 0.45rem;
        border-radius: 12px;
    }
    .voucher-name {
        font-size: 0.84rem;
        font-weight: 700;
        color: #0f172a;
        margin-top: 0.2rem;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .voucher-condition {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.2rem;
    }
    .voucher-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-top: 0.35rem;
        font-size: 0.72rem;
        color: #94a3b8;
    }
    .ineligible-notice {
        font-size: 0.74rem;
        margin-top: 0.25rem;
        padding: 0.25rem 0.45rem;
        background: #fef2f2;
        border-radius: 6px;
        border: 1px solid #fecaca;
    }

    .voucher-action {
        width: 90px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        border-left: 1px solid #f1f5f9;
        background: #fafbfc;
    }
    .voucher-action .btn {
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.3rem 0.65rem;
        white-space: nowrap;
        border-radius: 6px;
    }
    .btn-voucher-disabled {
        cursor: not-allowed !important;
        pointer-events: none;
    }

    /* ── Mini Voucher Card (Direct List) ── */
    .voucher-mini-card {
        display: flex;
        align-items: center;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.45rem 0.6rem;
        gap: 0.5rem;
        transition: all 0.15s ease;
    }
    .voucher-mini-card:hover {
        border-color: #3b82f6;
        background: #f8fafc;
    }
    .voucher-mini-card.is-applied {
        border-color: #22c55e;
        background: #f0fdf4;
    }
    .voucher-mini-card.is-ineligible {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        opacity: 0.72;
    }
    .voucher-mini-card.is-ineligible:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    .voucher-mini-left {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 60px;
        background: #f1f5f9;
        border-radius: 6px;
        padding: 0.2rem 0.35rem;
        border: 1px solid #e2e8f0;
    }
    .voucher-mini-card.is-applied .voucher-mini-left {
        background: #dcfce7;
        border-color: #86efac;
    }
    .mini-discount {
        font-size: 0.75rem;
        font-weight: 800;
        color: #2563eb;
        line-height: 1.1;
    }
    .voucher-mini-card.is-applied .mini-discount {
        color: #15803d;
    }
    .mini-code {
        font-size: 0.68rem;
        font-weight: 700;
        color: #475569;
    }
    .voucher-mini-mid {
        flex: 1;
        min-width: 0;
    }
    .mini-title {
        font-size: 0.76rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
    }
    .mini-sub {
        font-size: 0.7rem;
        margin-top: 1px;
    }
    .voucher-mini-right {
        flex-shrink: 0;
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
                    <div class="card p-3 mb-3">
                        <label for="coins_to_use" class="form-label">Dùng xu điểm danh ({{ number_format(auth()->user()->coin_balance) }} xu khả dụng)</label>
                        <input type="number" name="coins_to_use" id="coins_to_use" min="0" step="1" max="{{ auth()->user()->coin_balance }}" value="{{ old('coins_to_use', 0) }}" class="form-control">
                        <small>1 xu = {{ config('coins.vnd_per_coin') }}đ. Chỉ giảm tiền hàng; tiền hàng còn tối thiểu {{ number_format(config('coins.minimum_goods_payment')) }}đ.</small>
                        @error('coins_to_use')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="card p-3 mb-3">
                        <label for="referral_code" class="form-label">Mã giới thiệu (không bắt buộc)</label>
                        <input id="referral_code" name="referral_code" maxlength="32" class="form-control" value="{{ old('referral_code') }}" placeholder="REF-XXXXXX">
                        <small>Người giới thiệu nhận điểm khi đơn hàng hoàn thành.</small>
                        @error('referral_code')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

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
                                    ? \App\Models\Product::storageUrl($item['image'])
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

                        {{-- Ô nhập mã giảm giá Voucher & Đề xuất khuyến mãi --}}
                        <div class="coupon-section my-3 pt-2 pb-2 border-top border-bottom">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label mb-0 fw-bold small text-muted d-flex align-items-center gap-1">
                                    <i class="bi bi-ticket-perforated-fill text-primary"></i> Mã ưu đãi / Voucher
                                </label>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none fw-semibold p-0 text-primary d-flex align-items-center gap-1"
                                        id="btn_open_promo_modal" style="font-size:0.8rem;">
                                    <i class="bi bi-gift"></i> Chọn voucher
                                    @if(isset($availablePromotions) && $availablePromotions->count() > 0)
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle ms-1" style="font-size:0.7rem;">
                                            {{ $availablePromotions->count() }} khả dụng
                                        </span>
                                    @endif
                                </button>
                            </div>

                            {{-- Input group khi chưa áp dụng mã --}}
                            <div id="coupon_input_group" style="{{ ($discountAmount ?? 0) > 0 ? 'display:none;' : '' }}">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="coupon_code_input" class="form-control"
                                           placeholder="Nhập mã voucher (VD: GIAM20)..."
                                           style="text-transform:uppercase; font-weight:700; font-size:0.82rem; letter-spacing:0.04em;">
                                    <button type="button" id="btn_apply_coupon" class="btn btn-primary" style="font-weight:600; font-size:0.8rem; padding:0 0.85rem;">
                                        Áp dụng
                                    </button>
                                </div>
                                <div id="coupon_msg" class="small mt-1" style="display:none;"></div>

                                {{-- Danh sách Voucher hiển thị trực tiếp để khách hàng thấy ngay --}}
                                <div class="direct-promo-box mt-2 pt-2 border-top">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-dark fw-bold small d-flex align-items-center gap-1" style="font-size:0.8rem;">
                                            <i class="bi bi-stars text-warning"></i> Danh sách ưu đãi cho bạn
                                        </span>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.68rem;">
                                            {{ isset($availablePromotions) ? $availablePromotions->count() : 0 }} mã khả dụng
                                        </span>
                                    </div>

                                    <div class="direct-voucher-scroll-list" style="max-height: 260px; overflow-y: auto; padding-right: 4px;">
                                        {{-- 1. CÁC MÃ CÓ THỂ DÙNG (ĐỀ XUẤT) --}}
                                        @if(isset($availablePromotions) && $availablePromotions->count() > 0)
                                            <div class="mb-2">
                                                <div class="text-success fw-semibold small mb-1" style="font-size:0.72rem;">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Mã có thể áp dụng:
                                                </div>
                                                <div class="d-flex flex-column gap-1">
                                                    @foreach($availablePromotions as $promo)
                                                        @php
                                                            $isApplied = (!empty($coupon['code']) && strtoupper($coupon['code']) === strtoupper($promo->code));
                                                        @endphp
                                                        <div class="voucher-mini-card {{ $isApplied ? 'is-applied' : '' }}" data-code="{{ $promo->code }}">
                                                            <div class="voucher-mini-left">
                                                                <span class="mini-discount">{{ $promo->discount_display }}</span>
                                                                <span class="mini-code font-monospace">{{ $promo->code }}</span>
                                                            </div>
                                                            <div class="voucher-mini-mid">
                                                                <div class="mini-title text-truncate" title="{{ $promo->name }}">{{ $promo->name }}</div>
                                                                <div class="mini-sub text-muted">
                                                                    Đơn từ {{ number_format($promo->min_order_amount, 0, ',', '.') }}đ • Tiết kiệm <strong class="text-success">{{ number_format($promo->calculated_discount, 0, ',', '.') }}đ</strong>
                                                                </div>
                                                            </div>
                                                            <div class="voucher-mini-right">
                                                                @if($isApplied)
                                                                    <button type="button" class="btn btn-sm btn-success py-1 px-2 disabled btn-voucher-apply" data-code="{{ $promo->code }}" style="font-size:0.72rem;">
                                                                        <i class="bi bi-check-lg"></i> Đang dùng
                                                                    </button>
                                                                @else
                                                                    <button type="button" class="btn btn-sm btn-primary py-1 px-2 btn-voucher-apply" data-code="{{ $promo->code }}" style="font-size:0.72rem; font-weight:600;">
                                                                        Áp dụng
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- 2. VOUCHER KO DÙNG ĐƯỢC: HIỂN THỊ Ở DƯỚI VÀ KO ẤN ADD ĐƯỢC --}}
                                        @if(isset($ineligiblePromotions) && $ineligiblePromotions->count() > 0)
                                            <div class="mt-2 pt-2 border-top">
                                                <div class="text-muted fw-semibold small mb-1" style="font-size:0.72rem;">
                                                    <i class="bi bi-lock-fill me-1"></i>Chưa đủ điều kiện (hiển thị để biết điều kiện):
                                                </div>
                                                <div class="d-flex flex-column gap-1">
                                                    @foreach($ineligiblePromotions as $promo)
                                                        <div class="voucher-mini-card is-ineligible" data-code="{{ $promo->code }}">
                                                            <div class="voucher-mini-left">
                                                                <span class="mini-discount text-muted">{{ $promo->discount_display }}</span>
                                                                <span class="mini-code font-monospace text-muted">{{ $promo->code }}</span>
                                                            </div>
                                                            <div class="voucher-mini-mid">
                                                                <div class="mini-title text-muted text-truncate" title="{{ $promo->name }}">{{ $promo->name }}</div>
                                                                <div class="mini-sub text-danger fw-semibold">
                                                                    @if($promo->need_more_amount > 0)
                                                                        <i class="bi bi-exclamation-circle me-1"></i>Mua thêm {{ number_format($promo->need_more_amount, 0, ',', '.') }}đ để dùng
                                                                    @else
                                                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $promo->ineligible_reason ?? 'Chưa đủ điều kiện' }}
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="voucher-mini-right">
                                                                {{-- Nút disabled không thể ấn Add được --}}
                                                                <button type="button" class="btn btn-sm btn-light border text-muted py-1 px-2 btn-voucher-disabled"
                                                                        disabled style="font-size:0.72rem; cursor: not-allowed; opacity: 0.6;" title="Chưa đủ điều kiện">
                                                                    Áp dụng
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Pill khi đã áp dụng mã thành công --}}
                            <div id="coupon_applied_pill" class="d-flex align-items-center justify-content-between p-2 rounded-2 mt-1"
                                 style="background:#f0fdf4; border:1px dashed #86efac; font-size:0.82rem; {{ ($discountAmount ?? 0) > 0 ? '' : 'display:none!important;' }}">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:28px; height:28px; flex-shrink:0;">
                                        <i class="bi bi-ticket-perforated fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="fw-bold text-success font-monospace" id="pill_code">{{ $coupon['code'] ?? '' }}</span>
                                            <span class="badge bg-success-subtle text-success py-0 px-1" style="font-size:0.68rem;">Đã áp dụng</span>
                                        </div>
                                        <small class="text-muted d-block" id="pill_desc">Tiết kiệm {{ number_format($discountAmount ?? 0, 0, ',', '.') }}đ</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-link p-0 text-primary text-decoration-none fw-semibold" id="btn_change_coupon" style="font-size:0.78rem;">
                                        Đổi mã
                                    </button>
                                    <button type="button" id="btn_remove_coupon" class="btn btn-sm btn-link text-danger p-0 text-decoration-none fw-bold" title="Hủy mã" style="font-size:0.78rem;">
                                        Gỡ bỏ
                                    </button>
                                </div>
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
                        <div class="calc-row"><span>Giảm bằng xu</span><strong id="coin-discount-text" class="text-success">-0đ</strong></div>
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

{{-- Modal danh sách khuyến mãi & Voucher --}}
<div class="modal fade" id="promoListModal" tabindex="-1" aria-labelledby="promoListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">
            {{-- Modal Header --}}
            <div class="modal-header border-bottom pb-3" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="promoListModalLabel" style="font-size:1.05rem;">
                        <i class="bi bi-ticket-perforated-fill text-primary"></i> Chọn Voucher Khuyến Mãi
                    </h5>
                    <div class="text-muted small" style="font-size:0.78rem;">
                        Đơn hàng hiện tại: <strong class="text-dark" id="modal_subtotal_text">{{ number_format($totalPrice, 0, ',', '.') }}đ</strong>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-3" style="background:#f8fafc; max-height:70vh;">
                {{-- Ô nhập mã trực tiếp trong modal --}}
                <div class="bg-white p-2 rounded-3 border mb-3 shadow-sm">
                    <label class="form-label small fw-semibold text-muted mb-1">Bạn có mã ưu đãi riêng?</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="modal_coupon_input" class="form-control"
                               placeholder="Nhập mã voucher tại đây..."
                               style="text-transform:uppercase; font-weight:700; font-size:0.85rem; letter-spacing:0.04em;">
                        <button type="button" id="btn_modal_apply_input" class="btn btn-primary px-3 fw-semibold">
                            Áp dụng
                        </button>
                    </div>
                    <div id="modal_coupon_msg" class="small mt-1" style="display:none;"></div>
                </div>

                {{-- PHẦN 1: ĐỀ XUẤT KHUYẾN MÃI CÓ THỂ DÙNG --}}
                <div class="promo-group mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size:0.88rem;">
                            <i class="bi bi-stars text-warning"></i> Khuyến mãi có thể dùng
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle" id="modal_available_count" style="font-size:0.72rem;">
                            {{ isset($availablePromotions) ? $availablePromotions->count() : 0 }} mã khả dụng
                        </span>
                    </div>

                    <div id="modal_available_list" class="d-flex flex-column gap-2">
                        @if(isset($availablePromotions) && $availablePromotions->count() > 0)
                            @foreach($availablePromotions as $promo)
                                @php
                                    $isApplied = (!empty($coupon['code']) && strtoupper($coupon['code']) === strtoupper($promo->code));
                                @endphp
                                <div class="voucher-ticket-card {{ $isApplied ? 'is-applied' : '' }}" data-code="{{ $promo->code }}">
                                    <div class="voucher-stub">
                                        <div class="voucher-stub-badge">
                                            @if($promo->discount_type === 'percent')
                                                <span class="stub-val">-{{ (int)$promo->discount_value }}%</span>
                                            @else
                                                <span class="stub-val">-{{ number_format($promo->discount_value / 1000, 0) }}k</span>
                                            @endif
                                            <span class="stub-tag">GIẢM</span>
                                        </div>
                                    </div>
                                    <div class="voucher-body">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="voucher-code font-monospace">{{ $promo->code }}</span>
                                            <span class="voucher-saving-badge">
                                                Tiết kiệm {{ number_format($promo->calculated_discount, 0, ',', '.') }}đ
                                            </span>
                                        </div>
                                        <div class="voucher-name">{{ $promo->name }}</div>
                                        <div class="voucher-condition">
                                            Đơn tối thiểu: <strong>{{ number_format($promo->min_order_amount, 0, ',', '.') }}đ</strong>
                                            @if($promo->max_discount_amount > 0)
                                                (Tối đa {{ number_format($promo->max_discount_amount, 0, ',', '.') }}đ)
                                            @endif
                                        </div>
                                        <div class="voucher-footer">
                                            <span class="voucher-expiry">
                                                <i class="bi bi-clock me-1"></i>HSD: {{ $promo->end_date ? $promo->end_date->format('d/m/Y') : 'Không giới hạn' }}
                                            </span>
                                            @if(!is_null($promo->remaining_uses))
                                                <span class="voucher-limit text-muted">
                                                    Còn {{ $promo->remaining_uses }} lượt
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="voucher-action">
                                        @if($isApplied)
                                            <button type="button" class="btn btn-sm btn-success btn-voucher-apply disabled" data-code="{{ $promo->code }}">
                                                <i class="bi bi-check-lg"></i> Đang dùng
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-primary btn-voucher-apply" data-code="{{ $promo->code }}">
                                                Áp dụng
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-3 text-muted bg-white rounded-3 border" style="font-size:0.83rem;">
                                <i class="bi bi-inbox fs-4 d-block text-secondary mb-1"></i>
                                Chưa có mã khuyến mãi nào phù hợp với giá trị đơn hàng này.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- PHẦN 2: VOUCHER KHÔNG DÙNG ĐƯỢC HIỂN THỊ Ở DƯỚI VÀ KHÔNG ẤN ADD ĐƯỢC --}}
                <div class="promo-group">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-muted d-flex align-items-center gap-1" style="font-size:0.88rem;">
                            <i class="bi bi-slash-circle text-secondary"></i> Voucher chưa đủ điều kiện
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" id="modal_ineligible_count" style="font-size:0.72rem;">
                            {{ isset($ineligiblePromotions) ? $ineligiblePromotions->count() : 0 }} mã
                        </span>
                    </div>

                    <div id="modal_ineligible_list" class="d-flex flex-column gap-2">
                        @if(isset($ineligiblePromotions) && $ineligiblePromotions->count() > 0)
                            @foreach($ineligiblePromotions as $promo)
                                <div class="voucher-ticket-card is-ineligible" data-code="{{ $promo->code }}">
                                    <div class="voucher-stub">
                                        <div class="voucher-stub-badge">
                                            @if($promo->discount_type === 'percent')
                                                <span class="stub-val">-{{ (int)$promo->discount_value }}%</span>
                                            @else
                                                <span class="stub-val">-{{ number_format($promo->discount_value / 1000, 0) }}k</span>
                                            @endif
                                            <span class="stub-tag">GIẢM</span>
                                        </div>
                                    </div>
                                    <div class="voucher-body">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="voucher-code font-monospace text-muted">{{ $promo->code }}</span>
                                            <span class="badge bg-secondary-subtle text-muted" style="font-size:0.68rem;">Chưa đủ ĐK</span>
                                        </div>
                                        <div class="voucher-name text-muted">{{ $promo->name }}</div>
                                        
                                        {{-- Lý do không dùng được hiển thị rõ ràng --}}
                                        <div class="ineligible-notice">
                                            @if($promo->need_more_amount > 0)
                                                <span class="text-danger fw-semibold">
                                                    <i class="bi bi-info-circle-fill me-1"></i>Mua thêm {{ number_format($promo->need_more_amount, 0, ',', '.') }}đ để sử dụng
                                                </span>
                                                <div class="small text-muted">
                                                    Đơn hàng tối thiểu: {{ number_format($promo->min_order_amount, 0, ',', '.') }}đ
                                                </div>
                                            @else
                                                <span class="text-danger fw-semibold">
                                                    <i class="bi bi-info-circle-fill me-1"></i>{{ $promo->ineligible_reason ?? 'Chưa đủ điều kiện áp dụng' }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="voucher-footer">
                                            <span class="voucher-expiry text-muted">
                                                <i class="bi bi-clock me-1"></i>HSD: {{ $promo->end_date ? $promo->end_date->format('d/m/Y') : 'Không giới hạn' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="voucher-action">
                                        {{-- VOUCHER KO DÙNG ĐƯỢC: HIỂN THỊ Ở DƯỚI VÀ KO ẤN ADD ĐƯỢC --}}
                                        <button type="button" class="btn btn-sm btn-light border text-muted btn-voucher-disabled"
                                                disabled style="cursor: not-allowed; opacity: 0.6;" title="Chưa đủ điều kiện áp dụng cho đơn hàng hiện tại">
                                            Áp dụng
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-2 text-muted small">
                                Không có voucher nào bị hạn chế.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer border-top py-2 px-3 justify-content-between bg-white">
                <span class="text-muted small" style="font-size:0.75rem;">
                    <i class="bi bi-shield-check text-success me-1"></i>Mỗi đơn hàng áp dụng tối đa 01 voucher
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Đóng</button>
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
    const coinInput = document.getElementById('coins_to_use');
    const coinBalance = {{ (int) auth()->user()->coin_balance }};
    const coinRate = {{ (int) config('coins.vnd_per_coin') }};
    const minGoods = {{ (int) config('coins.minimum_goods_payment') }};
    coinInput.addEventListener('input', () => updateTotals(currentFee));

    function fmt(n) {
        return new Intl.NumberFormat('vi-VN').format(n) + 'đ';
    }

    function updateTotals(fee) {
        currentFee = fee;
        const maxCoins = Math.min(coinBalance, Math.floor(Math.max(0, subtotal - currentDiscount - minGoods) / coinRate));
        coinInput.max = maxCoins;
        const coins = Math.max(0, Math.min(maxCoins, parseInt(coinInput.value, 10) || 0));
        if (Number(coinInput.value) > maxCoins) coinInput.value = coins;
        const coinDiscount = coins * coinRate;
        document.getElementById('coin-discount-text').textContent = '-' + fmt(coinDiscount);
        const finalTotal = Math.max(0, subtotal - currentDiscount - coinDiscount + fee);
        const codLimit = @json(\App\Services\GHNOrderService::codLimit());
        const codExceeded = Math.round(Math.max(0, subtotal - currentDiscount - coinDiscount)) > codLimit;
        const codButton = document.getElementById('btn_cod_submit');
        let codNotice = document.getElementById('cod-limit-notice');
        if (!codNotice && codButton) {
            codNotice = document.createElement('div');
            codNotice.id = 'cod-limit-notice';
            codNotice.className = 'alert alert-warning mt-2';
            codNotice.setAttribute('role', 'status');
            codButton.parentNode.insertBefore(codNotice, codButton);
        }
        if (codNotice) {
            codNotice.hidden = !codExceeded;
            codNotice.textContent = @json(\App\Services\GHNOrderService::codLimitMessage());
        }

        if (fee > 0) {
            shippingFeeText.innerText = fmt(fee);
            shippingFeeInput.value = fee;
            finalTotalText.innerText = fmt(finalTotal);
            checkoutButtons.forEach(button => { button.disabled = false; });
            if (codButton) codButton.disabled = codExceeded;

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
            finalTotalText.innerText = fmt(Math.max(0, subtotal - currentDiscount - coinDiscount));
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

    // ── Xử lý Áp dụng và Gỡ bỏ mã khuyến mãi (Voucher) ──
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

    // Các phần tử trong Modal Voucher
    const promoModalEl = document.getElementById('promoListModal');
    const btnOpenPromoModal = document.getElementById('btn_open_promo_modal');
    const btnSeeMorePromos = document.getElementById('btn_see_more_promos');
    const btnChangeCoupon = document.getElementById('btn_change_coupon');
    const modalCouponInput = document.getElementById('modal_coupon_input');
    const btnModalApplyInput = document.getElementById('btn_modal_apply_input');
    const modalCouponMsg = document.getElementById('modal_coupon_msg');

    function getPromoModalInstance() {
        if (!promoModalEl || !window.bootstrap) return null;
        return bootstrap.Modal.getOrCreateInstance(promoModalEl);
    }

    if (btnOpenPromoModal) {
        btnOpenPromoModal.addEventListener('click', function () {
            getPromoModalInstance()?.show();
        });
    }
    if (btnSeeMorePromos) {
        btnSeeMorePromos.addEventListener('click', function () {
            getPromoModalInstance()?.show();
        });
    }
    if (btnChangeCoupon) {
        btnChangeCoupon.addEventListener('click', function () {
            getPromoModalInstance()?.show();
        });
    }

    // Hàm dùng chung để áp dụng mã khuyến mãi
    function applyPromoCode(rawCode, triggerBtn = null) {
        const code = (rawCode || '').trim();
        if (!code) {
            showErrorMsg('Vui lòng nhập hoặc chọn mã giảm giá.');
            return;
        }

        let originalBtnHtml = '';
        if (triggerBtn) {
            originalBtnHtml = triggerBtn.innerHTML;
            triggerBtn.disabled = true;
            triggerBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        }

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
            if (triggerBtn) {
                triggerBtn.disabled = false;
                triggerBtn.innerHTML = originalBtnHtml;
            }

            if (ok && body.success) {
                currentDiscount = parseInt(body.discount_amount, 10) || 0;
                document.getElementById('discount_amount_input').value = currentDiscount;

                discountAmountText.textContent = '-' + fmt(currentDiscount);
                if (appliedCodeBadge) appliedCodeBadge.textContent = body.code;
                if (pillCode) pillCode.textContent = body.code;
                if (pillDesc) pillDesc.textContent = 'Tiết kiệm ' + fmt(currentDiscount);

                discountRow.style.display = 'flex';
                couponInputGroup.style.display = 'none';
                couponAppliedPill.style.display = 'flex';
                clearErrorMsg();

                updateTotals(currentFee);

                // Cập nhật trạng thái các voucher card trong modal
                updateModalVoucherStates(body.code);

                // Đóng modal nếu đang mở
                getPromoModalInstance()?.hide();

                if (window.showCartToast) {
                    window.showCartToast(body.message || 'Áp dụng mã thành công!');
                }
            } else {
                showErrorMsg(body.message || 'Mã giảm giá không hợp lệ.');
            }
        })
        .catch(err => {
            console.error('Lỗi áp dụng voucher:', err);
            if (triggerBtn) {
                triggerBtn.disabled = false;
                triggerBtn.innerHTML = originalBtnHtml;
            }
            showErrorMsg('Không thể kết nối máy chủ. Vui lòng thử lại sau.');
        });
    }

    function showErrorMsg(msg) {
        if (couponMsg) {
            couponMsg.textContent = msg;
            couponMsg.className = 'small mt-1 text-danger';
            couponMsg.style.display = 'block';
        }
        if (modalCouponMsg) {
            modalCouponMsg.textContent = msg;
            modalCouponMsg.className = 'small mt-1 text-danger';
            modalCouponMsg.style.display = 'block';
        }
    }

    function clearErrorMsg() {
        if (couponMsg) couponMsg.style.display = 'none';
        if (modalCouponMsg) modalCouponMsg.style.display = 'none';
    }

    function updateModalVoucherStates(appliedCode) {
        document.querySelectorAll('.voucher-ticket-card, .voucher-mini-card').forEach(card => {
            const cardCode = card.dataset.code;
            const actionBtn = card.querySelector('.btn-voucher-apply');
            if (card.classList.contains('is-ineligible')) {
                // Voucher không dùng được: giữ nguyên disabled
                return;
            }

            if (appliedCode && cardCode && cardCode.toUpperCase() === appliedCode.toUpperCase()) {
                card.classList.add('is-applied');
                if (actionBtn) {
                    actionBtn.className = 'btn btn-sm btn-success btn-voucher-apply disabled';
                    actionBtn.innerHTML = '<i class="bi bi-check-lg"></i> Đang dùng';
                }
            } else {
                card.classList.remove('is-applied');
                if (actionBtn) {
                    actionBtn.className = 'btn btn-sm btn-primary btn-voucher-apply';
                    actionBtn.textContent = 'Áp dụng';
                }
            }
        });
    }

    // Áp dụng từ nút Áp dụng ở ô input chính
    if (applyCouponBtn && couponInput) {
        applyCouponBtn.addEventListener('click', function () {
            applyPromoCode(couponInput.value, applyCouponBtn);
        });

        couponInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCouponBtn.click();
            }
        });
    }

    // Áp dụng từ ô input bên trong modal
    if (btnModalApplyInput && modalCouponInput) {
        btnModalApplyInput.addEventListener('click', function () {
            applyPromoCode(modalCouponInput.value, btnModalApplyInput);
        });

        modalCouponInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnModalApplyInput.click();
            }
        });
    }

    // Áp dụng từ các chip đề xuất nhanh
    document.querySelectorAll('.btn-apply-quick-promo').forEach(btn => {
        btn.addEventListener('click', function () {
            const code = this.dataset.code;
            applyPromoCode(code, this);
        });
    });

    // Áp dụng từ các nút trong Modal danh sách voucher
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-voucher-apply');
        if (btn && !btn.disabled && !btn.classList.contains('disabled')) {
            const code = btn.dataset.code;
            applyPromoCode(code, btn);
        }
    });

    // Gỡ bỏ mã khuyến mãi
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
                if (couponInput) couponInput.value = '';
                if (modalCouponInput) modalCouponInput.value = '';
                clearErrorMsg();

                updateTotals(currentFee);
                updateModalVoucherStates(null);

                if (window.showCartToast) {
                    window.showCartToast('Đã hủy áp dụng mã giảm giá.');
                }
            })
            .catch(() => {
                removeCouponBtn.disabled = false;
            });
        });
    }
});
</script>
@endpush
