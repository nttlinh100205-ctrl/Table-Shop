@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
<style>
    /* ===== Hero banner ===== */
    .hero-banner-wrap { width:100%; margin:0; padding:0; line-height:0; }
    .hero-banner {
        position: relative; overflow: hidden; width: 100%; display: block;
    }
    .hero-banner img.hero-img {
        width: 100%; height: 300px; max-height: 300px;
        object-fit: cover; object-position: center 35%; display: block;
    }
    /* gradient overlay for text readability if needed */
    .hero-banner::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(to right, rgba(15,23,42,0.35) 0%, transparent 60%);
        pointer-events: none;
    }
    @media (max-width: 991px) {
        .hero-banner img.hero-img { height: 210px; max-height: 210px; }
    }
    @media (max-width: 575px) {
        .hero-banner img.hero-img { height: 160px; max-height: 160px; }
    }

    /* Feature strip */
    .feature-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
        margin-top: 1.5rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 991px) { .feature-strip { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .feature-strip { grid-template-columns: 1fr; margin-top: 1rem; } }
    .feature-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        transition: box-shadow .15s, transform .15s, border-color .15s;
    }
    .feature-item:hover {
        border-color: #bae6fd;
        box-shadow: 0 6px 20px rgba(14,165,233,.1);
        transform: translateY(-2px);
    }
    .feature-icon {
        width: 42px; height: 42px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; flex-shrink: 0;
        color: #fff;
    }
    .feature-icon.fi-1 { background: linear-gradient(135deg,#0ea5e9,#2563eb); }
    .feature-icon.fi-2 { background: linear-gradient(135deg,#8b5cf6,#7c3aed); }
    .feature-icon.fi-3 { background: linear-gradient(135deg,#10b981,#059669); }
    .feature-icon.fi-4 { background: linear-gradient(135deg,#f59e0b,#d97706); }
    .feature-item strong { display: block; font-size: 0.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.15rem; }
    .feature-item span { font-size: 0.76rem; color: #64748b; line-height: 1.4; }

    /* Section heading */
    .section-title {
        font-size: 1.1rem; font-weight: 800; color: #0f172a;
        margin-bottom: 1.25rem;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 0.75rem;
    }
    .section-title-left {
        display: flex; align-items: center; gap: 0.6rem;
    }
    .section-title-left::before {
        content: ''; display: block;
        width: 4px; height: 20px; border-radius: 2px;
        background: linear-gradient(180deg,#2563eb,#7c3aed);
    }
    .section-title .section-line {
        flex: 1; height: 1px; background: #e2e8f0; min-width: 40px;
    }
    .btn-clear-filter {
        font-size: 0.75rem; font-weight: 600; color: #64748b;
        background: #fff; border: 1px solid #e2e8f0;
        padding: 0.3rem 0.75rem; border-radius: 8px; text-decoration: none;
        transition: all .15s; display: inline-flex; align-items: center; gap: 0.3rem;
    }
    .btn-clear-filter:hover { border-color: #ef4444; color: #ef4444; }

    /* Product cards */
    .product-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        height: 100%;
        position: relative;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.12) !important;
        border-color: #bae6fd;
        z-index: 5;
    }
    .product-card .img-wrap {
        position: relative; overflow: hidden;
        background: #f8fafc;
    }
    .product-card .card-img-top {
        height: 200px; width: 100%;
        object-fit: cover; display: block;
        transition: transform .4s ease;
    }
    .product-card:hover .card-img-top { transform: scale(1.05); }

    .price-tag { color: #dc2626; font-weight: 800; font-size: 0.95rem; }
    .price-old { color: #94a3b8; text-decoration: line-through; font-size: 0.78rem; margin-left: 0.3rem; }
    .sale-badge {
        position: absolute; top: 10px; left: 10px; z-index: 2;
        background: linear-gradient(135deg,#ef4444,#dc2626);
        color: #fff; font-size: 0.68rem; font-weight: 800;
        padding: 0.2rem 0.55rem; border-radius: 6px;
        letter-spacing: 0.02em;
    }
    .price-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: 8px; margin-top: 8px; position: relative;
    }
    .btn-bag {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg,#2563eb,#7c3aed);
        color: #fff; border: none;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.9rem; cursor: pointer; flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(37,99,235,.3);
        transition: transform .15s, box-shadow .15s;
    }
    .btn-bag:hover { transform: scale(1.1); box-shadow: 0 4px 14px rgba(37,99,235,.4); }

    .size-popup {
        display: none;
        position: absolute;
        bottom: 42px;
        right: 0;
        min-width: 120px;
        max-width: 200px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 8px 28px rgba(15,23,42,.18);
        border: 1px solid #e2e8f0;
        padding: 6px 0;
        z-index: 20;
        list-style: none;
        margin: 0;
    }
    .size-popup.open { display: block; }
    .size-popup li {
        padding: 8px 14px;
        font-size: 0.84rem;
        cursor: pointer;
        color: #1e293b;
        list-style: none;
    }
    .size-popup li:hover { background: #f1f5f9; color: #0284c7; }
    .size-popup li.out-of-stock,
    .size-popup li.hidden-by-color {
        display: none;
    }
    .size-popup .size-hint {
        font-size: 0.72rem;
        font-weight: 600;
        color: #94a3b8;
        cursor: default;
        padding: 4px 14px 2px;
    }
    .size-popup .size-hint:hover { background: transparent; color: #94a3b8; }
    .size-popup .size-empty {
        font-size: 0.8rem;
        color: #94a3b8;
        cursor: default;
        display: none;
        padding: 8px 14px;
    }

    .color-dots {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 6px;
    }
    .color-dot {
        display: inline-flex; align-items: center; gap: 5px;
        max-width: 100%; padding: 3px 6px 3px 4px;
        border: 1px solid #e2e8f0; border-radius: 999px;
        background: #fff; color: #475569;
        cursor: pointer;
        font-size: 0.65rem; line-height: 1.2;
    }
    .color-dot:hover, .color-dot.active {
        border-color: #0284c7; color: #075985;
        box-shadow: 0 0 0 1px #0284c7;
    }
    .color-dot-swatch {
        width: 15px; height: 15px; border-radius: 50%; flex: 0 0 15px;
        border: 1px solid rgba(15,23,42,.16);
        background-size: cover; background-position: center;
    }
    .color-dot-swatch.is-empty {
        background: repeating-conic-gradient(#eee 0% 25%, #fff 0% 50%) 50% / 8px 8px;
    }
    .color-dot-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .color-dot.out-of-stock { opacity: .65; }
    .card-body-inner { padding: 0.875rem 1rem 1rem; }
    .product-card .card-title {
        font-size: 0.875rem; font-weight: 700; color: #1e293b;
        margin: 0; line-height: 1.35;
    }

    /* Product empty state */
    .products-empty {
        background: #fff; border-radius: 14px; border: 1px solid #e2e8f0;
        padding: 3rem 1.5rem; text-align: center;
    }
    .products-empty-icon {
        width: 64px; height: 64px; border-radius: 50%;
        background: #f1f5f9; margin: 0 auto 1rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; color: #94a3b8;
    }
</style>

@php
    $colorMap = \App\Models\Color::query()->get()->keyBy('name');
@endphp

{{-- Banner full-width (ảnh do shop cung cấp) --}}
<div class="hero-banner-wrap">
    <div class="hero-banner">
        <a href="#product-section">
            <img src="{{ asset('storage/images/banners/home-banner.png') }}"
                 alt="Shop bán bàn - Thế giới bàn cao cấp"
                 class="hero-img">
        </a>
    </div>
</div>

<div class="container pb-4" style="padding-top: 0.25rem;">

    {{-- 4 lợi ích (ý giống trang mẫu) --}}
    <div class="feature-strip">
        <div class="feature-item">
            <div class="feature-icon fi-1"><i class="bi bi-building"></i></div>
            <div>
                <strong>Xưởng sản xuất trực tiếp</strong>
                <span>Rẻ hơn 10–30% thị trường</span>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon fi-2"><i class="bi bi-palette2"></i></div>
            <div>
                <strong>Mẫu mã đa dạng</strong>
                <span>Thiết kế theo yêu cầu</span>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon fi-3"><i class="bi bi-truck"></i></div>
            <div>
                <strong>Giao hàng nhanh</strong>
                <span>Trong ngày · Hỗ trợ lắp đặt</span>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon fi-4"><i class="bi bi-shield-check"></i></div>
            <div>
                <strong>Bảo hành lâu dài</strong>
                <span>Đổi trả dễ dàng</span>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="section-title" id="product-section">
        <div class="section-title-left">
            @if(request('q'))
                Kết quả tìm: "{{ request('q') }}"
            @elseif(!empty($currentCategory))
                {{ $currentCategory->name }}
            @else
                Sản phẩm nổi bật
            @endif
        </div>
        <span class="section-line"></span>
        @if(request('q') || request('category') || request('sub_category') || request('sub_sub_category'))
            <a href="{{ route('user.home') }}" class="btn-clear-filter">
                <i class="bi bi-x-circle" style="font-size:0.75rem;"></i> Xóa bộ lọc
            </a>
        @endif
    </div>

    <div class="row g-3">
        @forelse($products as $product)
            @php
                if ($product->image) {
                    $src = asset('storage/' . $product->image);
                } elseif ($product->images->first()) {
                    $src = asset('storage/' . $product->images->first()->path);
                } else {
                    $src = null; // handled inline
                }

                $displayPrice = $product->price;
                $displayOld = $product->price_old;
                if ((!$displayPrice || $displayPrice <= 0) && $product->variants->isNotEmpty()) {
                    $displayPrice = $product->variants->min('price');
                    $cheapest = $product->variants->sortBy('price')->first();
                    $displayOld = $cheapest->price_old ?? null;
                }

                $sizeGroups = [];
                foreach ($product->variants as $v) {
                    $sizeKey = $v->size_label ?: $v->dimensions ?: ('#' . $v->id);
                    if (!isset($sizeGroups[$sizeKey])) {
                        $sizeGroups[$sizeKey] = [
                            'label' => $v->size_button_label ?: $sizeKey,
                            'variants' => [],
                        ];
                    }
                    $sizeGroups[$sizeKey]['variants'][] = $v;
                }

                $colors = $product->variants->pluck('color')->filter()->unique()->values();

                $variantsJson = $product->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'size_key' => $v->size_label ?: $v->dimensions ?: ('#' . $v->id),
                    'color' => $v->color,
                    'price' => (float) $v->price,
                    'stock' => (int) ($v->stock ?? 0),
                ])->values();
            @endphp

            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card shadow-sm h-100"
                     data-product-id="{{ $product->id }}"
                     data-product-name="{{ $product->name }}"
                     data-variants='@json($variantsJson)'>

                    <div class="img-wrap">
                        @if($displayOld && $displayOld > $displayPrice && $displayPrice > 0)
                            @php $pct = round((1 - $displayPrice / $displayOld) * 100); @endphp
                            <span class="sale-badge">-{{ $pct }}%</span>
                        @endif
                        <a href="{{ route('user.products.show', $product->id) }}">
                            @if($src)
                                <img src="{{ $src }}" class="card-img-top" alt="{{ $product->name }}">
                            @else
                                <div class="card-img-top d-flex align-items-center justify-content-center"
                                     style="background:#f1f5f9;color:#94a3b8;font-size:2rem;">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                        </a>
                    </div>

                    <div class="card-body-inner">
                        <a href="{{ route('user.products.show', $product->id) }}" class="text-decoration-none">
                            <h6 class="card-title text-truncate" title="{{ $product->name }}">
                                {{ $product->name }}
                            </h6>
                        </a>

                        {{-- Chấm màu --}}
                        @if($colors->count())
                            <div class="color-dots">
                                @foreach($colors as $colorName)
                                    @php
                                        $colorModel = $colorMap[$colorName] ?? null;
                                        $colorCode = $colorModel->code ?? $colorName;
                                    @endphp
                                    <button type="button" class="color-dot {{ $loop->first ? 'active' : '' }}"
                                            data-color="{{ $colorName }}" title="{{ $colorName }}"
                                            aria-label="Màu {{ $colorName }}">
                                        <span class="color-dot-swatch {{ $colorModel && ($colorModel->image_url || $colorModel->hex) ? '' : 'is-empty' }}"
                                              @if($colorModel) style="{{ $colorModel->swatch_style }}" @endif></span>
                                        <span class="color-dot-label">{{ $colorCode }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Giá + nút túi (dưới) --}}
                        <div class="price-row">
                            <div>
                                <span class="price-tag">
                                    @if($displayPrice)
                                        {{ number_format($displayPrice, 0, ',', '.') }} đ
                                    @else
                                        Liên hệ
                                    @endif
                                </span>
                                @if($displayOld && $displayOld > $displayPrice)
                                    <span class="price-old">
                                        {{ number_format($displayOld, 0, ',', '.') }} đ
                                    </span>
                                @endif
                            </div>

                            <div class="position-relative">
                                <button type="button" class="btn-bag" title="Thêm vào giỏ" aria-label="Thêm vào giỏ">
                                    <i class="bi bi-bag"></i>
                                </button>

                                @if(count($sizeGroups) > 0)
                                    <ul class="size-popup">
                                        <li class="size-hint">Chọn size</li>
                                        @foreach($sizeGroups as $sizeKey => $group)
                                            @php
                                                // Màu nào có size này (và còn hàng)
                                                $colorsForSize = collect($group['variants'])
                                                    ->filter(fn ($v) => ($v->stock ?? 0) > 0)
                                                    ->pluck('color')
                                                    ->filter()
                                                    ->unique()
                                                    ->values()
                                                    ->all();
                                                $hasAnyStock = collect($group['variants'])->contains(fn ($v) => ($v->stock ?? 0) > 0);
                                            @endphp
                                            <li class="size-option {{ $hasAnyStock ? '' : 'out-of-stock' }}"
                                                data-size-key="{{ $sizeKey }}"
                                                data-colors='@json($colorsForSize)'>
                                                {{ $group['label'] }}
                                            </li>
                                        @endforeach
                                        <li class="size-empty">Không có size cho màu này</li>
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="products-empty">
                    <div class="products-empty-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <p style="font-size:0.875rem;font-weight:700;color:#0f172a;margin-bottom:0.35rem;">Không tìm thấy sản phẩm</p>
                    <p style="font-size:0.8rem;color:#64748b;margin-bottom:1rem;">Hãy thử từ khóa khác hoặc xóa bộ lọc.</p>
                    <a href="{{ route('user.home') }}"
                       style="display:inline-flex;align-items:center;gap:0.35rem;font-size:0.8rem;font-weight:700;color:#2563eb;text-decoration:none;">
                        <i class="bi bi-x-circle"></i> Xóa bộ lọc
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    document.addEventListener('click', function () {
        document.querySelectorAll('.size-popup.open').forEach(p => p.classList.remove('open'));
    });

    document.querySelectorAll('.product-card').forEach(function (card) {
        const productId = card.dataset.productId;
        let variants = [];
        try { variants = JSON.parse(card.dataset.variants || '[]'); } catch (e) {}

        const bagBtn = card.querySelector('.btn-bag');
        const popup = card.querySelector('.size-popup');
        const colorDots = card.querySelectorAll('.color-dot');

        function selectedColor() {
            const active = card.querySelector('.color-dot.active');
            return active ? active.dataset.color : null;
        }

        /** Chỉ hiện size có đúng màu đang chọn (và còn hàng). Size không có màu đó → ẩn. */
        function filterSizesByColor() {
            if (!popup) return;
            const color = selectedColor();
            let visibleCount = 0;

            popup.querySelectorAll('.size-option').forEach(function (li) {
                if (li.classList.contains('out-of-stock')) {
                    li.classList.add('hidden-by-color');
                    return;
                }

                let colorsForSize = [];
                try { colorsForSize = JSON.parse(li.dataset.colors || '[]'); } catch (e) {}

                let show = true;
                if (color) {
                    // Chỉ hiện size có đúng màu đã chọn
                    show = colorsForSize.indexOf(color) !== -1;
                }

                if (show) {
                    li.classList.remove('hidden-by-color');
                    visibleCount++;
                } else {
                    li.classList.add('hidden-by-color');
                }
            });

            const emptyEl = popup.querySelector('.size-empty');
            if (emptyEl) {
                emptyEl.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        /** Giữ mọi màu hiển thị; đánh dấu màu hết hàng để khách vẫn thấy tùy chọn. */
        function filterColorDots() {
            if (!colorDots.length) return;
            colorDots.forEach(function (dot) {
                const c = dot.dataset.color;
                const hasSize = variants.some(function (v) {
                    return v.color === c && (v.stock || 0) > 0 && v.size_key;
                });
                dot.style.display = '';
                dot.classList.toggle('out-of-stock', !hasSize);
                dot.setAttribute('aria-label', `Màu ${c}${hasSize ? '' : ' (hết hàng)'}`);
            });
        }

        // Chọn màu → lọc lại size
        colorDots.forEach(function (dot) {
            dot.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                colorDots.forEach(d => d.classList.remove('active'));
                this.classList.add('active');
                filterSizesByColor();
                // Nếu popup đang mở thì giữ mở với list mới
            });
        });

        // Ẩn màu không có size; lọc size theo màu mặc định
        filterColorDots();
        filterSizesByColor();

        function findVariant(sizeKey, color) {
            let v = variants.find(function (x) {
                const sizeOk = sizeKey ? x.size_key === sizeKey : true;
                const colorOk = color ? x.color === color : true;
                return sizeOk && colorOk && x.stock > 0;
            });
            if (v) return v;
            v = variants.find(function (x) {
                return (sizeKey ? x.size_key === sizeKey : true) && x.stock > 0;
            });
            return v || null;
        }

        function addToCart(variantId) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            formData.append('_token', csrf);
            if (variantId) formData.append('variant_id', variantId);

            return fetch('{{ route("user.cart.add") }}', {
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
                try { data = JSON.parse(text); } catch (e) {}
                if (r.status === 401 || r.status === 403) {
                    throw new Error('Bạn cần đăng nhập và xác thực email.');
                }
                if (r.status === 419) throw new Error('Phiên hết hạn. Hãy tải lại trang.');
                if (r.status === 422 && data) {
                    throw new Error(data.message || Object.values(data.errors || {}).flat().join('\n') || 'Dữ liệu không hợp lệ');
                }
                if (!r.ok || !data || !data.success) {
                    throw new Error((data && data.message) || ('Lỗi: ' + r.status));
                }
                return data;
            })
            .then(function (data) {
                if (window.updateCartBadge) window.updateCartBadge(data.cart_count);
                else {
                    const badge = document.getElementById('cart-count-badge');
                    if (badge) {
                        badge.textContent = data.cart_count;
                        badge.style.display = 'inline-block';
                    }
                }
                if (window.showCartToast) window.showCartToast(data.message || 'Thêm giỏ hàng thành công.');
            })
            .catch(function (err) {
                if (window.showCartToast) window.showCartToast(err.message || 'Không thể thêm vào giỏ.', true);
                else console.error(err);
            });
        }

        if (bagBtn) {
            bagBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                if (popup && variants.length > 0) {
                    filterSizesByColor();
                    document.querySelectorAll('.size-popup.open').forEach(p => {
                        if (p !== popup) p.classList.remove('open');
                    });
                    popup.classList.toggle('open');
                    return;
                }
                addToCart(null);
            });
        }

        if (popup) {
            popup.addEventListener('click', function (e) { e.stopPropagation(); });

            popup.querySelectorAll('.size-option').forEach(function (li) {
                li.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (this.classList.contains('out-of-stock') || this.classList.contains('hidden-by-color')) return;

                    const sizeKey = this.dataset.sizeKey;
                    const color = selectedColor();
                    const v = findVariant(sizeKey, color);

                    if (!v) {
                        if (window.showCartToast) window.showCartToast('Không có size này cho màu đang chọn (hoặc hết hàng).', true);
                        return;
                    }

                    popup.classList.remove('open');
                    addToCart(v.id);
                });
            });
        }
    });
});
</script>
@endpush
@endsection
