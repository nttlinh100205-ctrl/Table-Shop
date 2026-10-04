@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
<style>
    .cart-page { padding: 2rem 0 5rem; background: #FAF6F0; min-height: 60vh; font-family: 'Manrope', sans-serif; color: #3A2E26; }

    /* ── Page Header ── */
    .cart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .cart-heading {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 2rem; font-weight: 600; color: #3A2E26;
        display: flex; align-items: center; gap: 0.6rem; margin: 0;
        letter-spacing: -0.01em;
    }
    .cart-heading .count-pill {
        font-family: 'Manrope', sans-serif;
        font-size: 0.72rem; font-weight: 600;
        background: #F3E9DC; color: #5A4536;
        border: 1px solid #E6D8C8;
        padding: 0.2rem 0.65rem; border-radius: 2px; line-height: 1.5;
        letter-spacing: 0.02em;
    }
    .btn-clear {
        font-size: 0.8rem; font-weight: 600; color: #8B4513;
        background: #fff; border: 1px solid #E6D8C8;
        border-radius: 2px; padding: 0.4rem 0.9rem;
        text-decoration: none; transition: all 0.15s; cursor: pointer;
        display: flex; align-items: center; gap: 0.35rem;
    }
    .btn-clear:hover { background: #FAF6F0; color: #5A4536; border-color: #5A4536; }

    /* ── Cart Card ── */
    .cart-card {
        background: #fff;
        border-radius: 2px;
        border: 1px solid #E6D8C8;
        box-shadow: 0 4px 20px rgba(90, 69, 54, 0.04);
        overflow: hidden;
    }

    /* ── Table Header ── */
    .cart-table-head {
        display: grid;
        grid-template-columns: 40px 1fr 120px 140px 120px 40px;
        align-items: center;
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid #E6D8C8;
        background: #FAF6F0;
        font-size: 0.75rem;
        font-weight: 700;
        color: #7E7065;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        gap: 0.5rem;
    }

    /* ── Cart Row ── */
    .cart-row {
        display: grid;
        grid-template-columns: 40px 1fr 120px 140px 120px 40px;
        align-items: center;
        padding: 1.2rem 1.25rem;
        border-bottom: 1px solid #FAF6F0;
        gap: 0.5rem;
        transition: background 0.15s;
    }
    .cart-row:last-child { border-bottom: none; }
    .cart-row:hover { background: #FCFAF7; }
    .cart-row.removing { opacity: 0.4; pointer-events: none; transition: opacity 0.3s; }

    /* Product cell */
    .cart-product-cell { display: flex; align-items: center; gap: 1rem; min-width: 0; }
    .cart-product-img {
        width: 72px; height: 72px; border-radius: 2px;
        object-fit: cover; border: 1px solid #E6D8C8;
        flex-shrink: 0;
    }
    .cart-product-placeholder {
        width: 72px; height: 72px; border-radius: 2px;
        background: #FAF6F0; border: 1px solid #E6D8C8;
        display: flex; align-items: center; justify-content: center;
        color: #7E7065; font-size: 1.25rem; flex-shrink: 0;
    }
    .cart-product-name {
        font-family: 'Manrope', sans-serif;
        font-weight: 600; color: #3A2E26; font-size: 0.92rem;
        text-decoration: none; line-height: 1.4;
        display: block; margin-bottom: 0.25rem; transition: color 0.15s;
    }
    .cart-product-name:hover { color: #5A4536; }
    .cart-product-meta {
        display: flex; flex-wrap: wrap; gap: 0.35rem;
        margin-top: 0.25rem;
    }
    .cart-meta-chip {
        display: inline-flex; align-items: center; gap: 0.25rem;
        font-size: 0.72rem; font-weight: 500; color: #5A4536;
        background: #F3E9DC; border: 1px solid #E6D8C8;
        padding: 0.15rem 0.5rem; border-radius: 2px;
    }
    .cart-change-link {
        font-size: 0.73rem; color: #5A4536; text-decoration: none;
        font-weight: 600; display: inline-flex; align-items: center; gap: 0.2rem;
        margin-top: 0.25rem;
    }
    .cart-change-link:hover { text-decoration: underline; color: #3F2F24; }

    /* Price cell */
    .cart-price-cell { font-size: 0.88rem; color: #7E7065; font-weight: 500; text-align: center; }

    /* Qty cell */
    .cart-qty-cell { display: flex; justify-content: center; }
    .qty-control {
        display: inline-flex; align-items: center;
        border: 1px solid #E6D8C8; border-radius: 2px;
        overflow: hidden; background: #fff;
    }
    .qty-btn {
        width: 32px; height: 32px; border: none; background: transparent;
        color: #5A4536; font-size: 1rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.12s;
        flex-shrink: 0;
    }
    .qty-btn:hover { background: #FAF6F0; }
    .qty-input {
        width: 38px; border: none; border-left: 1px solid #E6D8C8;
        border-right: 1px solid #E6D8C8; text-align: center;
        font-size: 0.88rem; font-weight: 700; color: #3A2E26;
        background: transparent; outline: none; padding: 0;
        height: 32px; -moz-appearance: textfield;
    }
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; }

    /* Subtotal cell */
    .cart-subtotal-cell {
        font-size: 0.95rem; font-weight: 700; color: #5A4536; text-align: right;
    }

    /* Remove btn */
    .btn-remove-item {
        width: 30px; height: 30px; border-radius: 2px; border: none;
        background: transparent; color: #A69282; font-size: 0.85rem;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.15s; margin: auto;
    }
    .btn-remove-item:hover { background: #FAF6F0; color: #8B4513; }

    /* Check-all row */
    .cart-check-all-row {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid #E6D8C8;
        font-size: 0.82rem; font-weight: 600; color: #7E7065;
        background: #fff;
    }
    .cart-check-all-row .form-check-input {
        width: 17px; height: 17px; cursor: pointer; border-color: #E6D8C8;
    }
    .cart-check-all-row .form-check-input:checked { background-color: #5A4536; border-color: #5A4536; }
    .cart-row .form-check-input:checked { background-color: #5A4536; border-color: #5A4536; }

    /* ── Order Summary ── */
    .order-summary-card {
        background: #fff; border-radius: 2px;
        border: 1px solid #E6D8C8;
        box-shadow: 0 4px 20px rgba(90, 69, 54, 0.04);
        padding: 1.5rem;
        position: sticky; top: 80px;
    }
    .summary-title {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.35rem; font-weight: 600; color: #3A2E26;
        margin-bottom: 1.25rem; padding-bottom: 0.875rem;
        border-bottom: 1px solid #FAF6F0;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .summary-row {
        display: flex; justify-content: space-between;
        align-items: center; font-size: 0.88rem;
        padding: 0.45rem 0; color: #7E7065;
    }
    .summary-row strong { color: #3A2E26; }
    .summary-divider {
        border: none; border-top: 1px dashed #E6D8C8;
        margin: 0.85rem 0;
    }
    .summary-total-row {
        display: flex; justify-content: space-between; align-items: center;
        padding-top: 0.85rem;
    }
    .summary-total-label { font-size: 0.92rem; font-weight: 600; color: #3A2E26; }
    .summary-total-value {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.6rem; font-weight: 700; color: #5A4536;
    }
    .btn-checkout {
        width: 100%; padding: 0.85rem 1rem;
        background: #5A4536;
        color: #FAF6F0; border: none; border-radius: 2px;
        font-size: 0.85rem; font-weight: 600; font-family: 'Manrope', sans-serif;
        cursor: pointer;
        transition: background 0.2s, transform 0.15s;
        margin-top: 1.25rem;
        display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        letter-spacing: 0.04em; text-transform: uppercase;
    }
    .btn-checkout:hover:not(:disabled) { background: #3F2F24; transform: translateY(-1px); }
    .btn-checkout:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }
    .btn-continue {
        width: 100%; padding: 0.65rem 1rem;
        background: transparent; color: #5A4536;
        border: 1px solid #5A4536; border-radius: 2px;
        font-size: 0.82rem; font-weight: 600; cursor: pointer;
        transition: all 0.15s; margin-top: 0.5rem;
        display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        text-decoration: none;
    }
    .btn-continue:hover { border-color: #3F2F24; color: #3F2F24; background: #F3E9DC; }

    /* ── Empty State ── */
    .cart-empty {
        background: #fff; border-radius: 2px; border: 1px solid #E6D8C8;
        box-shadow: 0 4px 20px rgba(90, 69, 54, 0.04);
        padding: 4rem 1.5rem; text-align: center;
    }
    .cart-empty-icon {
        width: 80px; height: 80px; border-radius: 50%;
        background: #FAF6F0; margin: 0 auto 1.25rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: #5A4536;
    }
    .cart-empty h4 {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.35rem; font-weight: 600; color: #3A2E26; margin-bottom: 0.5rem;
    }
    .cart-empty p { font-size: 0.88rem; color: #7E7065; margin-bottom: 1.5rem; }

    /* Mobile responsive */
    @media (max-width: 767px) {
        .cart-table-head { display: none; }
        .cart-row {
            grid-template-columns: 40px 1fr 40px;
            grid-template-rows: auto auto auto;
        }
        .cart-row > *:nth-child(3), /* price */
        .cart-row > *:nth-child(4) { display: none; } /* qty handled inline */
        .cart-product-cell { grid-column: 2; }
        .cart-subtotal-cell { grid-column: 2; text-align: left; }
        .cart-qty-cell { grid-column: 2; justify-content: flex-start; margin-top: 0.25rem; }
        .btn-remove-item { grid-column: 3; grid-row: 1; align-self: start; }
    }
    @media (min-width: 768px) and (max-width: 991px) {
        .cart-table-head { grid-template-columns: 40px 1fr 100px 120px 100px 40px; }
        .cart-row        { grid-template-columns: 40px 1fr 100px 120px 100px 40px; }
    }

    /* ── Mini Voucher Card (Cart) ── */
    .cart-voucher-box {
        background: #FAF6F0;
        border: 1px solid #E6D8C8;
        border-radius: 2px;
        padding: 0.75rem;
        margin-top: 1rem;
        margin-bottom: 0.75rem;
    }
    .voucher-mini-card {
        display: flex;
        align-items: center;
        background: #fff;
        border: 1px solid #E6D8C8;
        border-radius: 2px;
        padding: 0.45rem 0.6rem;
        gap: 0.5rem;
        transition: all 0.15s ease;
    }
    .voucher-mini-card:hover {
        border-color: #5A4536;
        background: #FCFAF7;
    }
    .voucher-mini-card.is-applied {
        border-color: #5A4536;
        background: #F3E9DC;
    }
    .voucher-mini-card.is-ineligible {
        background: #FAF6F0;
        border: 1px dashed #E6D8C8;
        opacity: 0.72;
    }
    .voucher-mini-left {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 58px;
        background: #FAF6F0;
        border-radius: 2px;
        padding: 0.2rem 0.35rem;
        border: 1px solid #E6D8C8;
    }
    .voucher-mini-card.is-applied .voucher-mini-left {
        background: #E6D8C8;
        border-color: #5A4536;
    }
    .mini-discount {
        font-size: 0.75rem;
        font-weight: 800;
        color: #5A4536;
        line-height: 1.1;
    }
    .voucher-mini-card.is-applied .mini-discount {
        color: #3F2F24;
    }
    .mini-code {
        font-size: 0.68rem;
        font-weight: 700;
        color: #7E7065;
    }
    .voucher-mini-mid {
        flex: 1;
        min-width: 0;
    }
    .mini-title {
        font-size: 0.76rem;
        font-weight: 700;
        color: #3A2E26;
        line-height: 1.25;
    }
    .mini-sub {
        font-size: 0.7rem;
        margin-top: 1px;
        color: #7E7065;
    }
    .voucher-mini-right {
        flex-shrink: 0;
    }
    .btn-voucher-disabled {
        cursor: not-allowed !important;
        pointer-events: none;
    }
</style>

<div class="cart-page">
<div class="container">

    {{-- Page Header --}}
    <div class="cart-header">
        <h1 class="cart-heading">
            <i class="bi bi-bag" style="color:#5A4536;"></i>
            Giỏ hàng của bạn
            @if(count($cart) > 0)
                <span class="count-pill">{{ count($cart) }} sản phẩm</span>
            @endif
        </h1>
        @if(count($cart) > 0)
            <form action="{{ route('user.cart.clear') }}" method="POST"
                  onsubmit="return confirm('Xóa toàn bộ giỏ hàng?')">
                @csrf
                <button type="submit" class="btn-clear">
                    <i class="bi bi-trash3"></i> Xóa tất cả
                </button>
            </form>
        @endif
    </div>

    @if(count($cart) === 0)
        {{-- Empty State --}}
        <div class="cart-empty">
            <div class="cart-empty-icon">
                <i class="bi bi-cart-x"></i>
            </div>
            <h4>Giỏ hàng đang trống</h4>
            <p>Hãy thêm sản phẩm vào giỏ để tiến hành thanh toán.</p>
            <a href="{{ route('user.home') }}"
               style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.65rem 1.5rem;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;border-radius:10px;font-size:0.875rem;font-weight:700;text-decoration:none;">
                <i class="bi bi-shop"></i> Khám phá sản phẩm
            </a>
        </div>
    @else
        <form id="checkout-form" action="{{ route('user.payment.index') }}" method="GET">
            @csrf
            <div class="row g-3 align-items-start">
                {{-- Left: Cart Items --}}
                <div class="col-lg-8">
                    <div class="cart-card">
                        {{-- Check All --}}
                        <div class="cart-check-all-row">
                            <input type="checkbox" class="form-check-input" id="check-all" checked>
                            <label for="check-all" style="cursor:pointer;">Chọn tất cả ({{ count($cart) }} sản phẩm)</label>
                        </div>

                        {{-- Table Header (desktop) --}}
                        <div class="cart-table-head">
                            <div></div>
                            <div>Sản phẩm</div>
                            <div class="text-center">Đơn giá</div>
                            <div class="text-center">Số lượng</div>
                            <div class="text-end">Thành tiền</div>
                            <div></div>
                        </div>

                        {{-- Rows --}}
                        @foreach($cart as $cartKey => $item)
                            @php $productId = $item['id'] ?? $cartKey; @endphp
                            <div class="cart-row" data-id="{{ $cartKey }}">
                                {{-- Checkbox --}}
                                <div>
                                    <input type="checkbox"
                                           name="selected[]"
                                           value="{{ $cartKey }}"
                                           class="form-check-input item-check"
                                           style="width:17px;height:17px;cursor:pointer;border-color:#cbd5e1;"
                                           checked>
                                </div>

                                {{-- Product --}}
                                <div class="cart-product-cell">
                                    @php
                                        $img = $item['image']
                                            ? asset('storage/' . $item['image'])
                                            : null;
                                    @endphp
                                    @if($img)
                                        <img src="{{ $img }}" alt="{{ $item['name'] }}" class="cart-product-img">
                                    @else
                                        <div class="cart-product-placeholder">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif
                                    <div style="min-width:0;">
                                        <a href="{{ route('user.products.show', $productId) }}" class="cart-product-name">
                                            {{ $item['name'] }}
                                        </a>
                                        @php
                                            $sizeLabel = $item['size_label'] ?? null;
                                            $color = $item['color'] ?? null;
                                            if (!$sizeLabel && !$color && str_contains($item['name'] ?? '', ' — ')) {
                                                $tail = trim(explode(' — ', $item['name'], 2)[1] ?? '');
                                                $parts = array_map('trim', explode('/', $tail));
                                                $sizeLabel = $parts[0] ?? null;
                                                $color = $parts[1] ?? null;
                                            }
                                        @endphp
                                        <div class="cart-product-meta">
                                            @if($sizeLabel)
                                                <span class="cart-meta-chip">
                                                    <i class="bi bi-rulers" style="font-size:0.7rem;"></i>
                                                    {{ $sizeLabel }}
                                                </span>
                                            @endif
                                            @if($color)
                                                <span class="cart-meta-chip">
                                                    <i class="bi bi-circle-fill" style="font-size:0.6rem;color:#64748b;"></i>
                                                    {{ $color }}
                                                </span>
                                            @endif
                                        </div>
                                        <a href="{{ route('user.products.show', $productId) }}" class="cart-change-link">
                                            <i class="bi bi-arrow-repeat" style="font-size:0.7rem;"></i>Đổi size / màu
                                        </a>
                                    </div>
                                </div>

                                {{-- Unit price --}}
                                <div class="cart-price-cell">
                                    {{ number_format($item['price'], 0, ',', '.') }}đ
                                </div>

                                {{-- Qty --}}
                                <div class="cart-qty-cell">
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn btn-qty" data-action="minus" aria-label="Giảm">−</button>
                                        <input type="number"
                                               class="qty-input"
                                               value="{{ $item['quantity'] }}"
                                               min="1" max="99"
                                               data-id="{{ $cartKey }}"
                                               aria-label="Số lượng">
                                        <button type="button" class="qty-btn btn-qty" data-action="plus" aria-label="Tăng">+</button>
                                    </div>
                                </div>

                                {{-- Subtotal --}}
                                <div class="cart-subtotal-cell item-subtotal">
                                    {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ
                                </div>

                                {{-- Remove --}}
                                <div>
                                    <button type="button"
                                            class="btn-remove-item"
                                            data-id="{{ $cartKey }}"
                                            title="Xóa sản phẩm">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: Order Summary --}}
                <div class="col-lg-4">
                    <div class="order-summary-card">
                        <div class="summary-title">
                            <i class="bi bi-receipt" style="color:#2563eb;"></i>
                            Tóm tắt đơn hàng
                        </div>

                        <div class="summary-row">
                            <span>Sản phẩm được chọn</span>
                            <strong><span id="selected-count">{{ count($cart) }}</span> sản phẩm</strong>
                        </div>
                        <div class="summary-row">
                            <span>Tạm tính</span>
                            <strong id="summary-subtotal">{{ number_format($total, 0, ',', '.') }}đ</strong>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển</span>
                            <strong style="color:#16a34a;">Miễn phí</strong>
                        </div>

                        {{-- Khối Ưu đãi / Voucher cho giỏ hàng --}}
                        <div class="cart-voucher-box">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold small text-dark d-flex align-items-center gap-1" style="font-size:0.8rem;">
                                    <i class="bi bi-ticket-perforated-fill text-primary"></i> Khuyến mãi & Voucher
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.68rem;">
                                    {{ isset($availablePromotions) ? $availablePromotions->count() : 0 }} mã khả dụng
                                </span>
                            </div>

                            <div style="max-height: 220px; overflow-y: auto; padding-right: 2px;" class="d-flex flex-column gap-1">
                                {{-- 1. Voucher có thể dùng --}}
                                @if(isset($availablePromotions) && $availablePromotions->count() > 0)
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
                                                    <span class="badge bg-success py-1 px-2" style="font-size:0.7rem;">
                                                        <i class="bi bi-check-lg"></i> Đã chọn
                                                    </span>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-primary py-1 px-2 btn-cart-apply-coupon" data-code="{{ $promo->code }}" style="font-size:0.72rem; font-weight:600;">
                                                        Áp dụng
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- 2. Voucher chưa đủ điều kiện (hiển thị ở dưới, không ấn add được) --}}
                                @if(isset($ineligiblePromotions) && $ineligiblePromotions->count() > 0)
                                    <div class="text-muted fw-semibold small mt-1" style="font-size:0.7rem;">
                                        <i class="bi bi-lock-fill me-1"></i>Chưa đủ điều kiện:
                                    </div>
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
                                                        <i class="bi bi-info-circle me-1"></i>Mua thêm {{ number_format($promo->need_more_amount, 0, ',', '.') }}đ để dùng
                                                    @else
                                                        <i class="bi bi-info-circle me-1"></i>{{ $promo->ineligible_reason ?? 'Chưa đủ điều kiện' }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="voucher-mini-right">
                                                {{-- Voucher ko dùng được: ko ấn add được --}}
                                                <button type="button" class="btn btn-sm btn-light border text-muted py-1 px-2 btn-voucher-disabled"
                                                        disabled style="font-size:0.72rem; cursor: not-allowed; opacity: 0.6;" title="Chưa đủ điều kiện">
                                                    Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <hr class="summary-divider">

                        <div class="summary-total-row">
                            <span class="summary-total-label">Tổng cộng</span>
                            <span class="summary-total-value" id="cart-total">{{ number_format($total, 0, ',', '.') }}đ</span>
                        </div>

                        <button type="submit" class="btn-checkout" id="btn-checkout">
                            <i class="bi bi-credit-card-2-front"></i> Thanh toán ngay
                        </button>

                        <a href="{{ route('user.home') }}" class="btn-continue">
                            <i class="bi bi-arrow-left"></i> Tiếp tục mua sắm
                        </a>

                        <div style="margin-top:1rem;padding-top:0.875rem;border-top:1px solid #f1f5f9;">
                            <div style="display:flex;gap:0.75rem;justify-content:center;">
                                <span style="font-size:0.72rem;color:#94a3b8;display:flex;align-items:center;gap:0.25rem;">
                                    <i class="bi bi-shield-check" style="color:#22c55e;"></i> Bảo mật thanh toán
                                </span>
                                <span style="font-size:0.72rem;color:#94a3b8;display:flex;align-items:center;gap:0.25rem;">
                                    <i class="bi bi-arrow-counterclockwise" style="color:#3b82f6;"></i> Đổi trả dễ dàng
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif

</div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;

    const checkAll   = document.getElementById('check-all');
    const itemChecks = document.querySelectorAll('.item-check');

    function recalcTotal() {
        const checked = document.querySelectorAll('.item-check:checked');
        const countEl   = document.getElementById('selected-count');
        const totalEl   = document.getElementById('cart-total');
        const subtotalEl = document.getElementById('summary-subtotal');

        let total = 0;
        checked.forEach(cb => {
            const row = cb.closest('.cart-row');
            const priceText = row.querySelector('.cart-price-cell').textContent.replace(/[^\d]/g, '');
            const qty = parseInt(row.querySelector('.qty-input').value) || 0;
            total += parseInt(priceText) * qty;
        });

        const fmt = total.toLocaleString('vi-VN') + 'đ';
        if (countEl) countEl.textContent = checked.length;
        if (totalEl) totalEl.textContent = fmt;
        if (subtotalEl) subtotalEl.textContent = fmt;

        const checkoutBtn = document.getElementById('btn-checkout');
        if (checkoutBtn) checkoutBtn.disabled = checked.length === 0;
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            itemChecks.forEach(cb => cb.checked = this.checked);
            recalcTotal();
        });
    }

    itemChecks.forEach(cb => {
        cb.addEventListener('change', function () {
            if (checkAll) {
                checkAll.checked = document.querySelectorAll('.item-check:checked').length === itemChecks.length;
            }
            recalcTotal();
        });
    });
    recalcTotal();

    // ── Quantity Controls ──
    document.querySelectorAll('.btn-qty').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.closest('.qty-control').querySelector('.qty-input');
            let val = parseInt(input.value) || 1;
            val = this.dataset.action === 'plus' ? Math.min(99, val + 1) : Math.max(1, val - 1);
            input.value = val;
            updateQty(input);
        });
    });

    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function () {
            this.value = Math.max(1, Math.min(99, parseInt(this.value) || 1));
            updateQty(this);
        });
    });

    function updateQty(input) {
        const cartKey = input.dataset.id;
        const qty = parseInt(input.value) || 1;

        fetch('{{ route("user.cart.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ cart_key: cartKey, quantity: qty }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const row = input.closest('.cart-row');
                const priceText = row.querySelector('.cart-price-cell').textContent.replace(/[^\d]/g, '');
                const subtotal = parseInt(priceText) * qty;
                row.querySelector('.item-subtotal').textContent = subtotal.toLocaleString('vi-VN') + 'đ';
                recalcTotal();
                if (window.updateCartBadge) window.updateCartBadge(data.cart_count);
            }
        });
    }

    // ── Remove Item ──
    document.querySelectorAll('.btn-remove-item').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Xóa sản phẩm này khỏi giỏ?')) return;
            const cartKey = this.dataset.id;
            const row = this.closest('.cart-row');
            row.classList.add('removing');

            fetch('{{ route("user.cart.remove") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ cart_key: cartKey }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    row.remove();
                    if (document.querySelectorAll('.cart-row').length === 0) {
                        location.reload();
                    } else {
                        recalcTotal();
                        if (window.updateCartBadge) window.updateCartBadge(data.cart_count);
                    }
                } else {
                    row.classList.remove('removing');
                }
            });
        });
    });

    // ── Checkout submit validation ──
    document.getElementById('checkout-form')?.addEventListener('submit', function (e) {
        if (document.querySelectorAll('.item-check:checked').length === 0) {
            e.preventDefault();
        }
    });

    // ── Apply Coupon from Cart ──
    document.querySelectorAll('.btn-cart-apply-coupon').forEach(btn => {
        btn.addEventListener('click', function () {
            const code = this.dataset.code;
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

            fetch('{{ route("user.coupon.apply") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ code: code }),
            })
            .then(r => r.json().then(data => ({ ok: r.ok, body: data })))
            .then(({ ok, body }) => {
                if (ok && body.success) {
                    if (window.showCartToast) {
                        window.showCartToast(body.message || 'Áp dụng mã thành công!');
                    }
                    setTimeout(() => location.reload(), 500);
                } else {
                    this.disabled = false;
                    this.innerHTML = originalText;
                    alert(body.message || 'Mã giảm giá không hợp lệ.');
                }
            })
            .catch(() => {
                this.disabled = false;
                this.innerHTML = originalText;
                alert('Không thể kết nối máy chủ.');
            });
        });
    });
});
</script>
@endpush
@endsection
