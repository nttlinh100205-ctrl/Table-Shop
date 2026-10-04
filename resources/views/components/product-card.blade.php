{{-- resources/views/components/product-card.blade.php --}}
@props(['product', 'colorMap' => []])

@php
    if ($product->image) {
        $src = asset('storage/' . $product->image);
    } elseif ($product->images->first()) {
        $src = asset('storage/' . $product->images->first()->path);
    } else {
        $src = null;
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

    $discountPct = null;
    if ($displayOld && $displayOld > $displayPrice && $displayPrice > 0) {
        $discountPct = round((1 - $displayPrice / $displayOld) * 100);
    }
@endphp

<article class="nth-card product-card"
         data-product-id="{{ $product->id }}"
         data-product-name="{{ $product->name }}"
         data-variants='@json($variantsJson)'>

    {{-- Khung ảnh tỉ lệ 4:5 --}}
    <div class="nth-card__media">
        @if($discountPct)
            <span class="nth-badge-discount">-{{ $discountPct }}%</span>
        @endif

        <a href="{{ route('user.products.show', $product->id) }}" class="nth-card__img-link">
            @if($src)
                <img src="{{ $src }}"
                     alt="{{ $product->name }}"
                     class="nth-card__img"
                     loading="lazy">
            @else
                <div class="nth-card__placeholder">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="M21 15l-5-5L5 21"/>
                    </svg>
                </div>
            @endif
        </a>

        {{-- Nút Thêm vào giỏ (chỉ hiện khi hover trên Desktop) --}}
        <div class="nth-card__action-wrap">
            <button type="button" class="nth-btn-quick-add btn-bag" aria-label="Thêm vào giỏ">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <span>Thêm vào giỏ</span>
            </button>

            @if(count($sizeGroups) > 0)
                <ul class="size-popup nth-size-popup">
                    <li class="size-hint nth-size-hint">Chọn kích thước</li>
                    @foreach($sizeGroups as $sizeKey => $group)
                        @php
                            $colorsForSize = collect($group['variants'])
                                ->filter(fn ($v) => ($v->stock ?? 0) > 0)
                                ->pluck('color')
                                ->filter()
                                ->unique()
                                ->values()
                                ->all();
                            $hasAnyStock = collect($group['variants'])->contains(fn ($v) => ($v->stock ?? 0) > 0);
                        @endphp
                        <li class="size-option nth-size-option {{ $hasAnyStock ? '' : 'out-of-stock' }}"
                            data-size-key="{{ $sizeKey }}"
                            data-colors='@json($colorsForSize)'>
                            {{ $group['label'] }}
                        </li>
                    @endforeach
                    {{-- Ẩn hoàn toàn dòng "không có size" theo yêu cầu --}}
                    <li class="size-empty" style="display:none !important;"></li>
                </ul>
            @endif
        </div>
    </div>

    {{-- Phần thông tin sản phẩm --}}
    <div class="nth-card__body">
        <a href="{{ route('user.products.show', $product->id) }}" class="nth-card__title-link">
            <h3 class="nth-card__title" title="{{ $product->name }}">
                {{ $product->name }}
            </h3>
        </a>

        {{-- Ô tròn màu sắc Swatch --}}
        @if($colors->count())
            <div class="nth-swatches color-dots">
                @foreach($colors as $colorName)
                    @php
                        $colorModel = $colorMap[$colorName] ?? null;
                        $colorCode = $colorModel->code ?? $colorName;
                    @endphp
                    <button type="button"
                            class="nth-swatch color-dot {{ $loop->first ? 'active' : '' }}"
                            data-color="{{ $colorName }}"
                            title="{{ $colorName }}"
                            aria-label="Màu {{ $colorName }}">
                        <span class="nth-swatch__circle {{ $colorModel && ($colorModel->image_url || $colorModel->hex) ? '' : 'is-empty' }}"
                              @if($colorModel) style="{{ $colorModel->swatch_style }}" @endif></span>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Giá sản phẩm --}}
        <div class="nth-card__price-wrap">
            <span class="nth-card__price">
                @if($displayPrice)
                    {{ number_format($displayPrice, 0, ',', '.') }} đ
                @else
                    Liên hệ tư vấn
                @endif
            </span>
            @if($displayOld && $displayOld > $displayPrice)
                <span class="nth-card__price-old">
                    {{ number_format($displayOld, 0, ',', '.') }} đ
                </span>
            @endif
        </div>
    </div>
</article>

<style>
/* ========================================================
   PRODUCT CARD STYLES — NỘI THẤT TINH HOA
   - Tỉ lệ 4:5, góc bo 2px, nút giỏ hàng hover mượt mà
   ======================================================== */
.nth-card {
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
    transition: transform 0.4s cubic-bezier(0.22, 0.61, 0.36, 1),
                box-shadow 0.4s cubic-bezier(0.22, 0.61, 0.36, 1),
                border-color 0.4s cubic-bezier(0.22, 0.61, 0.36, 1);
}
.nth-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(58, 46, 38, 0.08);
    border-color: #C29D62;
}

/* Media 4:5 */
.nth-card__media {
    position: relative;
    width: 100%;
    aspect-ratio: 4 / 5;
    background: #F3E9DC;
    overflow: hidden;
}
.nth-card__img-link {
    display: block;
    width: 100%;
    height: 100%;
}
.nth-card__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.6s cubic-bezier(0.22, 0.61, 0.36, 1);
}
.nth-card:hover .nth-card__img {
    transform: scale(1.04);
}
.nth-card__placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #7E7065;
    opacity: 0.6;
}

/* Nhãn giảm giá tinh tế */
.nth-badge-discount {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 3;
    background: #3F2F24;
    color: #F3E9DC;
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: 0.03em;
    padding: 2px 6px;
    border-radius: 2px;
}

/* Nút thêm giỏ hàng chỉ hiện khi hover */
.nth-card__action-wrap {
    position: absolute;
    left: 8px;
    right: 8px;
    bottom: 8px;
    z-index: 4;
    opacity: 0;
    transform: translateY(6px);
    transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
    pointer-events: none;
}
.nth-card:hover .nth-card__action-wrap {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}
.nth-btn-quick-add {
    width: 100%;
    height: 34px;
    background: #5A4536;
    color: #FAF6F0;
    border: none;
    border-radius: 2px;
    font-size: 11.5px;
    font-weight: 600;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(63, 47, 36, 0.18);
    transition: background 0.25s ease;
}
.nth-btn-quick-add:hover {
    background: #3F2F24;
}

/* Popup chọn kích thước */
.nth-size-popup {
    display: none;
    position: absolute;
    bottom: calc(100% + 6px);
    left: 0;
    right: 0;
    background: #FAF6F0;
    border: 1px solid #E6D8C8;
    border-radius: 2px;
    box-shadow: 0 8px 20px rgba(58, 46, 38, 0.12);
    padding: 4px 0;
    list-style: none;
    margin: 0;
    z-index: 20;
}
.nth-size-popup.open {
    display: block;
}
.nth-size-hint {
    padding: 5px 12px 3px;
    font-size: 10px;
    letter-spacing: 0.1em;
    color: #7E7065;
    text-transform: uppercase;
    font-weight: 600;
}
.nth-size-option {
    padding: 6px 12px;
    font-size: 12px;
    color: #3A2E26;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.nth-size-option:hover {
    background: #F3E9DC;
    color: #5A4536;
}
.nth-size-option.out-of-stock,
.nth-size-option.hidden-by-color {
    display: none !important;
}

/* Body */
.nth-card__body {
    padding: 10px 12px 14px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.nth-card__title-link {
    text-decoration: none;
    color: inherit;
}
.nth-card__title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 16px;
    font-weight: 600;
    color: #3A2E26;
    margin: 0 0 6px;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: color 0.2s;
}
.nth-card:hover .nth-card__title {
    color: #5A4536;
}

/* Swatches tròn */
.nth-swatches {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 8px;
}
.nth-swatch {
    background: none;
    border: none;
    padding: 1px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: 1px solid transparent;
    transition: border-color 0.2s;
}
.nth-swatch:hover,
.nth-swatch.active {
    border-color: #5A4536;
}
.nth-swatch__circle {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: block;
    background-size: cover;
    background-position: center;
    border: 1px solid rgba(58, 46, 38, 0.15);
}
.nth-swatch__circle.is-empty {
    background: repeating-conic-gradient(#ddd 0% 25%, #fff 0% 50%) 50% / 5px 5px;
}
.nth-swatch.out-of-stock {
    opacity: 0.4;
}

/* Giá */
.nth-card__price-wrap {
    margin-top: auto;
    display: flex;
    align-items: baseline;
    gap: 6px;
}
.nth-card__price {
    font-family: 'Manrope', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #5A4536;
}
.nth-card__price-old {
    font-size: 11.5px;
    color: #7E7065;
    text-decoration: line-through;
    opacity: 0.75;
}

/* Mobile: luôn hiển thị nút thêm giỏ hàng để dễ chạm */
@media (max-width: 991px) {
    .nth-card__action-wrap {
        opacity: 1;
        transform: none;
        pointer-events: auto;
        position: static;
        margin-top: 10px;
    }
    .nth-card {
        padding-bottom: 0;
    }
}
</style>
