<div class="size-block" data-si="{{ $si }}">
    <div class="size-block-head">
        <span class="title">Size #{{ $si + 1 }}</span>
        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-size">Xóa size</button>
    </div>
    <div class="row g-2 mb-2">
        <div class="col-md-2">
            <label class="form-label small mb-0">Nhãn size</label>
            <input type="text" name="sizes[{{ $si }}][size_label]" class="form-control form-control-sm"
                   value="{{ $sg['size_label'] ?? '' }}" placeholder="BGD16_1">
        </div>
        <div class="col-md-1">
            <label class="form-label small mb-0">Dài</label>
            <input type="number" step="0.01" name="sizes[{{ $si }}][width]" class="form-control form-control-sm"
                   value="{{ $sg['width'] ?? '' }}">
        </div>
        <div class="col-md-1">
            <label class="form-label small mb-0">Sâu</label>
            <input type="number" step="0.01" name="sizes[{{ $si }}][depth]" class="form-control form-control-sm"
                   value="{{ $sg['depth'] ?? '' }}">
        </div>
        <div class="col-md-1">
            <label class="form-label small mb-0">Cao</label>
            <input type="number" step="0.01" name="sizes[{{ $si }}][height]" class="form-control form-control-sm"
                   value="{{ $sg['height'] ?? '' }}">
        </div>
        <div class="col-md-1">
            <label class="form-label small mb-0">Mặt bàn</label>
            <input type="number" step="0.01" name="sizes[{{ $si }}][desktop_width]" class="form-control form-control-sm"
                   value="{{ $sg['desktop_width'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0">Giá</label>
            <input type="number" min="0" step="1000" name="sizes[{{ $si }}][price]" class="form-control form-control-sm"
                   value="{{ $sg['price'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0">Giá cũ</label>
            <input type="number" min="0" step="1000" name="sizes[{{ $si }}][price_old]" class="form-control form-control-sm"
                   value="{{ $sg['price_old'] ?? '' }}">
        </div>
    </div>
    <div class="small fw-semibold mb-1">Chọn màu (bấm ô màu — chọn nhiều):</div>
    <div class="color-grid-wrap">
        <div class="color-pick-grid">
            @foreach ($flatColors as $c)
                @php
                    $selectedKeys = array_keys($sg['colors'] ?? []);
                    $isActive = in_array($c['name'], $selectedKeys, true)
                        || ($c['code'] && in_array($c['code'], $selectedKeys, true));
                    // Lấy tồn theo name hoặc code
                    $stockVal = $sg['colors'][$c['name']] ?? ($c['code'] ? ($sg['colors'][$c['code']] ?? null) : null);
                    $label = $c['code'] ? ($c['code'] . ' · ' . $c['name']) : $c['name'];
                @endphp
                <div class="color-pick-item {{ $isActive ? 'active' : '' }}"
                     data-name="{{ $c['name'] }}"
                     data-code="{{ $c['code'] ?? '' }}"
                     data-style="{{ $c['style'] }}"
                     title="{{ $label }}">
                    <div class="swatch" style="{{ $c['style'] ?: 'background:#e2e8f0' }}"></div>
                    <div class="cname">{{ $c['code'] ?: $c['name'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="stock-wrap">
        <div class="color-stock-list">
            @foreach (($sg['colors'] ?? []) as $colorName => $stock)
                @php
                    $cMeta = collect($flatColors)->first(function ($c) use ($colorName) {
                        return $c['name'] === $colorName || ($c['code'] ?? '') === $colorName;
                    });
                    $st = $cMeta['style'] ?? 'background:#e2e8f0';
                    $code = $cMeta['code'] ?? '';
                    $displayName = $cMeta['name'] ?? $colorName;
                    // Luôn lưu theo name chuẩn trong bảng màu (nếu tìm thấy)
                    $saveName = $cMeta['name'] ?? $colorName;
                @endphp
                <div class="color-stock-row" data-name="{{ $saveName }}">
                    <span class="mini-swatch" style="{{ $st }}"></span>
                    <span class="flex-grow-1">
                        @if ($code)
                            <strong>{{ $code }}</strong>
                            <span class="text-muted">· {{ $displayName }}</span>
                        @else
                            {{ $displayName }}
                        @endif
                    </span>
                    <label class="mb-0 small text-muted">Tồn</label>
                    <input type="number" min="0" class="form-control form-control-sm color-stock-input"
                           name="sizes[{{ $si }}][color_stocks][{{ $saveName }}]" value="{{ $stock }}">
                    <input type="hidden" name="sizes[{{ $si }}][colors][]" value="{{ $saveName }}">
                </div>
            @endforeach
        </div>
    </div>
</div>
