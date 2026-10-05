@extends('layouts.app')

@section('title', $product->name)

@section('content')
<style>
    :root {
        --pd-primary: #3A2E26;
        --pd-accent: #5A4536;
        --pd-ocean: #5A4536;
        --pd-ocean-dark: #3F2F24;
        --pd-ocean-soft: #F3E9DC;
        --pd-muted: #7E7065;
        --pd-border: #E6D8C8;
        --pd-bg: #FAF6F0;
    }

    .pd-page { background: var(--pd-bg); min-height: 70vh; padding: 20px 0 60px; font-family: 'Manrope', sans-serif; color: var(--pd-primary); }

    .pd-breadcrumb { font-size: 0.82rem; color: var(--pd-muted); margin-bottom: 20px; }
    .pd-breadcrumb a { color: var(--pd-muted); text-decoration: none; transition: color 0.15s; }
    .pd-breadcrumb a:hover { color: var(--pd-ocean); }

    .pd-card {
        background: #fff;
        border-radius: 2px;
        box-shadow: 0 4px 20px rgba(90, 69, 54, 0.04);
        border: 1px solid var(--pd-border);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .pd-gallery-wrap { padding: 24px; background: #fff; border-right: 1px solid var(--pd-border); }
    .pd-main-img {
        width: 100%; height: 380px; object-fit: contain;
        border-radius: 2px; background: #fff; display: block; margin: 0 auto;
    }
    .pd-no-img {
        height: 300px; display: flex; align-items: center; justify-content: center;
        color: var(--pd-muted); background: var(--pd-bg); border-radius: 2px; border: 1px dashed var(--pd-border);
    }
    .pd-thumbs { display: flex; gap: 10px; margin-top: 14px; overflow-x: auto; padding-bottom: 6px; }
    .pd-thumb {
        flex: 0 0 84px; width: 84px; height: 68px; object-fit: cover;
        border-radius: 2px; border: 1px solid var(--pd-border); cursor: pointer;
        background: #fff; opacity: 0.8; transition: all 0.2s;
    }
    .pd-thumb:hover { opacity: 1; border-color: var(--pd-muted); }
    .pd-thumb.active { border-color: var(--pd-ocean); opacity: 1; box-shadow: 0 0 0 1px var(--pd-ocean); }

    .pd-info { padding: 32px 36px; }
    .pd-title {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 2rem; font-weight: 600; color: var(--pd-primary);
        line-height: 1.3; margin-bottom: 8px; letter-spacing: -0.01em;
    }
    .pd-sku {
        display: inline-block; font-size: 0.75rem; color: var(--pd-ocean);
        background: var(--pd-ocean-soft); padding: 3px 10px; border-radius: 2px;
        margin-bottom: 18px; font-weight: 600; border: 1px solid var(--pd-border);
        letter-spacing: 0.04em; text-transform: uppercase;
    }

    .pd-attr { display: flex; padding: 10px 0; border-bottom: 1px solid #FAF6F0; font-size: 0.88rem; }
    .pd-attr:last-of-type { border-bottom: none; }
    .pd-attr-label { flex: 0 0 110px; color: var(--pd-muted); font-weight: 500; }
    .pd-attr-value { flex: 1; color: var(--pd-primary); line-height: 1.5; }

    .pd-color-chip {
        display: inline-block; background: var(--pd-ocean-soft); color: var(--pd-ocean);
        font-size: 0.78rem; padding: 2px 10px; border-radius: 2px; margin: 1px 3px 1px 0;
        border: 1px solid var(--pd-border); font-weight: 500;
    }

    .pd-price-wrap { margin: 14px 0 24px; padding: 14px 0; border-top: 1px solid #FAF6F0; border-bottom: 1px solid #FAF6F0; }
    .pd-discount {
        display: inline-block; background: #5A4536; color: #FAF6F0;
        font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 2px; margin-bottom: 6px;
        letter-spacing: 0.04em;
    }
    .pd-price-current {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 2.2rem; font-weight: 700; color: var(--pd-accent); letter-spacing: -0.5px;
    }
    .pd-price-old { font-size: 1.05rem; color: #A69282; text-decoration: line-through; margin-left: 10px; font-weight: 400; font-family: 'Manrope', sans-serif; }

    .pd-size-label { font-size: 0.84rem; font-weight: 600; color: var(--pd-primary); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.03em; }
    .pd-sizes { display: flex; flex-wrap: wrap; gap: 8px; }
    .pd-size-btn {
        border: 1px solid var(--pd-border); background: #fff; color: var(--pd-primary);
        padding: 8px 16px; border-radius: 2px; font-size: 0.82rem; font-weight: 500;
        cursor: pointer; transition: all 0.15s; font-family: 'Manrope', sans-serif;
    }
    .pd-size-btn:hover:not(.disabled) { border-color: var(--pd-ocean); color: var(--pd-ocean); }
    .pd-size-btn.active {
        border-color: var(--pd-ocean); background: var(--pd-ocean); color: #fff; font-weight: 600;
    }
    .pd-size-btn.disabled {
        opacity: 0.35; cursor: not-allowed; pointer-events: none;
        text-decoration: line-through; background: #FAF6F0; color: #A69282;
        border-color: var(--pd-border);
    }

    .pd-color-label {
        font-size: 0.84rem; font-weight: 600; color: var(--pd-primary);
        margin: 20px 0 12px; display: flex; align-items: baseline; gap: 6px;
        text-transform: uppercase; letter-spacing: 0.03em;
    }
    .pd-color-label .pd-color-selected-code {
        color: var(--pd-ocean); font-weight: 700;
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
        width: 52px; height: 52px; border-radius: 2px;
        border: 1px solid var(--pd-border);
        box-shadow: inset 0 0 0 1px rgba(0,0,0,.04);
        position: relative;
        transition: border-color .15s, box-shadow .15s;
        background-size: cover; background-position: center;
    }
    .pd-color-btn .pd-color-swatch.is-empty {
        background: repeating-conic-gradient(#FAF6F0 0% 25%, #fff 0% 50%) 50% / 10px 10px;
    }
    .pd-color-btn:hover:not(.disabled) .pd-color-swatch {
        border-color: var(--pd-muted);
    }
    .pd-color-btn.active .pd-color-swatch {
        border-color: var(--pd-ocean);
        box-shadow: 0 0 0 2px var(--pd-ocean), inset 0 0 0 1px rgba(255,255,255,.3);
    }
    .pd-color-btn.active .pd-color-swatch::after {
        content: '✓';
        position: absolute; right: 3px; bottom: 2px;
        width: 15px; height: 15px; border-radius: 50%;
        background: var(--pd-ocean); color: #fff;
        font-size: 9px; font-weight: 700; line-height: 15px; text-align: center;
    }
    .pd-color-btn.disabled {
        opacity: 0.35; cursor: not-allowed; pointer-events: none;
    }
    .pd-color-btn .pd-color-meta {
        margin-top: 4px; text-align: center; line-height: 1.2;
        font-size: 0.68rem; color: var(--pd-muted); width: 100%;
    }
    .pd-color-btn .pd-color-meta .pd-color-status {
        display: block; font-weight: 600; color: var(--pd-primary);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .pd-color-btn .pd-color-meta .pd-color-stock {
        display: block; color: var(--pd-muted);
    }
    .pd-color-btn .pd-color-meta .pd-color-stock.is-out {
        color: #A65236;
    }

    .pd-actions { margin-top: 26px; }

    .pd-tabs {
        display: flex; gap: 0; border-bottom: 1px solid var(--pd-border); margin-bottom: 0;
        background: #fff;
    }
    .pd-tab {
        padding: 16px 28px; font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.15rem; font-weight: 600; color: var(--pd-muted);
        background: transparent; border: none; border-bottom: 2px solid transparent;
        margin-bottom: -1px; cursor: pointer; transition: all 0.2s;
    }
    .pd-tab:hover { color: var(--pd-ocean); }
    .pd-tab.active {
        color: var(--pd-ocean); border-bottom-color: var(--pd-ocean);
    }
    .pd-tab-content {
        padding: 28px 32px;
        font-size: 0.92rem; line-height: 1.85; color: #53453a;
        display: none;
    }
    .pd-tab-content.active { display: block; }
    .pd-tab-content ul { padding-left: 1.2rem; margin-bottom: 0; }
    .pd-tab-content li { margin-bottom: 6px; }

    .pd-specs-title {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.35rem; font-weight: 600; color: var(--pd-primary);
        padding: 24px 32px 10px; margin: 0;
    }
    .pd-specs-table {
        width: 100%; border-collapse: collapse; margin: 12px 0 8px;
        font-size: 0.88rem;
    }
    .pd-specs-table th {
        text-align: left; width: 170px; padding: 12px 32px;
        color: var(--pd-muted); font-weight: 600; border-bottom: 1px solid #FAF6F0;
        vertical-align: top;
    }
    .pd-specs-table td {
        padding: 12px 32px 12px 0; color: var(--pd-primary);
        border-bottom: 1px solid #FAF6F0;
    }
    .pd-specs-table tr:last-child th,
    .pd-specs-table tr:last-child td { border-bottom: none; }

    .pd-variant-table-wrap {
        padding: 12px 32px 28px;
    }
    .pd-variant-table-title {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.15rem; font-weight: 600; color: var(--pd-primary);
        margin: 8px 0 14px;
    }
    .pd-variant-spec-table {
        width: 100%; max-width: 640px; border-collapse: collapse;
        font-size: 0.85rem; background: #fff; border: 1px solid var(--pd-border);
    }
    .pd-variant-spec-table th {
        background: #FAF6F0; color: var(--pd-primary); font-weight: 600;
        text-align: left; padding: 10px 14px;
        border: 1px solid var(--pd-border); width: auto;
    }
    .pd-variant-spec-table td {
        padding: 10px 14px; border: 1px solid var(--pd-border); color: var(--pd-primary);
    }
    .pd-variant-spec-table tr:nth-child(even) td { background: #FCFAF7; }

    @media (max-width: 767px) {
        .pd-gallery-wrap { border-right: none; border-bottom: 1px solid var(--pd-border); padding: 16px; }
        .pd-main-img { height: 260px; }
        .pd-info { padding: 22px 18px; }
        .pd-title { font-size: 1.5rem; }
        .pd-price-current { font-size: 1.8rem; }
        .pd-tab { padding: 12px 16px; font-size: 1rem; }
        .pd-specs-table th { width: 120px; padding: 10px 16px; }
        .pd-specs-table td { padding: 10px 16px 10px 0; }
        .pd-specs-title { padding: 16px 16px 0; }
        .pd-variant-table-wrap { padding: 12px 16px 20px; }
    }

    /* ===== PRODUCT IMAGE INNER ZOOM (Auto zoom on hover) ===== */
    .pd-img-zoom-wrap {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        background: #fff;
        cursor: crosshair;
        display: block;
        width: 100%;
        height: 400px;
        border: 1px solid #eef2f6;
        user-select: none;
    }
    .pd-img-zoom-wrap .pd-main-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        margin: 0 auto;
        border-radius: 0;
        transform-origin: 0 0;
        transform: translate3d(0, 0, 0) scale(1);
        pointer-events: none;
        will-change: transform;
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    }
    /* Nút xem toàn màn hình (gọn gàng góc trên phải) */
    .pd-fullscreen-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid rgba(0,0,0,0.08);
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(4px);
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.95rem;
        z-index: 10;
        transition: all 0.2s ease;
        opacity: 0.7;
    }
    .pd-fullscreen-btn:hover {
        opacity: 1;
        background: #fff;
        color: #0284c7;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        transform: scale(1.05);
    }
    @media (max-width: 767px) {
        .pd-img-zoom-wrap {
            height: 280px;
            cursor: default;
        }
        .pd-fullscreen-btn {
            opacity: 1;
        }
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
                            {{-- Zoom wrap: Tự động phóng to khi di chuyển chuột qua bất kỳ vùng nào của ảnh --}}
                            <div class="pd-img-zoom-wrap" id="pdZoomWrap" title="Di chuột để phóng to chi tiết">
                                <img id="mainImage"
                                     src="{{ $gallery->first() }}"
                                     class="pd-main-img"
                                     alt="{{ $product->name }}"
                                     data-gallery='@json($gallery->values())'
                                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1538688525198-9b88f6f53126?auto=format&fit=crop&w=800&q=80';">
                                <button type="button" class="pd-fullscreen-btn" id="pdFullscreenBtn" title="Xem ảnh toàn màn hình" aria-label="Xem ảnh toàn màn hình">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                            </div>
                            @if ($gallery->count() > 1)
                                <div class="pd-thumbs">
                                    @foreach ($gallery as $i => $src)
                                        <img src="{{ $src }}"
                                             class="pd-thumb {{ $i === 0 ? 'active' : '' }}"
                                             data-src="{{ $src }}"
                                             alt="Ảnh {{ $i + 1 }}"
                                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1538688525198-9b88f6f53126?auto=format&fit=crop&w=200&q=80';">
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
                            <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
                                {{-- Qty control --}}
                                <div style="display:inline-flex;align-items:center;border:1px solid #E6D8C8;border-radius:2px;overflow:hidden;background:#fff;">
                                    <button type="button" id="qty-minus"
                                            style="width:40px;height:44px;border:none;background:transparent;font-size:1.1rem;color:#5A4536;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.12s;"
                                            onmouseover="this.style.background='#FAF6F0'" onmouseout="this.style.background='transparent'">−</button>
                                    <input type="number" id="cart-qty" value="1" min="1" max="99"
                                           style="width:48px;height:44px;border:none;border-left:1px solid #E6D8C8;border-right:1px solid #E6D8C8;text-align:center;font-size:0.92rem;font-weight:700;color:#3A2E26;background:transparent;outline:none;-moz-appearance:textfield;">
                                    <button type="button" id="qty-plus"
                                            style="width:40px;height:44px;border:none;background:transparent;font-size:1.1rem;color:#5A4536;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.12s;"
                                            onmouseover="this.style.background='#FAF6F0'" onmouseout="this.style.background='transparent'">+</button>
                                </div>

                                {{-- Add to cart button --}}
                                <button type="button" id="btn-add-to-cart"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        style="flex:1;min-width:180px;height:44px;padding:0 1.5rem;
                                               background:#5A4536;
                                               color:#FAF6F0;border:none;border-radius:2px;
                                               font-size:0.85rem;font-weight:600;font-family:'Manrope',sans-serif;
                                               display:flex;align-items:center;justify-content:center;gap:0.5rem;
                                               cursor:pointer;transition:background 0.2s,transform 0.15s;
                                               letter-spacing:0.04em;text-transform:uppercase;"
                                        onmouseover="this.style.background='#3F2F24';this.style.transform='translateY(-1px)';"
                                        onmouseout="this.style.background='#5A4536';this.style.transform='translateY(0)';"
                                        onfocus="this.style.boxShadow='0 0 0 3px rgba(90,69,54,0.2)'"
                                        onblur="this.style.boxShadow='none'">
                                    <i class="bi bi-bag-plus"></i>
                                    Thêm vào giỏ
                                </button>
                            </div>

                            {{-- Back link --}}
                            <div style="margin-top:1rem;">
                                <a href="{{ route('user.home') }}"
                                   style="font-size:0.82rem;color:#7E7065;text-decoration:none;display:inline-flex;align-items:center;gap:0.4rem;font-weight:500;"
                                   onmouseover="this.style.color='#5A4536'" onmouseout="this.style.color='#7E7065'">
                                    <i class="bi bi-arrow-left" style="font-size:0.8rem;"></i> Quay lại danh mục sản phẩm
                                </a>
                            </div>

                            {{-- Trust badges --}}
                            <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #FAF6F0;display:flex;gap:1.25rem;flex-wrap:wrap;">
                                <span style="font-size:0.75rem;color:#7E7065;display:flex;align-items:center;gap:0.4rem;">
                                    <i class="bi bi-truck" style="color:#5A4536;"></i> Vận chuyển tận nơi
                                </span>
                                <span style="font-size:0.75rem;color:#7E7065;display:flex;align-items:center;gap:0.4rem;">
                                    <i class="bi bi-shield-check" style="color:#5A4536;"></i> Bảo hành chính hãng
                                </span>
                                <span style="font-size:0.75rem;color:#7E7065;display:flex;align-items:center;gap:0.4rem;">
                                    <i class="bi bi-gem" style="color:#C29D62;"></i> Gỗ tuyển chọn thủ công
                                </span>
                            </div>
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

        {{-- ===== ĐÁNH GIÁ & TRẢI NGHIỆM TỪ KHÁCH HÀNG ===== --}}
        <div class="pd-card p-4 mt-4" id="danh-gia">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom pb-3 mb-4">
                <div>
                    <h3 class="font-serif mb-1" style="font-size: 1.6rem; color: var(--pd-primary);">
                        <i class="bi bi-star-fill text-warning me-2"></i>Đánh giá từ khách hàng
                    </h3>
                    <p class="text-muted small mb-0">Nhận xét thực tế từ khách hàng đã mua và sử dụng sản phẩm</p>
                </div>
                <div class="d-flex align-items-center gap-3 bg-light px-3 py-2 rounded border">
                    <div class="text-center">
                        <div class="fs-3 fw-bold font-serif text-dark" style="line-height: 1;">
                            {{ $product->reviews->isNotEmpty() ? number_format($product->reviews->avg('rating'), 1) : '5.0' }}
                        </div>
                        <div class="text-warning small">
                            @php
                                $avgR = round($product->reviews->isNotEmpty() ? $product->reviews->avg('rating') : 5);
                            @endphp
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $avgR ? '-fill text-warning' : ' text-muted' }}"></i>
                            @endfor
                        </div>
                    </div>
                    <div class="border-start ps-3 text-muted small">
                        <strong>{{ $product->reviews->count() }}</strong> lượt đánh giá
                    </div>
                </div>
            </div>

            @if ($product->reviews->isNotEmpty())
                <div class="d-flex flex-column gap-3">
                    @foreach ($product->reviews as $rev)
                        <div class="p-3 bg-light rounded border" style="border-color: #E6D8C8 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    @if (!empty($rev->user?->avatar))
                                        <img src="{{ $rev->user->avatar }}" alt="{{ $rev->user->name }}" class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                            {{ strtoupper(mb_substr($rev->user?->name ?? 'K', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.92rem;">
                                            {{ $rev->user?->name ?? 'Khách hàng đã mua' }}
                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.68rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>Đã mua hàng
                                            </span>
                                        </div>
                                        <div class="text-warning small" style="font-size: 0.78rem;">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="bi bi-star{{ $i <= $rev->rating ? '-fill text-warning' : ' text-muted' }}"></i>
                                            @endfor
                                            <span class="text-muted ms-1">{{ $rev->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="mb-2 text-dark" style="white-space: pre-line; line-height: 1.6; font-size: 0.95rem;">
                                {{ $rev->comment }}
                            </p>
                            @include('components.review-reply')

                            @if (!empty($rev->images) && is_array($rev->images))
                                <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                                    @foreach ($rev->images as $imgUrl)
                                        <a href="{{ $imgUrl }}" target="_blank" rel="noopener noreferrer" class="d-inline-block rounded overflow-hidden shadow-sm border" style="width: 72px; height: 72px;">
                                            <img src="{{ $imgUrl }}" alt="Ảnh đánh giá" style="width: 100%; height: 100%; object-fit: cover;">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-chat-square-text fs-2 d-block mb-2 text-secondary opacity-50"></i>
                    Sản phẩm này hiện chưa có đánh giá nào. Khách hàng đã hoàn thành đơn có thể để lại nhận xét tại trang chi tiết đơn hàng!
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
        if (mainImg) {
            mainImg.src = this.dataset.src;
            mainImg.style.transition = 'none';
            mainImg.style.transform = 'translate3d(0, 0, 0) scale(1)';
        }
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

/* ===== AUTO ZOOM TRỰC TIẾP TRÊN ẢNH KHI DI CHUỘT (Desktop) ===== */
(function () {
    const wrap = document.getElementById('pdZoomWrap');
    const img  = document.getElementById('mainImage');
    if (!wrap || !img) return;

    const SCALE = 2.4; // Tỉ lệ zoom phóng to
    let isHovered = false;
    let targetX = 0, targetY = 0;
    let currentX = 0, currentY = 0;
    let animId = null;

    function renderZoom() {
        if (!isHovered) return;

        // Nội suy mượt mà (Lerp) 0.18 giúp hình ảnh lướt êm ái theo con trỏ chuột
        currentX += (targetX - currentX) * 0.18;
        currentY += (targetY - currentY) * 0.18;

        const tx = -currentX * (SCALE - 1);
        const ty = -currentY * (SCALE - 1);

        img.style.transform = `translate3d(${tx.toFixed(2)}px, ${ty.toFixed(2)}px, 0) scale(${SCALE})`;
        animId = requestAnimationFrame(renderZoom);
    }

    wrap.addEventListener('mouseenter', function (e) {
        // Chỉ kích hoạt tự động zoom trên màn hình desktop có chuột
        if (window.innerWidth < 992 || window.matchMedia('(pointer: coarse)').matches) return;

        isHovered = true;
        const rect = wrap.getBoundingClientRect();
        targetX = Math.max(0, Math.min(rect.width, e.clientX - rect.left));
        targetY = Math.max(0, Math.min(rect.height, e.clientY - rect.top));
        currentX = targetX;
        currentY = targetY;

        // Phóng to êm ái từ vị trí con trỏ chuột bước vào
        img.style.transition = 'transform 0.22s cubic-bezier(0.16, 1, 0.3, 1)';
        const tx = -currentX * (SCALE - 1);
        const ty = -currentY * (SCALE - 1);
        img.style.transform = `translate3d(${tx.toFixed(2)}px, ${ty.toFixed(2)}px, 0) scale(${SCALE})`;

        setTimeout(() => {
            if (isHovered) {
                img.style.transition = 'none';
                if (!animId) animId = requestAnimationFrame(renderZoom);
            }
        }, 220);
    });

    wrap.addEventListener('mousemove', function (e) {
        if (!isHovered) return;
        const rect = wrap.getBoundingClientRect();
        targetX = Math.max(0, Math.min(rect.width, e.clientX - rect.left));
        targetY = Math.max(0, Math.min(rect.height, e.clientY - rect.top));
        if (!animId) {
            animId = requestAnimationFrame(renderZoom);
        }
    });

    wrap.addEventListener('mouseleave', function () {
        if (!isHovered) return;
        isHovered = false;
        if (animId) {
            cancelAnimationFrame(animId);
            animId = null;
        }
        // Tự động thu nhỏ mượt mà về kích thước ban đầu
        img.style.transition = 'transform 0.28s cubic-bezier(0.16, 1, 0.3, 1)';
        img.style.transform = 'translate3d(0, 0, 0) scale(1)';
    });
})();

/* ===== LIGHTBOX (Khi nhấn nút Fullscreen hoặc chạm trên mobile) ===== */
(function () {
    const lb        = document.getElementById('pd-lightbox');
    const lbImg     = document.getElementById('pd-lightbox-img');
    const lbClose   = document.getElementById('pd-lightbox-close');
    const lbPrev    = document.getElementById('pd-lightbox-prev');
    const lbNext    = document.getElementById('pd-lightbox-next');
    const lbCounter = document.getElementById('pd-lightbox-counter');
    const fsBtn     = document.getElementById('pdFullscreenBtn');
    const wrap      = document.getElementById('pdZoomWrap');
    if (!lb || !lbImg) return;

    let gallery = [];
    let current = 0;

    function getGallery() {
        const mainImg = document.getElementById('mainImage');
        if (mainImg) {
            try { gallery = JSON.parse(mainImg.dataset.gallery || '[]'); } catch(e) {}
            if (!gallery.length && mainImg.src) gallery = [mainImg.src];
        }
        return gallery;
    }

    function openLightbox(idx) {
        getGallery();
        current = Math.max(0, Math.min(gallery.length - 1, idx));
        lbImg.src = gallery[current];
        lb.classList.add('open');
        document.body.style.overflow = 'hidden';
        updateCounter();
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

    // Nút xem toàn màn hình
    if (fsBtn) {
        fsBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            getGallery();
            const src = document.getElementById('mainImage')?.src;
            const idx = gallery.indexOf(src);
            openLightbox(idx >= 0 ? idx : 0);
        });
    }

    // Trên mobile/màn hình cảm ứng: tap ảnh để xem toàn màn hình
    if (wrap) {
        wrap.addEventListener('click', function (e) {
            if (e.target.closest('#pdFullscreenBtn')) return;
            if (window.innerWidth < 992 || window.matchMedia('(pointer: coarse)').matches) {
                getGallery();
                const src = document.getElementById('mainImage')?.src;
                const idx = gallery.indexOf(src);
                openLightbox(idx >= 0 ? idx : 0);
            }
        });
    }

    // Thumb click -> cập nhật gallery index
    document.querySelectorAll('.pd-thumb').forEach(function (t, i) {
        t.addEventListener('click', function () { current = i; });
    });

    if (lbClose) lbClose.addEventListener('click', closeLightbox);

    lb.addEventListener('click', function (e) {
        if (e.target === lb) closeLightbox();
    });

    if (lbPrev) lbPrev.addEventListener('click', function (e) {
        e.stopPropagation();
        getGallery();
        current = (current - 1 + gallery.length) % gallery.length;
        lbImg.src = gallery[current];
        updateCounter();
    });

    if (lbNext) lbNext.addEventListener('click', function (e) {
        e.stopPropagation();
        getGallery();
        current = (current + 1) % gallery.length;
        lbImg.src = gallery[current];
        updateCounter();
    });

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
