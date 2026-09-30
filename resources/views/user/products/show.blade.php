@extends('layouts.app')

@section('title', $product->name)

@section('content')
<style>
    :root {
        --pd-primary: #0f172a;
        --pd-accent: #dc2626;
        --pd-ocean: #0284c7;
        --pd-ocean-dark: #0369a1;
        --pd-ocean-soft: #e0f2fe;
        --pd-muted: #64748b;
        --pd-border: #e2e8f0;
        --pd-bg: #f1f5f9;
    }

    .pd-page { background: var(--pd-bg); min-height: 70vh; padding: 8px 0 48px; }

    .pd-breadcrumb { font-size: 0.84rem; color: var(--pd-muted); margin-bottom: 16px; }
    .pd-breadcrumb a { color: var(--pd-muted); text-decoration: none; }
    .pd-breadcrumb a:hover { color: var(--pd-ocean); }

    .pd-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 16px rgba(15, 23, 42, 0.05);
        border: 1px solid var(--pd-border);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .pd-gallery-wrap { padding: 24px 24px 16px; background: #fafbfc; }
    .pd-main-img {
        width: 100%; height: 380px; object-fit: contain;
        border-radius: 10px; background: #fff; display: block; margin: 0 auto;
    }
    .pd-no-img {
        height: 300px; display: flex; align-items: center; justify-content: center;
        color: var(--pd-muted); background: #f1f5f9; border-radius: 10px;
    }
    .pd-thumbs { display: flex; gap: 10px; margin-top: 14px; overflow-x: auto; padding-bottom: 6px; }
    .pd-thumb {
        flex: 0 0 88px; width: 88px; height: 72px; object-fit: cover;
        border-radius: 8px; border: 2px solid transparent; cursor: pointer;
        background: #fff; opacity: 0.85; transition: all 0.2s;
    }
    .pd-thumb:hover { opacity: 1; border-color: #94a3b8; }
    .pd-thumb.active { border-color: var(--pd-ocean); opacity: 1; box-shadow: 0 0 0 1px var(--pd-ocean); }

    .pd-info { padding: 28px 32px; }
    .pd-title {
        font-size: 1.5rem; font-weight: 700; color: var(--pd-primary);
        line-height: 1.35; margin-bottom: 6px;
    }
    .pd-sku {
        display: inline-block; font-size: 0.8rem; color: var(--pd-ocean-dark);
        background: var(--pd-ocean-soft); padding: 2px 10px; border-radius: 20px;
        margin-bottom: 18px; font-weight: 600;
    }

    .pd-attr { display: flex; padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.92rem; }
    .pd-attr:last-of-type { border-bottom: none; }
    .pd-attr-label { flex: 0 0 110px; color: var(--pd-ocean); font-weight: 600; }
    .pd-attr-value { flex: 1; color: #334155; line-height: 1.5; }

    .pd-color-chip {
        display: inline-block; background: var(--pd-ocean-soft); color: var(--pd-ocean-dark);
        font-size: 0.8rem; padding: 2px 10px; border-radius: 12px; margin: 1px 3px 1px 0;
        border: 1px solid #7dd3fc;
    }

    .pd-price-wrap { margin: 12px 0 22px; }
    .pd-discount {
        display: inline-block; background: var(--pd-accent); color: #fff;
        font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; margin-bottom: 4px;
    }
    .pd-price-current { font-size: 1.9rem; font-weight: 800; color: var(--pd-accent); letter-spacing: -0.5px; }
    .pd-price-old { font-size: 1.05rem; color: #94a3b8; text-decoration: line-through; margin-left: 10px; font-weight: 500; }

    .pd-size-label { font-size: 0.88rem; font-weight: 600; color: var(--pd-primary); margin-bottom: 10px; }
    .pd-sizes { display: flex; flex-wrap: wrap; gap: 8px; }
    .pd-size-btn {
        border: 1.5px solid #cbd5e1; background: #fff; color: #334155;
        padding: 8px 14px; border-radius: 8px; font-size: 0.84rem; font-weight: 500;
        cursor: pointer; transition: all 0.15s;
    }
    .pd-size-btn:hover:not(.disabled) { border-color: var(--pd-ocean); color: var(--pd-ocean); }
    .pd-size-btn.active {
        border-color: var(--pd-ocean); background: var(--pd-ocean); color: #fff; font-weight: 600;
    }
    .pd-size-btn.disabled {
        opacity: 0.35; cursor: not-allowed; pointer-events: none;
        text-decoration: line-through; background: #f8fafc; color: #94a3b8;
        border-color: #e2e8f0;
    }

    .pd-color-label {
        font-size: 0.95rem; font-weight: 600; color: var(--pd-primary);
        margin: 18px 0 12px; display: flex; align-items: baseline; gap: 6px;
    }
    .pd-color-label .pd-color-selected-code {
        color: var(--pd-ocean-dark); font-weight: 700;
    }
    .pd-colors {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(64px, 1fr));
        gap: 10px 8px;
        max-width: 100%;
    }
    .pd-color-btn {
        display: flex; flex-direction: column; align-items: center;
        border: none; background: transparent; padding: 0;
        cursor: pointer; transition: all 0.15s;
        width: 100%;
    }
    .pd-color-btn .pd-color-swatch {
        width: 56px; height: 56px; border-radius: 6px;
        border: 2px solid #e2e8f0;
        box-shadow: inset 0 0 0 1px rgba(0,0,0,.06);
        position: relative;
        transition: border-color .15s, box-shadow .15s;
        background-size: cover; background-position: center;
    }
    .pd-color-btn .pd-color-swatch.is-empty {
        background: repeating-conic-gradient(#eee 0% 25%, #fff 0% 50%) 50% / 10px 10px;
    }
    .pd-color-btn:hover:not(.disabled) .pd-color-swatch {
        border-color: #94a3b8;
    }
    .pd-color-btn.active .pd-color-swatch {
        border-color: #0f172a;
        box-shadow: 0 0 0 2px #0f172a, inset 0 0 0 1px rgba(255,255,255,.25);
    }
    .pd-color-btn.active .pd-color-swatch::after {
        content: '✓';
        position: absolute; right: 3px; bottom: 2px;
        width: 16px; height: 16px; border-radius: 50%;
        background: #0f172a; color: #fff;
        font-size: 10px; font-weight: 700; line-height: 16px; text-align: center;
    }
    .pd-color-btn.disabled {
        opacity: 0.35; cursor: not-allowed; pointer-events: none;
    }
    .pd-color-btn .pd-color-meta {
        margin-top: 4px; text-align: center; line-height: 1.2;
        font-size: 0.68rem; color: #64748b; width: 100%;
    }
    .pd-color-btn .pd-color-meta .pd-color-status {
        display: block; font-weight: 600; color: #475569;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .pd-color-btn .pd-color-meta .pd-color-stock {
        display: block; color: #94a3b8;
    }
    .pd-color-btn .pd-color-meta .pd-color-stock.is-out {
        color: #dc2626;
    }

    .pd-actions { margin-top: 26px; display: flex; gap: 10px; }
    .pd-btn-back {
        background: #fff; border: 1.5px solid #cbd5e1; color: var(--pd-primary);
        padding: 9px 18px; border-radius: 8px; font-size: 0.88rem; text-decoration: none; font-weight: 500;
    }
    .pd-btn-back:hover { background: #f8fafc; color: var(--pd-primary); }

    .pd-tabs {
        display: flex; gap: 0; border-bottom: 2px solid var(--pd-border); margin-bottom: 0;
    }
    .pd-tab {
        padding: 14px 24px; font-size: 0.95rem; font-weight: 600; color: var(--pd-muted);
        background: transparent; border: none; border-bottom: 2px solid transparent;
        margin-bottom: -2px; cursor: pointer; transition: all 0.2s;
    }
    .pd-tab:hover { color: var(--pd-ocean); }
    .pd-tab.active {
        color: var(--pd-ocean); border-bottom-color: var(--pd-ocean);
    }
    .pd-tab-content {
        padding: 24px 28px;
        font-size: 0.95rem; line-height: 1.8; color: #475569;
        display: none;
    }
    .pd-tab-content.active { display: block; }
    .pd-tab-content ul { padding-left: 1.2rem; margin-bottom: 0; }
    .pd-tab-content li { margin-bottom: 6px; }

    .pd-specs-title {
        font-size: 1.05rem; font-weight: 700; color: var(--pd-primary);
        padding: 18px 28px 0; margin: 0;
    }
    .pd-specs-table {
        width: 100%; border-collapse: collapse; margin: 12px 0 8px;
        font-size: 0.92rem;
    }
    .pd-specs-table th {
        text-align: left; width: 160px; padding: 10px 28px;
        color: var(--pd-muted); font-weight: 600; border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
    }
    .pd-specs-table td {
        padding: 10px 28px 10px 0; color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }
    .pd-specs-table tr:last-child th,
    .pd-specs-table tr:last-child td { border-bottom: none; }

    .pd-variant-table-wrap {
        padding: 8px 28px 24px;
    }
    .pd-variant-table-title {
        font-size: 0.95rem; font-weight: 600; color: var(--pd-primary);
        margin: 8px 0 12px;
    }
    .pd-variant-spec-table {
        width: 100%; max-width: 640px; border-collapse: collapse;
        font-size: 0.88rem; background: #fff;
    }
    .pd-variant-spec-table th {
        background: #f8fafc; color: #475569; font-weight: 600;
        text-align: left; padding: 8px 12px;
        border: 1px solid #e2e8f0; width: auto;
    }
    .pd-variant-spec-table td {
        padding: 8px 12px; border: 1px solid #e2e8f0; color: #334155;
    }
    .pd-variant-spec-table tr:nth-child(even) td { background: #fafbfc; }

    @media (max-width: 767px) {
        .pd-main-img { height: 260px; }
        .pd-info { padding: 20px 18px; }
        .pd-title { font-size: 1.25rem; }
        .pd-price-current { font-size: 1.5rem; }
        .pd-tab { padding: 12px 14px; font-size: 0.88rem; }
        .pd-specs-table th { width: 120px; padding: 10px 16px; }
        .pd-specs-table td { padding: 10px 16px 10px 0; }
        .pd-specs-title { padding: 16px 16px 0; }
    }

    /* ===== IMAGE ZOOM ===== */
    .pd-img-zoom-wrap {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        background: #fff;
        cursor: zoom-in;
        display: block;
    }
    .pd-img-zoom-wrap .pd-main-img {
        border-radius: 0;
        transition: transform 0.1s ease;
        display: block;
        width: 100%;
    }
    /* Zoom lens overlay */
    .pd-zoom-lens {
        display: none;
        position: absolute;
        border: 2px solid #0284c7;
        border-radius: 6px;
        width: 120px;
        height: 120px;
        background: rgba(2,132,199,0.08);
        pointer-events: none;
        z-index: 10;
        box-shadow: 0 0 0 1px rgba(2,132,199,0.3);
        transform: translate(-50%,-50%);
    }
    /* Zoom result panel bên phải ảnh */
    .pd-zoom-result {
        display: none;
        position: absolute;
        top: 0;
        left: calc(100% + 12px);
        width: 340px;
        height: 340px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 32px rgba(15,23,42,0.14);
        background-repeat: no-repeat;
        background-color: #fff;
        z-index: 200;
        overflow: hidden;
    }
    @media (max-width: 991px) {
        .pd-zoom-result { display: none !important; }
        .pd-zoom-lens { display: none !important; }
        .pd-img-zoom-wrap { cursor: zoom-in; }
    }

    /* ===== LIGHTBOX ===== */
    #pd-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.92);
        z-index: 9000;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    #pd-lightbox.open { display: flex; }
    #pd-lightbox-img {
        max-width: 90vw;
        max-height: 88vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 24px 64px rgba(0,0,0,0.5);
        display: block;
        animation: lb-in 0.2s ease;
    }
    @keyframes lb-in {
        from { opacity:0; transform: scale(0.94); }
        to   { opacity:1; transform: scale(1); }
    }
    #pd-lightbox-close {
        position: fixed;
        top: 18px;
        right: 22px;
        width: 40px; height: 40px;
        background: rgba(255,255,255,0.12);
        border: none;
        border-radius: 50%;
        color: #fff;
        font-size: 1.25rem;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.15s;
        z-index: 9001;
    }
    #pd-lightbox-close:hover { background: rgba(255,255,255,0.22); }
    #pd-lightbox-prev, #pd-lightbox-next {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        width: 44px; height: 44px;
        background: rgba(255,255,255,0.12);
        border: none; border-radius: 50%;
        color: #fff; font-size: 1.2rem;
        cursor: pointer; display: flex;
        align-items: center; justify-content: center;
        transition: background 0.15s;
        z-index: 9001;
    }
    #pd-lightbox-prev { left: 12px; }
    #pd-lightbox-next { right: 12px; }
    #pd-lightbox-prev:hover, #pd-lightbox-next:hover { background: rgba(255,255,255,0.25); }
    #pd-lightbox-counter {
        position: fixed;
        bottom: 18px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.82rem;
        color: rgba(255,255,255,0.6);
        background: rgba(0,0,0,0.4);
        padding: 3px 12px;
        border-radius: 20px;
        z-index: 9001;
    }
</style>

@php
    $gallery = $product->all_images;
    $firstVariant = $product->variants->first();
@endphp

<div class="pd-page">
    <div class="container">

        <div class="pd-breadcrumb">
            <a href="{{ route('user.home') }}">Trang chủ</a>
            <span class="mx-1">/</span>
            @if ($product->category)
                <a href="{{ route('user.home', ['category' => $product->category_id]) }}">{{ $product->category->name }}</a>
                <span class="mx-1">/</span>
            @endif
            <span>{{ $product->name }}</span>
        </div>

        <div class="pd-card">
            <div class="row g-0">
                <div class="col-md-6">
                    <div class="pd-gallery-wrap">
                        @if ($gallery->count())
                            {{-- Zoom wrap --}}
                            <div class="pd-img-zoom-wrap" id="pdZoomWrap" title="Di chuột để phóng to">
                                <img id="mainImage"
                                     src="{{ $gallery->first() }}"
                                     class="pd-main-img"
                                     alt="{{ $product->name }}"
                                     data-gallery='@json($gallery->values())'>
                                <div class="pd-zoom-lens" id="pdZoomLens"></div>
                                <div class="pd-zoom-result" id="pdZoomResult"></div>
                            </div>
                            @if ($gallery->count() > 1)
                                <div class="pd-thumbs">
                                    @foreach ($gallery as $i => $src)
                                        <img src="{{ $src }}"
                                             class="pd-thumb {{ $i === 0 ? 'active' : '' }}"
                                             data-src="{{ $src }}"
                                             alt="Ảnh {{ $i + 1 }}">
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="pd-no-img">
                                <span><i class="bi bi-image" style="font-size:2rem;"></i><br>Chưa có ảnh</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="pd-info">
                        <h1 class="pd-title">{{ $product->name }}</h1>

                        @if ($product->sku)
                            <span class="pd-sku">Mã: {{ $product->sku }}</span>
                        @endif

                        @if ($product->material)
                        <div class="pd-attr">
                            <span class="pd-attr-label">Chất liệu</span>
                            <span class="pd-attr-value">{{ $product->material }}</span>
                        </div>
                        @endif

                        <div class="pd-attr">
                            <span class="pd-attr-label">Kích thước</span>
                            <span class="pd-attr-value" id="attrDimensions">
                                @if ($product->variants->count())
                                    {{ $product->variants->map(fn($v) => $v->dimensions)->implode(', ') }}
                                @else — @endif
                            </span>
                        </div>

                        @if ($product->warranty)
                        <div class="pd-attr">
                            <span class="pd-attr-label">Bảo hành</span>
                            <span class="pd-attr-value">{{ $product->warranty }}</span>
                        </div>
                        @endif

                        @if ($product->style)
                        <div class="pd-attr">
                            <span class="pd-attr-label">Phong cách</span>
                            <span class="pd-attr-value">{{ $product->style }}</span>
                        </div>
                        @endif

                        <div class="pd-attr">
                            <span class="pd-attr-label">Màu sắc</span>
                            <span class="pd-attr-value">
                                @php $colors = $product->variants->pluck('color')->filter()->unique()->values(); @endphp
                                @if ($colors->count())
                                    @foreach ($colors as $c)
                                        <span class="pd-color-chip">{{ $c }}</span>
                                    @endforeach
                                @elseif ($product->color)
                                    <span class="pd-color-chip">{{ $product->color }}</span>
                                @else
                                    Đặt tùy chọn
                                @endif
                            </span>
                        </div>

                        @if ($product->category)
                        <div class="pd-attr">
                            <span class="pd-attr-label">Danh mục</span>
                            <span class="pd-attr-value">{{ $product->category->name }}</span>
                        </div>
                        @endif

                        {{-- Giá --}}
                        <div class="pd-price-wrap">
                            @if ($firstVariant)
                                @if ($firstVariant->price_old && $firstVariant->price_old > $firstVariant->price)
                                    @php $pct = round((1 - $firstVariant->price / $firstVariant->price_old) * 100); @endphp
                                    <div><span class="pd-discount" id="discountBadge">-{{ $pct }}%</span></div>
                                @else
                                    <div><span class="pd-discount d-none" id="discountBadge"></span></div>
                                @endif
                                <span class="pd-price-current" id="currentPrice">{{ number_format($firstVariant->price, 0, ',', '.') }}đ</span>
                                @if ($firstVariant->price_old && $firstVariant->price_old > $firstVariant->price)
                                    <span class="pd-price-old" id="oldPrice">{{ number_format($firstVariant->price_old, 0, ',', '.') }}đ</span>
                                @else
                                    <span class="pd-price-old d-none" id="oldPrice"></span>
                                @endif
                            @elseif ($product->price)
                                @if ($product->price_old && $product->price_old > $product->price)
                                    @php $pct = round((1 - $product->price / $product->price_old) * 100); @endphp
                                    <div><span class="pd-discount">-{{ $pct }}%</span></div>
                                @endif
                                <span class="pd-price-current">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                                @if ($product->price_old && $product->price_old > $product->price)
                                    <span class="pd-price-old">{{ number_format($product->price_old, 0, ',', '.') }}đ</span>
                                @endif
                            @else
                                <span class="pd-price-current">Liên hệ</span>
                            @endif
                        </div>

                        @if ($product->variants->count())
                            @php
                                // Nhóm theo kích thước (size_label hoặc dimensions)
                                $sizeGroups = [];
                                foreach ($product->variants as $v) {
                                    $sizeKey = $v->size_label ?: $v->dimensions ?: ('#' . $v->id);
                                    if (!isset($sizeGroups[$sizeKey])) {
                                        $sizeGroups[$sizeKey] = [
                                            'label' => $v->size_button_label ?: $sizeKey,
                                            'dimensions' => $v->dimensions,
                                            'variants' => [],
                                        ];
                                    }
                                    $sizeGroups[$sizeKey]['variants'][] = $v;
                                }
                                $colorRows = \App\Models\Color::query()->get();
                                $colorByName = $colorRows->keyBy('name');
                                $hexMap  = $colorRows->whereNotNull('hex')->pluck('hex', 'name');
                                $codeMap = $colorRows->pluck('code', 'name');
                                // Tổng tồn kho theo tên màu (mọi size)
                                $stockByColor = [];
                                foreach ($product->variants as $v) {
                                    if (!$v->color) continue;
                                    $stockByColor[$v->color] = ($stockByColor[$v->color] ?? 0) + (int) ($v->stock ?? 0);
                                }
                                $allColors = $product->variants->pluck('color')->filter()->unique()->values();
                                $firstSizeKey = array_key_first($sizeGroups);
                                $firstSizeVariants = $sizeGroups[$firstSizeKey]['variants'] ?? [];
                                $firstColor = optional($firstSizeVariants[0] ?? null)->color;
                            @endphp

                            <div class="pd-size-label">Chọn kích thước</div>
                            <div class="pd-sizes" id="pdSizes">
                                @foreach ($sizeGroups as $sizeKey => $group)
                                    <button type="button"
                                            class="pd-size-btn {{ $loop->first ? 'active' : '' }}"
                                            data-size-key="{{ $sizeKey }}"
                                            data-dimensions="{{ $group['dimensions'] }}">
                                        {{ $group['label'] }}
                                    </button>
                                @endforeach
                            </div>

                            @if ($allColors->count())
                                @php
                                    $initCode = $firstColor
                                        ? ($codeMap[$firstColor] ?? $firstColor)
                                        : ($codeMap[$allColors->first()] ?? $allColors->first());
                                @endphp
                                <div class="pd-color-label">
                                    Màu sắc:
                                    <span class="pd-color-selected-code" id="pdSelectedColorCode">{{ $initCode }}</span>
                                </div>
                                <div class="pd-colors" id="pdColors">
                                    @foreach ($allColors as $colorName)
                                        @php
                                            $colorModel = $colorByName[$colorName] ?? null;
                                            $hex   = $hexMap[$colorName] ?? null;
                                            $code  = $codeMap[$colorName] ?? $colorName;
                                            $stock = (int) ($stockByColor[$colorName] ?? 0);
                                            $status = $stock > 0 ? 'Còn hàng' : 'Đặt hàng';
                                            $swatchStyle = $colorModel ? $colorModel->swatch_style : ($hex ? 'background:'.$hex.';' : '');
                                            $hasSwatch = $colorModel && ($colorModel->image_url || $colorModel->hex);
                                        @endphp
                                        <button type="button"
                                                class="pd-color-btn"
                                                data-color="{{ $colorName }}"
                                                data-code="{{ $code }}"
                                                data-stock="{{ $stock }}"
                                                title="{{ $colorName }}{{ $hex ? ' ('.$hex.')' : '' }}">
                                            <span class="pd-color-swatch {{ $hasSwatch ? '' : 'is-empty' }}"
                                                  @if($swatchStyle) style="{{ $swatchStyle }}" @endif></span>
                                            <span class="pd-color-meta">
                                                <span class="pd-color-status">{{ $status }}</span>
                                                <span class="pd-color-stock {{ $stock <= 0 ? 'is-out' : '' }}">{{ $stock > 0 ? $stock : $code }}</span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Tồn kho: chữ nhỏ dưới chỗ màu sắc --}}
                            @php
                                $initStock = (int) (optional($firstSizeVariants[0] ?? null)->stock ?? 0);
                            @endphp
                            <div id="pd-stock-info" class="mt-2" style="font-size:0.82rem; color:#64748b;">
                                @if ($initStock > 0)
                                    Còn <strong id="pd-stock-num" style="color:#059669;">{{ $initStock }}</strong> sản phẩm
                                @else
                                    <span id="pd-stock-num" style="color:#dc2626;">Hết hàng</span>
                                @endif
                            </div>

                            @php
                                $variantsJson = $product->variants->map(function ($v) {
                                    return [
                                        'id' => $v->id,
                                        'size_key' => $v->size_label ?: $v->dimensions ?: ('#' . $v->id),
                                        'color' => $v->color,
                                        'price' => (float) $v->price,
                                        'price_old' => (float) ($v->price_old ?? 0),
                                        'dimensions' => $v->dimensions,
                                        'stock' => (int) ($v->stock ?? 0),
                                    ];
                                })->values();
                            @endphp
                            <script type="application/json" id="pdVariantsJson">{!! json_encode($variantsJson) !!}</script>
                        @else
                            {{-- Không có variant: vẫn hiện tồn kho nếu có --}}
                            <div id="pd-stock-info" class="mt-2" style="font-size:0.82rem; color:#64748b; display:none;"></div>
                        @endif

                        {{-- Actions: Qty + Add to cart --}}
                        <div class="pd-actions" style="margin-top:1.5rem;">
                            <div style="display:flex;gap:0.625rem;align-items:center;flex-wrap:wrap;">
                                {{-- Qty control --}}
                                <div style="display:inline-flex;align-items:center;border:1.5px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#fff;">
                                    <button type="button" id="qty-minus"
                                            style="width:38px;height:44px;border:none;background:transparent;font-size:1.15rem;color:#475569;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.12s;"
                                            onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">−</button>
                                    <input type="number" id="cart-qty" value="1" min="1" max="99"
                                           style="width:44px;height:44px;border:none;border-left:1px solid #f1f5f9;border-right:1px solid #f1f5f9;text-align:center;font-size:0.9rem;font-weight:700;color:#0f172a;background:transparent;outline:none;-moz-appearance:textfield;">
                                    <button type="button" id="qty-plus"
                                            style="width:38px;height:44px;border:none;background:transparent;font-size:1.15rem;color:#475569;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.12s;"
                                            onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">+</button>
                                </div>

                                {{-- Add to cart button --}}
                                <button type="button" id="btn-add-to-cart"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        style="flex:1;min-width:160px;height:44px;padding:0 1.5rem;
                                               background:linear-gradient(135deg,#2563eb,#7c3aed);
                                               color:#fff;border:none;border-radius:10px;
                                               font-size:0.9rem;font-weight:700;
                                               display:flex;align-items:center;justify-content:center;gap:0.5rem;
                                               cursor:pointer;transition:opacity 0.15s,transform 0.15s;
                                               letter-spacing:0.01em;"
                                        onmouseover="this.style.opacity='0.9';this.style.transform='translateY(-1px)';"
                                        onmouseout="this.style.opacity='1';this.style.transform='translateY(0)';"
                                        onfocus="this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.25)'"
                                        onblur="this.style.boxShadow='none'">
                                    <i class="bi bi-cart-plus"></i>
                                    Thêm vào giỏ
                                </button>
                            </div>

                            {{-- Back link --}}
                            <div style="margin-top:0.875rem;">
                                <a href="{{ route('user.home') }}"
                                   style="font-size:0.82rem;color:#64748b;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;font-weight:600;"
                                   onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#64748b'">
                                    <i class="bi bi-arrow-left" style="font-size:0.75rem;"></i> Quay lại cửa hàng
                                </a>
                            </div>

                            {{-- Trust badges --}}
                            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #f1f5f9;display:flex;gap:1rem;flex-wrap:wrap;">
                                <span style="font-size:0.75rem;color:#64748b;display:flex;align-items:center;gap:0.3rem;">
                                    <i class="bi bi-truck" style="color:#22c55e;"></i> Giao hàng miễn phí
                                </span>
                                <span style="font-size:0.75rem;color:#64748b;display:flex;align-items:center;gap:0.3rem;">
                                    <i class="bi bi-shield-check" style="color:#2563eb;"></i> Bảo hành chính hãng
                                </span>
                                <span style="font-size:0.75rem;color:#64748b;display:flex;align-items:center;gap:0.3rem;">
                                    <i class="bi bi-arrow-counterclockwise" style="color:#f59e0b;"></i> Đổi trả 30 ngày
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mô tả / Ưu điểm / Hướng dẫn --}}
        <div class="pd-card">
            <div class="pd-tabs">
                <button type="button" class="pd-tab active" data-tab="desc">Mô tả</button>
                <button type="button" class="pd-tab" data-tab="adv">Ưu điểm</button>
                <button type="button" class="pd-tab" data-tab="usage">Hướng dẫn sử dụng</button>
            </div>

            <div class="pd-tab-content active" id="tab-desc">
                @if ($product->description)
                    {!! nl2br(e($product->description)) !!}
                @else
                    <span class="text-muted">Chưa có mô tả.</span>
                @endif
            </div>

            <div class="pd-tab-content" id="tab-adv">
                @if ($product->advantages)
                    @php
                        $lines = preg_split('/\r\n|\r|\n/', $product->advantages);
                        $lines = array_filter(array_map('trim', $lines));
                    @endphp
                    @if (count($lines) > 1)
                        <ul>
                            @foreach ($lines as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ul>
                    @else
                        {!! nl2br(e($product->advantages)) !!}
                    @endif
                @else
                    <span class="text-muted">Chưa có thông tin ưu điểm.</span>
                @endif
            </div>

            <div class="pd-tab-content" id="tab-usage">
                @if ($product->usage_guide)
                    {!! nl2br(e($product->usage_guide)) !!}
                @else
                    <span class="text-muted">Chưa có hướng dẫn sử dụng.</span>
                @endif
            </div>
        </div>

        {{-- Thông số kỹ thuật --}}
        <div class="pd-card">
            <h3 class="pd-specs-title">Thông số kỹ thuật</h3>
            <table class="pd-specs-table">
                <tr>
                    <th>Tên sản phẩm</th>
                    <td>{{ $product->name }}</td>
                </tr>
                @if ($product->sku)
                <tr>
                    <th>Mã sản phẩm</th>
                    <td>{{ $product->sku }}</td>
                </tr>
                @endif
                @if ($product->category)
                <tr>
                    <th>Danh mục</th>
                    <td>{{ $product->category->name }}</td>
                </tr>
                @endif
                @if ($product->material)
                <tr>
                    <th>Chất liệu</th>
                    <td>{{ $product->material }}</td>
                </tr>
                @endif
                @if ($product->style)
                <tr>
                    <th>Phong cách</th>
                    <td>{{ $product->style }}</td>
                </tr>
                @endif
                @if ($product->warranty)
                <tr>
                    <th>Bảo hành</th>
                    <td>{{ $product->warranty }}</td>
                </tr>
                @endif
            </table>

            @if ($product->variants->count())
                @php
                    // Gộp theo kích thước — mỗi size 1 dòng (lấy giá đầu tiên / min)
                    $sizePriceRows = [];
                    foreach ($product->variants as $v) {
                        $sizeKey = $v->dimensions ?: ($v->size_label ?: '—');
                        if (!isset($sizePriceRows[$sizeKey])) {
                            $sizePriceRows[$sizeKey] = (float) $v->price;
                        } else {
                            $sizePriceRows[$sizeKey] = min($sizePriceRows[$sizeKey], (float) $v->price);
                        }
                    }
                @endphp
                <div class="pd-variant-table-wrap">
                    <h4 class="pd-variant-table-title">Bảng kích thước &amp; giá</h4>
                    <div style="overflow-x:auto;">
                        <table class="pd-variant-spec-table">
                            <thead>
                                <tr>
                                    <th>Kích thước</th>
                                    <th>Giá</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sizePriceRows as $sizeLabel => $price)
                                    <tr>
                                        <td>{{ $sizeLabel }}</td>
                                        <td><strong>{{ number_format($price, 0, ',', '.') }}đ</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

</div>
@endsection

{{-- ===== LIGHTBOX ===== --}}
@push('scripts')
<div id="pd-lightbox" role="dialog" aria-modal="true" aria-label="Xem ảnh lớn">
    <button id="pd-lightbox-close" title="Đóng (ESC)"><i class="bi bi-x-lg"></i></button>
    <button id="pd-lightbox-prev" title="Ảnh trước"><i class="bi bi-chevron-left"></i></button>
    <img id="pd-lightbox-img" src="" alt="Ảnh phóng to">
    <button id="pd-lightbox-next" title="Ảnh tiếp theo"><i class="bi bi-chevron-right"></i></button>
    <div id="pd-lightbox-counter"></div>
</div>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.pd-thumb').forEach(function (thumb) {
    thumb.addEventListener('click', function () {
        document.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const mainImg = document.getElementById('mainImage');
        mainImg.src = this.dataset.src;
        // Reset zoom result khi đổi ảnh
        const zoomResult = document.getElementById('pdZoomResult');
        if (zoomResult) zoomResult.style.backgroundImage = 'url(' + this.dataset.src + ')';
    });
});

// Biến global để add-to-cart lấy được variant đang chọn
window.pdSelectedVariantId = null;

(function () {
    const jsonEl = document.getElementById('pdVariantsJson');
    if (!jsonEl) return;
    let variants = [];
    try { variants = JSON.parse(jsonEl.textContent); } catch (e) { return; }

    let selectedSize = null;
    let selectedColor = null;

    function formatPrice(n) {
        return (n || 0).toLocaleString('vi-VN') + 'đ';
    }

    function findVariant() {
        return variants.find(function (v) {
            const sizeOk = selectedSize ? v.size_key === selectedSize : true;
            const colorOk = selectedColor
                ? (v.color === selectedColor)
                : true;
            return sizeOk && colorOk;
        }) || variants.find(function (v) {
            return selectedSize ? v.size_key === selectedSize : true;
        }) || variants[0];
    }

    function syncSelectedVariantId() {
        const v = findVariant();
        window.pdSelectedVariantId = v && v.id ? v.id : null;
    }

    function colorsForSize(sizeKey) {
        const set = {};
        variants.forEach(function (v) {
            if (v.size_key === sizeKey && v.color) set[v.color] = true;
        });
        return set;
    }

    function sizesForColor(colorName) {
        const set = {};
        variants.forEach(function (v) {
            if (v.color === colorName && v.size_key) set[v.size_key] = true;
        });
        return set;
    }

    function updatePrice(v) {
        if (!v) return;
        const price = v.price || 0;
        const priceOld = v.price_old || 0;
        const cur = document.getElementById('currentPrice');
        const oldEl = document.getElementById('oldPrice');
        const badge = document.getElementById('discountBadge');
        if (cur) cur.textContent = formatPrice(price);
        if (oldEl && badge) {
            if (priceOld > price) {
                oldEl.textContent = formatPrice(priceOld);
                oldEl.classList.remove('d-none');
                badge.textContent = '-' + Math.round((1 - price / priceOld) * 100) + '%';
                badge.classList.remove('d-none');
            } else {
                oldEl.classList.add('d-none');
                badge.classList.add('d-none');
            }
        }
        if (v.dimensions) {
            const dimEl = document.getElementById('attrDimensions');
            if (dimEl) dimEl.innerHTML = '<strong style="color:#0284c7">' + v.dimensions + '</strong>';
        }
        // Cập nhật tồn kho (chữ nhỏ dưới màu)
        updateStock(v.stock);
        syncSelectedVariantId();
    }

    function updateStock(stock) {
        const wrap = document.getElementById('pd-stock-info');
        if (!wrap) return;
        const n = parseInt(stock, 10) || 0;
        if (n > 0) {
            wrap.innerHTML = 'Còn <strong id="pd-stock-num" style="color:#059669;">' + n + '</strong> sản phẩm';
        } else {
            wrap.innerHTML = '<span id="pd-stock-num" style="color:#dc2626;">Hết hàng</span>';
        }
    }

    function refreshColorButtons() {
        const available = selectedSize ? colorsForSize(selectedSize) : {};
        const btns = document.querySelectorAll('.pd-color-btn');
        let firstEnabled = null;
        btns.forEach(function (btn) {
            const c = btn.dataset.color;
            const ok = !selectedSize || available[c];
            btn.classList.toggle('disabled', !ok);
            if (ok && !firstEnabled) firstEnabled = btn;
            if (!ok) btn.classList.remove('active');
        });
        // Nếu màu đang chọn không có cho size này → chọn màu đầu tiên còn
        if (selectedColor && selectedSize && !available[selectedColor]) {
            selectedColor = firstEnabled ? firstEnabled.dataset.color : null;
            btns.forEach(function (btn) {
                btn.classList.toggle('active', btn.dataset.color === selectedColor);
            });
            if (firstEnabled) updateSelectedColorLabel(firstEnabled);
        }
        if (!selectedColor && firstEnabled) {
            selectedColor = firstEnabled.dataset.color;
            firstEnabled.classList.add('active');
            updateSelectedColorLabel(firstEnabled);
        }
    }

    function refreshSizeButtons() {
        const available = selectedColor ? sizesForColor(selectedColor) : {};
        const btns = document.querySelectorAll('.pd-size-btn');
        let firstEnabled = null;
        btns.forEach(function (btn) {
            const sk = btn.dataset.sizeKey;
            // Nếu chưa chọn màu → tất cả size đều bật
            const ok = !selectedColor || available[sk];
            btn.classList.toggle('disabled', !ok);
            if (ok && !firstEnabled) firstEnabled = btn;
            if (!ok) btn.classList.remove('active');
        });
        // Nếu size đang chọn không có màu này → chuyển sang size đầu tiên còn
        if (selectedSize && selectedColor && !available[selectedSize]) {
            selectedSize = firstEnabled ? firstEnabled.dataset.sizeKey : null;
            btns.forEach(function (btn) {
                btn.classList.toggle('active', btn.dataset.sizeKey === selectedSize);
            });
        }
        if (!selectedSize && firstEnabled) {
            selectedSize = firstEnabled.dataset.sizeKey;
            firstEnabled.classList.add('active');
        }
    }

    document.querySelectorAll('.pd-size-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Size luôn chọn được — không khóa theo màu
            document.querySelectorAll('.pd-size-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedSize = this.dataset.sizeKey;
            // Sau khi chọn size → màu có size này đậm, không có thì nhạt
            refreshColorButtons();
            updatePrice(findVariant());
        });
    });

    function updateSelectedColorLabel(btn) {
        const el = document.getElementById('pdSelectedColorCode');
        if (!el || !btn) return;
        el.textContent = btn.dataset.code || btn.dataset.color || '';
    }

    document.querySelectorAll('.pd-color-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (this.classList.contains('disabled')) return;
            document.querySelectorAll('.pd-color-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedColor = this.dataset.color;
            updateSelectedColorLabel(this);
            // Không khóa size theo màu (ngược trang chủ: trang chủ chọn màu → lọc size)
            updatePrice(findVariant());
        });
    });

    // Init: chọn size đầu → chỉ làm đậm/nhạt màu theo size (không khóa size)
    document.querySelectorAll('.pd-size-btn').forEach(function (b) {
        b.classList.remove('disabled');
    });
    const firstSize = document.querySelector('.pd-size-btn.active') || document.querySelector('.pd-size-btn');
    if (firstSize) {
        firstSize.classList.add('active');
        selectedSize = firstSize.dataset.sizeKey;
    }
    refreshColorButtons();
    const firstColorBtn = document.querySelector('.pd-color-btn.active') || document.querySelector('.pd-color-btn:not(.disabled)');
    if (firstColorBtn) {
        document.querySelectorAll('.pd-color-btn').forEach(b => b.classList.remove('active'));
        firstColorBtn.classList.add('active');
        selectedColor = firstColorBtn.dataset.color;
        updateSelectedColorLabel(firstColorBtn);
    }
    updatePrice(findVariant());
})();

document.querySelectorAll('.pd-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.pd-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.pd-tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});

// ===== Thêm vào giỏ hàng =====
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const qtyInput = document.getElementById('cart-qty');
    const btnAdd = document.getElementById('btn-add-to-cart');
    if (!btnAdd) return;

    document.getElementById('qty-minus')?.addEventListener('click', function () {
        let v = parseInt(qtyInput.value) || 1;
        qtyInput.value = Math.max(1, v - 1);
    });
    document.getElementById('qty-plus')?.addEventListener('click', function () {
        let v = parseInt(qtyInput.value) || 1;
        qtyInput.value = Math.min(99, v + 1);
    });

    btnAdd.addEventListener('click', function () {
        const productId = this.dataset.id;
        const productName = this.dataset.name;
        const qty = Math.max(1, Math.min(99, parseInt(qtyInput?.value) || 1));
        const original = this.innerHTML;
        const variantId = window.pdSelectedVariantId || null;

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang thêm...';

        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', qty);
        formData.append('_token', csrf);
        if (variantId) {
            formData.append('variant_id', variantId);
        }

        fetch('{{ route("user.cart.add") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
            credentials: 'same-origin',
        })
        .then(async function (r) {
            let data = null;
            const text = await r.text();
            try { data = JSON.parse(text); } catch (e) { data = null; }

            if (r.status === 401 || r.status === 403) {
                throw new Error('Bạn cần đăng nhập và xác thực email trước.');
            }
            if (r.status === 419) {
                throw new Error('Phiên hết hạn (CSRF). Hãy tải lại trang.');
            }
            if (r.status === 422 && data) {
                throw new Error(data.message || Object.values(data.errors || {}).flat().join('\n') || 'Dữ liệu không hợp lệ');
            }
            if (!r.ok || !data || !data.success) {
                throw new Error((data && data.message) || ('Lỗi: ' + r.status));
            }
            return data;
        })
        .then(function (data) {
            if (typeof window.updateCartBadge === 'function') {
                window.updateCartBadge(data.cart_count);
            } else {
                const badge = document.getElementById('cart-count-badge');
                if (badge) {
                    badge.textContent = data.cart_count;
                    badge.style.display = 'inline-block';
                }
            }
            if (!window.showCartToast || !window.showCartToast(data.message || 'Thêm giỏ hàng thành công.')) {
                /* không dùng alert khi thành công */
            }
        })
        .catch(function (err) {
            if (!window.showCartToast || !window.showCartToast(err.message || 'Không thể thêm vào giỏ.', true)) {
                console.error(err);
            }
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = original;
        });
    });
})();

/* ===== ZOOM LENS (desktop only) ===== */
(function () {
    const wrap   = document.getElementById('pdZoomWrap');
    const img    = document.getElementById('mainImage');
    const lens   = document.getElementById('pdZoomLens');
    const result = document.getElementById('pdZoomResult');
    if (!wrap || !img || !lens || !result) return;

    const ZOOM = 2.5; // hệ số phóng to
    const LENS_W = 120, LENS_H = 120;

    function initZoom() {
        if (window.innerWidth < 992) return; // chỉ desktop
        const src = img.src;
        result.style.backgroundImage = `url('${src}')`;
        const rw = result.offsetWidth, rh = result.offsetHeight;
        const iw = img.naturalWidth  || img.offsetWidth  * ZOOM;
        const ih = img.naturalHeight || img.offsetHeight * ZOOM;
        result.style.backgroundSize = `${img.offsetWidth * ZOOM}px ${img.offsetHeight * ZOOM}px`;
    }

    wrap.addEventListener('mouseenter', function () {
        if (window.innerWidth < 992) return;
        initZoom();
        lens.style.display   = 'block';
        result.style.display = 'block';
    });

    wrap.addEventListener('mousemove', function (e) {
        if (window.innerWidth < 992) return;
        const rect = wrap.getBoundingClientRect();
        let x = e.clientX - rect.left;
        let y = e.clientY - rect.top;

        // Clamp lens inside wrap
        x = Math.max(LENS_W / 2, Math.min(rect.width  - LENS_W / 2, x));
        y = Math.max(LENS_H / 2, Math.min(rect.height - LENS_H / 2, y));

        lens.style.left = `${x}px`;
        lens.style.top  = `${y}px`;

        // Tỷ lệ zoom
        const ratioX = (img.offsetWidth  * ZOOM) / img.offsetWidth;
        const ratioY = (img.offsetHeight * ZOOM) / img.offsetHeight;

        const bgX = (x - LENS_W / 2) * ratioX;
        const bgY = (y - LENS_H / 2) * ratioY;

        result.style.backgroundPosition = `-${bgX}px -${bgY}px`;
        result.style.backgroundSize = `${img.offsetWidth * ZOOM}px ${img.offsetHeight * ZOOM}px`;
        result.style.backgroundImage = `url('${img.src}')`;
    });

    wrap.addEventListener('mouseleave', function () {
        lens.style.display   = 'none';
        result.style.display = 'none';
    });
})();

/* ===== LIGHTBOX ===== */
(function () {
    const lb        = document.getElementById('pd-lightbox');
    const lbImg     = document.getElementById('pd-lightbox-img');
    const lbClose   = document.getElementById('pd-lightbox-close');
    const lbPrev    = document.getElementById('pd-lightbox-prev');
    const lbNext    = document.getElementById('pd-lightbox-next');
    const lbCounter = document.getElementById('pd-lightbox-counter');
    if (!lb || !lbImg) return;

    let gallery = [];
    let current = 0;

    // Lấy danh sách ảnh từ mainImage data-gallery
    const mainImg = document.getElementById('mainImage');
    if (mainImg) {
        try { gallery = JSON.parse(mainImg.dataset.gallery || '[]'); } catch(e) {}
    }
    if (!gallery.length && mainImg) gallery = [mainImg.src];

    function openLightbox(idx) {
        current = Math.max(0, Math.min(gallery.length - 1, idx));
        lbImg.src = gallery[current];
        lb.classList.add('open');
        document.body.style.overflow = 'hidden';
        updateCounter();
        // Ẩn prev/next nếu chỉ có 1 ảnh
        if (lbPrev) lbPrev.style.display = gallery.length <= 1 ? 'none' : '';
        if (lbNext) lbNext.style.display = gallery.length <= 1 ? 'none' : '';
    }

    function closeLightbox() {
        lb.classList.remove('open');
        document.body.style.overflow = '';
    }

    function updateCounter() {
        if (lbCounter) lbCounter.textContent = `${current + 1} / ${gallery.length}`;
    }

    // Click vào wrap → mở lightbox
    const wrap = document.getElementById('pdZoomWrap');
    if (wrap) {
        wrap.addEventListener('click', function () {
            const src = document.getElementById('mainImage').src;
            const idx = gallery.indexOf(src);
            openLightbox(idx >= 0 ? idx : 0);
        });
    }

    // Thumb click → cập nhật gallery index
    document.querySelectorAll('.pd-thumb').forEach(function (t, i) {
        t.addEventListener('click', function () { current = i; });
    });

    if (lbClose) lbClose.addEventListener('click', closeLightbox);

    lb.addEventListener('click', function (e) {
        if (e.target === lb) closeLightbox();
    });

    if (lbPrev) lbPrev.addEventListener('click', function (e) {
        e.stopPropagation();
        current = (current - 1 + gallery.length) % gallery.length;
        lbImg.src = gallery[current];
        updateCounter();
    });

    if (lbNext) lbNext.addEventListener('click', function (e) {
        e.stopPropagation();
        current = (current + 1) % gallery.length;
        lbImg.src = gallery[current];
        updateCounter();
    });

    // ESC
    document.addEventListener('keydown', function (e) {
        if (!lb.classList.contains('open')) return;
        if (e.key === 'Escape')     closeLightbox();
        if (e.key === 'ArrowLeft')  lbPrev && lbPrev.click();
        if (e.key === 'ArrowRight') lbNext && lbNext.click();
    });

    // Touch swipe (mobile)
    let touchX = null;
    lb.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend',   e => {
        if (touchX === null) return;
        const dx = e.changedTouches[0].clientX - touchX;
        if (Math.abs(dx) > 50) { dx < 0 ? lbNext && lbNext.click() : lbPrev && lbPrev.click(); }
        touchX = null;
    });
})();
</script>
@endpush
