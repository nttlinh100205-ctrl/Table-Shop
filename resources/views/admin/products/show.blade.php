@extends('layouts.admin')

@section('title', $product->name)

@section('content')
<style>
    :root {
        --pd-primary: #3f2f24;
        --pd-accent: #dc2626;
        --pd-ocean: #0284c7;        /* xanh nước biển */
        --pd-ocean-dark: #0369a1;
        --pd-ocean-soft: #e0f2fe;
        --pd-muted: #7e7065;
        --pd-border: #e6d8c8;
        --pd-bg: #f3e9dc;
    }

    .pd-page { background: var(--pd-bg); min-height: 80vh; padding: 20px 0 48px; }

    .pd-breadcrumb { font-size: 0.84rem; color: var(--pd-muted); margin-bottom: 16px; }
    .pd-breadcrumb a { color: var(--pd-muted); text-decoration: none; }
    .pd-breadcrumb a:hover { color: var(--pd-ocean); }

    .pd-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 16px rgba(63, 47, 36, 0.05);
        border: 1px solid var(--pd-border);
        overflow: hidden;
        margin-bottom: 24px;
    }

    /* Gallery */
    .pd-gallery-wrap { padding: 24px 24px 16px; background: #faf6f0; }
    .pd-main-img {
        width: 100%; height: 380px; object-fit: contain;
        border-radius: 10px; background: #fff; display: block; margin: 0 auto;
    }
    .pd-no-img {
        height: 300px; display: flex; align-items: center; justify-content: center;
        color: var(--pd-muted); background: #f3e9dc; border-radius: 10px;
    }
    .pd-thumbs { display: flex; gap: 10px; margin-top: 14px; overflow-x: auto; padding-bottom: 6px; }
    .pd-thumb {
        flex: 0 0 88px; width: 88px; height: 72px; object-fit: cover;
        border-radius: 8px; border: 2px solid transparent; cursor: pointer;
        background: #fff; opacity: 0.85; transition: all 0.2s;
    }
    .pd-thumb:hover { opacity: 1; border-color: #9c8875; }
    .pd-thumb.active { border-color: var(--pd-ocean); opacity: 1; box-shadow: 0 0 0 1px var(--pd-ocean); }

    /* Info */
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

    .pd-attr { display: flex; padding: 9px 0; border-bottom: 1px solid #f3e9dc; font-size: 0.92rem; }
    .pd-attr:last-of-type { border-bottom: none; }
    .pd-attr-label { flex: 0 0 110px; color: var(--pd-ocean); font-weight: 600; }
    .pd-attr-value { flex: 1; color: #5a4536; line-height: 1.5; }

    .pd-color-chip {
        display: inline-block; background: var(--pd-ocean-soft); color: var(--pd-ocean-dark);
        font-size: 0.8rem; padding: 2px 10px; border-radius: 12px; margin: 1px 3px 1px 0;
        border: 1px solid #7dd3fc;
    }

    /* Giá */
    .pd-price-wrap { margin: 12px 0 22px; }
    .pd-discount {
        display: inline-block; background: var(--pd-accent); color: #fff;
        font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; margin-bottom: 4px;
    }
    .pd-price-current { font-size: 1.9rem; font-weight: 800; color: var(--pd-accent); letter-spacing: -0.5px; }
    .pd-price-old { font-size: 1.05rem; color: #9c8875; text-decoration: line-through; margin-left: 10px; font-weight: 500; }

    /* Size */
    .pd-size-label { font-size: 0.88rem; font-weight: 600; color: var(--pd-primary); margin-bottom: 10px; }
    .pd-sizes { display: flex; flex-wrap: wrap; gap: 8px; }
    .pd-size-btn {
        border: 1.5px solid #d9c7b3; background: #fff; color: #5a4536;
        padding: 8px 14px; border-radius: 8px; font-size: 0.84rem; font-weight: 500;
        cursor: pointer; transition: all 0.15s;
    }
    .pd-size-btn:hover { border-color: var(--pd-ocean); color: var(--pd-ocean); }
    .pd-size-btn.active {
        border-color: var(--pd-ocean); background: var(--pd-ocean); color: #fff; font-weight: 600;
    }

    .pd-color-label { font-size: 0.88rem; font-weight: 600; color: var(--pd-primary); margin: 16px 0 10px; }
    .pd-colors { display: flex; flex-wrap: wrap; gap: 8px; }
    .pd-color-btn {
        display: inline-flex; align-items: center; gap: 8px;
        border: 1.5px solid #d9c7b3; background: #fff; color: #5a4536;
        padding: 6px 12px 6px 8px; border-radius: 8px; font-size: 0.84rem; font-weight: 500;
        cursor: pointer; transition: all 0.15s;
    }
    .pd-color-btn:hover { border-color: var(--pd-ocean); color: var(--pd-ocean); }
    .pd-color-btn.active {
        border-color: var(--pd-ocean); background: var(--pd-ocean-soft); color: var(--pd-ocean-dark); font-weight: 600;
        box-shadow: 0 0 0 1px var(--pd-ocean);
    }
    .pd-color-btn.disabled {
        opacity: 0.35; cursor: not-allowed; pointer-events: none;
    }
    .pd-color-dot {
        width: 20px; height: 20px; border-radius: 4px;
        border: 1px solid rgba(0,0,0,.15);
        flex-shrink: 0;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.3);
    }
    .pd-color-dot.is-empty {
        background: repeating-conic-gradient(#eee 0% 25%, #fff 0% 50%) 50% / 8px 8px;
    }

    .pd-actions { margin-top: 26px; display: flex; gap: 10px; }
    .pd-btn-back {
        background: #fff; border: 1.5px solid #d9c7b3; color: var(--pd-primary);
        padding: 9px 18px; border-radius: 8px; font-size: 0.88rem; text-decoration: none; font-weight: 500;
    }
    .pd-btn-back:hover { background: #faf6f0; color: var(--pd-primary); }
    .pd-btn-edit {
        background: var(--pd-ocean); border: none; color: #fff;
        padding: 9px 20px; border-radius: 8px; font-size: 0.88rem; text-decoration: none; font-weight: 500;
    }
    .pd-btn-edit:hover { background: var(--pd-ocean-dark); color: #fff; }

    /* Tabs mô tả / ưu điểm / hướng dẫn */
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
        font-size: 0.95rem; line-height: 1.8; color: #6b5848;
        display: none;
    }
    .pd-tab-content.active { display: block; }
    .pd-tab-content ul { padding-left: 1.2rem; margin-bottom: 0; }
    .pd-tab-content li { margin-bottom: 6px; }

    /* Thông số kỹ thuật */
    .pd-specs-title {
        font-size: 1.15rem; font-weight: 700; color: var(--pd-primary);
        padding: 20px 28px 12px; margin: 0;
        border-bottom: 1px solid var(--pd-border);
    }
    .pd-specs-table {
        width: 100%; border-collapse: collapse; font-size: 0.92rem;
    }
    .pd-specs-table tr { border-bottom: 1px solid #f3e9dc; }
    .pd-specs-table tr:last-child { border-bottom: none; }
    .pd-specs-table th {
        width: 200px; text-align: left; padding: 12px 28px;
        color: var(--pd-ocean); font-weight: 600; background: #faf6f0;
        vertical-align: top;
    }
    .pd-specs-table td {
        padding: 12px 28px; color: #5a4536;
    }

    @media (max-width: 767px) {
        .pd-info { padding: 20px; }
        .pd-main-img { height: 260px; }
        .pd-title { font-size: 1.25rem; }
        .pd-price-current { font-size: 1.55rem; }
        .pd-tab { padding: 12px 14px; font-size: 0.88rem; }
        .pd-specs-table th { width: 120px; padding: 10px 16px; }
        .pd-specs-table td { padding: 10px 16px; }
    }
</style>

@php
    $allImages = $product->all_images;
    $firstVariant = $product->variants->first();
@endphp

<div class="pd-page">
    <div class="container">
        <div class="pd-breadcrumb">
            <a href="{{ url('/') }}">Trang chủ</a> ›
            <a href="{{ route('admin.products.index') }}">Sản phẩm</a> ›
            <span>{{ Str::limit($product->name, 40) }}</span>
        </div>

        {{-- Khối chính: ảnh + thông tin --}}
        <div class="pd-card">
            <div class="row g-0">
                <div class="col-lg-6">
                    <div class="pd-gallery-wrap">
                        @if ($allImages->count())
                            <img src="{{ $allImages->first() }}" alt="{{ $product->name }}" class="pd-main-img" id="mainImage">
                            @if ($allImages->count() > 1)
                                <div class="pd-thumbs">
                                    @foreach ($allImages as $i => $url)
                                        <img src="{{ $url }}" class="pd-thumb {{ $i === 0 ? 'active' : '' }}"
                                             data-src="{{ $url }}" alt="Ảnh {{ $i + 1 }}">
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="pd-no-img">Chưa có ảnh sản phẩm</div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6">
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
                            @else
                                <span class="pd-price-current">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                            @endif
                        </div>

                        @if ($product->variants->count())
                            @php
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
                                $hexMap = \App\Models\Color::query()
                                    ->whereNotNull('hex')
                                    ->pluck('hex', 'name');
                                $allColors = $product->variants->pluck('color')->filter()->unique()->values();
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
                                <div class="pd-color-label">Chọn màu</div>
                                <div class="pd-colors" id="pdColors">
                                    @foreach ($allColors as $colorName)
                                        @php $hex = $hexMap[$colorName] ?? null; @endphp
                                        <button type="button"
                                                class="pd-color-btn"
                                                data-color="{{ $colorName }}"
                                                title="{{ $colorName }}{{ $hex ? ' ('.$hex.')' : '' }}">
                                            <span class="pd-color-dot {{ $hex ? '' : 'is-empty' }}"
                                                  @if($hex) style="background: {{ $hex }};" @endif></span>
                                            <span>{{ $colorName }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

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
                        @endif

                        <div class="pd-actions">
                            <a href="{{ route('admin.products.index') }}" class="pd-btn-back">← Danh sách</a>
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="pd-btn-edit">Sửa sản phẩm</a>
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
                <tr>
                    <th>Màu sắc</th>
                    <td>
                        @php $colors = $product->variants->pluck('color')->filter()->unique()->values(); @endphp
                        @if ($colors->count())
                            {{ $colors->implode(', ') }}
                        @else
                            {{ $product->color ?? '—' }}
                        @endif
                    </td>
                </tr>
                @if ($product->warranty)
                <tr>
                    <th>Bảo hành</th>
                    <td>{{ $product->warranty }}</td>
                </tr>
                @endif
                @if ($product->variants->count())
                @php $hasDesktopWidth = $product->variants->contains(fn($v) => !is_null($v->desktop_width) && $v->desktop_width !== ''); @endphp
                <tr>
                    <th>Kích thước &amp; giá</th>
                    <td>
                        <table class="table table-sm table-bordered mb-0" style="font-size:0.88rem; max-width:560px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Size</th>
                                    @if ($hasDesktopWidth)
                                    <th>Mặt bàn</th>
                                    @endif
                                    <th>Màu</th>
                                    <th>Giá</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($product->variants as $v)
                                    <tr>
                                        <td>{{ $v->size_button_label }}</td>
                                        @if ($hasDesktopWidth)
                                        <td>
                                            @if ($v->desktop_width)
                                                {{ fmod($v->desktop_width, 1) == 0 ? (int)$v->desktop_width : $v->desktop_width }}cm
                                            @else — @endif
                                        </td>
                                        @endif
                                        <td>{{ $v->color ?? '—' }}</td>
                                        <td><strong>{{ number_format($v->price, 0, ',', '.') }}đ</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.pd-thumb').forEach(function (thumb) {
    thumb.addEventListener('click', function () {
        document.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('mainImage').src = this.dataset.src;
    });
});

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
            const colorOk = selectedColor ? (v.color === selectedColor) : true;
            return sizeOk && colorOk;
        }) || variants.find(function (v) {
            return selectedSize ? v.size_key === selectedSize : true;
        }) || variants[0];
    }

    function colorsForSize(sizeKey) {
        const set = {};
        variants.forEach(function (v) {
            if (v.size_key === sizeKey && v.color) set[v.color] = true;
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
        if (selectedColor && selectedSize && !available[selectedColor]) {
            selectedColor = firstEnabled ? firstEnabled.dataset.color : null;
            btns.forEach(function (btn) {
                btn.classList.toggle('active', btn.dataset.color === selectedColor);
            });
        }
        if (!selectedColor && firstEnabled) {
            selectedColor = firstEnabled.dataset.color;
            firstEnabled.classList.add('active');
        }
    }

    document.querySelectorAll('.pd-size-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.pd-size-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedSize = this.dataset.sizeKey;
            refreshColorButtons();
            updatePrice(findVariant());
        });
    });

    document.querySelectorAll('.pd-color-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (this.classList.contains('disabled')) return;
            document.querySelectorAll('.pd-color-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedColor = this.dataset.color;
            updatePrice(findVariant());
        });
    });

    const firstSize = document.querySelector('.pd-size-btn.active');
    selectedSize = firstSize ? firstSize.dataset.sizeKey : null;
    refreshColorButtons();
    const firstColorBtn = document.querySelector('.pd-color-btn.active') || document.querySelector('.pd-color-btn:not(.disabled)');
    if (firstColorBtn) {
        firstColorBtn.classList.add('active');
        selectedColor = firstColorBtn.dataset.color;
    }
    updatePrice(findVariant());
})();

// Tabs
document.querySelectorAll('.pd-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.pd-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.pd-tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});
</script>
@endsection
