@extends('layouts.admin')

@section('title', 'Sửa sản phẩm')

@section('content')

<style>
.size-block {
    border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;
    margin-bottom: 14px; background: #fff;
}
.size-block-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.size-block-head .title { font-weight: 600; color: #0f172a; }
.color-pick-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(72px, 1fr));
    gap: 8px; margin-top: 8px;
}
.color-pick-item {
    border: 2px solid #e2e8f0; border-radius: 8px; padding: 4px;
    cursor: pointer; text-align: center; background: #fff;
    transition: border-color .15s, box-shadow .15s;
}
.color-pick-item:hover { border-color: #94a3b8; }
.color-pick-item.active {
    border-color: #0d6efd; box-shadow: 0 0 0 2px rgba(13,110,253,.35);
}
.color-stock-row {
    background: #f8fafc; border-radius: 8px; padding: 6px 10px !important;
    margin-bottom: 4px; border: 1px solid #e2e8f0;
}
.color-stock-row .mini-swatch {
    width: 28px !important; height: 28px !important; border-radius: 6px;
}
.color-pick-item .swatch {
    width: 100%; height: 40px; border-radius: 5px;
    border: 1px solid rgba(0,0,0,.08);
    background-size: cover; background-position: center;
}
.color-pick-item .cname {
    font-size: 0.65rem; color: #64748b; margin-top: 3px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.color-stock-list { margin-top: 10px; }
.color-stock-row {
    display: flex; align-items: center; gap: 8px;
    padding: 4px 0; font-size: 0.85rem;
}
.color-stock-row .mini-swatch {
    width: 20px; height: 20px; border-radius: 4px; border: 1px solid #cbd5e1;
    background-size: cover; flex-shrink: 0;
}
.color-stock-row input { width: 90px; }

/* Image Manager Styles */
.image-box-wrapper {
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    padding: 14px;
}
.image-preview-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 8px;
}
.image-preview-item {
    position: relative;
    width: 86px;
    height: 86px;
    border-radius: 8px;
    overflow: hidden;
    border: 1.5px solid #e2e8f0;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    transition: transform 0.15s, border-color 0.15s, opacity 0.2s;
}
.image-preview-item:hover {
    transform: translateY(-2px);
    border-color: #94a3b8;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.image-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.image-preview-item .btn-remove-img {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #ef4444;
    color: #fff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.25);
    transition: background 0.15s, transform 0.15s;
    z-index: 5;
    padding: 0;
    line-height: 1;
}
.image-preview-item .btn-remove-img:hover {
    background: #dc2626;
    transform: scale(1.15);
}
.image-preview-item.marked-deleted {
    border-color: #ef4444;
    background: #fef2f2;
}
.image-preview-item.marked-deleted img {
    opacity: 0.35;
    filter: grayscale(100%);
}
.image-preview-item .deleted-overlay {
    position: absolute;
    inset: 0;
    background: rgba(239, 68, 68, 0.7);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 600;
    z-index: 4;
    text-align: center;
    padding: 2px;
}
.image-preview-item .deleted-overlay .undo-btn {
    font-size: 0.65rem;
    background: #fff;
    color: #ef4444;
    border: none;
    border-radius: 4px;
    padding: 2px 6px;
    margin-top: 3px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    transition: background 0.1s, transform 0.1s;
}
.image-preview-item .deleted-overlay .undo-btn:hover {
    background: #f1f5f9;
    transform: scale(1.05);
}
.image-preview-item .img-badge {
    position: absolute;
    bottom: 3px;
    left: 3px;
    font-size: 0.6rem;
    padding: 1px 5px;
    border-radius: 3px;
    font-weight: 600;
    pointer-events: none;
    z-index: 3;
    line-height: 1.2;
}
.image-preview-item .badge-main {
    background: rgba(15, 23, 42, 0.85);
    color: #fff;
}
.image-preview-item .badge-new {
    background: rgba(34, 197, 94, 0.9);
    color: #fff;
}
</style>

<div class="container">
    <div class="row mb-3">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h2>Sửa sản phẩm</h2>
            <a class="btn btn-secondary" href="{{ route('admin.products.index') }}">Quay lại</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header bg-dark text-white"><strong>Thông tin chung</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label"><strong>Danh mục <span class="text-danger">*</span></strong></label>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <select id="cat_level1" class="form-select" required>
                                    <option value="">-- Danh mục cha --</option>
                                    @foreach ($categoryTree as $root)
                                        <option value="{{ $root->id }}">{{ $root->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4" id="wrap_cat_level2">
                                <select id="cat_level2" class="form-select">
                                    <option value="">-- Chọn danh mục cha trước --</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="wrap_cat_level3" style="display:none;">
                                <select id="cat_level3" class="form-select">
                                    <option value="">-- Danh mục cấp 3 --</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id', $product->category_id) }}" required>
                        <input type="hidden" name="sub_category_id" id="sub_category_id" value="{{ old('sub_category_id', $product->sub_category_id) }}">
                        <input type="hidden" name="sub_sub_category_id" id="sub_sub_category_id" value="{{ old('sub_sub_category_id', $product->sub_sub_category_id) }}">
                        <div class="form-text">Chọn danh mục cha, sau đó chọn danh mục con (nếu có).</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><strong>Mã sản phẩm (SKU)</strong></label>
                        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Tên sản phẩm <span class="text-danger">*</span></strong></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><strong>Chất liệu</strong></label>
                        <input type="text" name="material" class="form-control" value="{{ old('material', $product->material) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><strong>Phong cách</strong></label>
                        <input type="text" name="style" class="form-control" value="{{ old('style', $product->style) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><strong>Bảo hành</strong></label>
                        <input type="text" name="warranty" class="form-control" value="{{ old('warranty', $product->warranty) }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Mô tả</strong></label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Ưu điểm</strong></label>
                    <textarea name="advantages" class="form-control" rows="3">{{ old('advantages', $product->advantages) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Hướng dẫn sử dụng</strong></label>
                    <textarea name="usage_guide" class="form-control" rows="3">{{ old('usage_guide', $product->usage_guide) }}</textarea>
                </div>
                <!-- Ảnh chính -->
                <div class="mb-4">
                    <label class="form-label d-flex align-items-center justify-content-between">
                        <strong>Ảnh chính</strong>
                        <span class="text-muted small">Ảnh đại diện sản phẩm</span>
                    </label>
                    <div class="image-box-wrapper">
                        @if ($product->image)
                            <div class="mb-2" id="wrap-old-main-image">
                                <div class="text-muted small mb-1 fw-semibold">Ảnh chính hiện tại (bấm <span class="badge bg-danger rounded-pill px-1">x</span> để xóa):</div>
                                <div class="image-preview-grid">
                                    <div class="image-preview-item" id="old-main-image-item">
                                        <img src="{{ $product->image_url }}" alt="Ảnh chính" onerror="this.onerror=null;this.src='https://placehold.co/200x200?text=No+Image';">
                                        <span class="img-badge badge-main">Ảnh chính</span>
                                        <button type="button" class="btn-remove-img" id="btn-delete-main-img" title="Xóa ảnh chính này">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <input type="hidden" name="delete_main_image" id="input-delete-main-img" value="0">
                                        <div class="deleted-overlay d-none" id="overlay-main-deleted">
                                            <span style="font-size:0.68rem;"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                                            <button type="button" class="undo-btn" id="btn-undo-main-img">Hoàn tác</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-2">
                            <label class="form-label small fw-semibold text-secondary">
                                {{ $product->image ? 'Thay thế bằng ảnh mới:' : 'Tải lên ảnh chính:' }}
                            </label>
                            <input type="file" name="image" id="input-main-image" class="form-control form-control-sm" accept="image/*">
                            <div class="form-text text-muted" style="font-size:0.75rem;">
                                <i class="bi bi-info-circle me-1"></i>Sau khi chọn ảnh, bấm <strong>"Cập nhật sản phẩm"</strong> ở cuối trang để lưu thật vào hệ thống.
                            </div>
                        </div>

                        <!-- Khung xem trước ảnh chính mới upload nhỏ nhỏ -->
                        <div id="new-main-image-preview-wrap" class="d-none mt-2">
                            <div class="text-muted small mb-1 fw-semibold">Ảnh mới đã chọn:</div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="image-preview-item" id="new-main-image-item">
                                    <img id="new-main-image-thumb" src="" alt="Ảnh mới">
                                    <span class="img-badge badge-new">Mới</span>
                                    <button type="button" class="btn-remove-img" id="btn-cancel-new-main" title="Hủy chọn ảnh này">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div class="d-flex flex-column">
                                    <span id="new-main-filename" class="small fw-semibold text-truncate" style="max-width:260px;"></span>
                                    <span id="new-main-filesize" class="text-muted" style="font-size:0.75rem;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thư viện ảnh (Gallery) -->
                <div class="mb-3">
                    <label class="form-label d-flex align-items-center justify-content-between">
                        <strong>Thư viện ảnh (Gallery)</strong>
                        <span class="text-muted small">Nhiều góc chụp hoặc chi tiết sản phẩm</span>
                    </label>
                    <div class="image-box-wrapper">
                        <!-- Danh sách ảnh gallery cũ -->
                        @if ($product->images->count())
                            <div class="mb-3">
                                <div class="text-muted small mb-1 fw-semibold">
                                    Ảnh gallery hiện tại (bấm <span class="badge bg-danger rounded-pill px-1">x</span> trên ảnh để xóa):
                                </div>
                                <div class="image-preview-grid" id="old-gallery-grid">
                                    @foreach ($product->images as $img)
                                        <div class="image-preview-item" id="gallery-item-{{ $img->id }}">
                                            <img src="{{ $img->url }}" alt="Gallery image" onerror="this.onerror=null;this.src='https://placehold.co/200x200?text=No+Image';">
                                            <button type="button" class="btn-remove-img btn-delete-gallery-old" data-id="{{ $img->id }}" title="Xóa ảnh này">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <input type="checkbox" name="delete_images[]" value="{{ $img->id }}" id="del-chk-{{ $img->id }}" class="d-none">
                                            <div class="deleted-overlay d-none" id="overlay-gallery-{{ $img->id }}">
                                                <span style="font-size:0.68rem;"><i class="bi bi-trash3-fill"></i> Đã xóa</span>
                                                <button type="button" class="undo-btn btn-undo-gallery-old" data-id="{{ $img->id }}">Hoàn tác</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-muted small mb-2">Chưa có ảnh trong thư viện.</p>
                        @endif

                        <!-- Chọn thêm ảnh gallery mới -->
                        <div class="mt-2">
                            <label class="form-label small fw-semibold text-secondary">Thêm ảnh vào thư viện:</label>
                            <input type="file" name="gallery[]" id="input-gallery-images" class="form-control form-control-sm" accept="image/*" multiple>
                            <div class="form-text small">Có thể giữ Ctrl / Shift để chọn nhiều ảnh cùng lúc.</div>
                        </div>

                        <!-- Khung xem trước các ảnh gallery mới upload nhỏ nhỏ -->
                        <div id="new-gallery-preview-wrap" class="d-none mt-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted small fw-semibold">Các ảnh mới đã chọn (<span id="new-gallery-count">0</span> ảnh):</span>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size:0.75rem;" id="btn-clear-all-gallery">Hủy tất cả</button>
                            </div>
                            <div class="image-preview-grid" id="new-gallery-grid"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>Biến thể (Size + nhiều màu)</strong>
                <button type="button" class="btn btn-light btn-sm" id="btnAddSize">+ Thêm size</button>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Mỗi size nhập 1 lần kích thước + giá. Chọn <strong>nhiều màu</strong> bằng ô màu (giống bảng màu), không cần thêm dòng size cho từng màu.
                </p>
                <div id="sizeBlocks">
                    @php
                        // Gộp variant theo size (label + kích thước)
                        $sizeGroups = [];
                        foreach ($product->variants as $v) {
                            $key = implode('|', [
                                $v->size_label ?? '',
                                $v->width ?? '',
                                $v->depth ?? '',
                                $v->height ?? '',
                                $v->desktop_width ?? '',
                            ]);
                            if (!isset($sizeGroups[$key])) {
                                $sizeGroups[$key] = [
                                    'size_label' => $v->size_label,
                                    'width' => $v->width,
                                    'depth' => $v->depth,
                                    'height' => $v->height,
                                    'desktop_width' => $v->desktop_width,
                                    'price' => $v->price,
                                    'price_old' => $v->price_old,
                                    'colors' => [], // name => stock
                                ];
                            }
                            if ($v->color) {
                                $sizeGroups[$key]['colors'][$v->color] = (int) ($v->stock ?? 0);
                            }
                            // Giữ giá nếu chưa có
                            if (!$sizeGroups[$key]['price'] && $v->price) {
                                $sizeGroups[$key]['price'] = $v->price;
                                $sizeGroups[$key]['price_old'] = $v->price_old;
                            }
                        }
                        if (empty($sizeGroups)) {
                            $sizeGroups[''] = [
                                'size_label' => '', 'width' => '', 'depth' => '', 'height' => '',
                                'desktop_width' => '', 'price' => '', 'price_old' => '', 'colors' => [],
                            ];
                        }
                        $flatColors = [];
                        foreach ($colors as $gKey => $list) {
                            foreach ($list as $c) {
                                $flatColors[] = [
                                    'name' => $c->name,
                                    'code' => $c->code,
                                    'hex' => $c->hex,
                                    'style' => $c->swatch_style,
                                ];
                            }
                        }
                    @endphp
                    @foreach ($sizeGroups as $si => $sg)
                        @include('admin.products._size_block', ['si' => $loop->index, 'sg' => $sg, 'flatColors' => $flatColors])
                    @endforeach
                </div>
            </div>
        </div>

        <div class="text-center mb-4">

            <button type="submit" class="btn btn-primary px-5">Cập nhật</button>
        </div>
    </form>
</div>


@php
    $flatColorsJson = collect($colors)->flatten(1)->map(function ($c) {
        return [
            'name' => $c->name,
            'code' => $c->code,
            'hex' => $c->hex,
            'style' => $c->swatch_style,
        ];
    })->values();
@endphp
<script>
window.__flatColors = @json($flatColorsJson);
let sizeIndex = {{ count($sizeGroups) }};

function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

function isColorSelected(selectedMap, c) {
    if (!selectedMap) return false;
    if (selectedMap[c.name] !== undefined) return true;
    if (c.code && selectedMap[c.code] !== undefined) return true;
    return false;
}
function getStockForColor(selectedMap, c) {
    if (!selectedMap) return 0;
    if (selectedMap[c.name] !== undefined) return selectedMap[c.name];
    if (c.code && selectedMap[c.code] !== undefined) return selectedMap[c.code];
    return 0;
}
function buildColorGridHtml(si, selectedMap) {
    selectedMap = selectedMap || {};
    let html = '<div class="color-pick-grid">';
    (window.__flatColors || []).forEach(function (c) {
        const active = isColorSelected(selectedMap, c);
        const title = (c.code ? c.code + ' · ' : '') + c.name;
        html += '<div class="color-pick-item' + (active ? ' active' : '') + '" data-name="' + attrEscape(c.name) + '" data-code="' + attrEscape(c.code || '') + '" data-style="' + attrEscape(c.style || '') + '" title="' + attrEscape(title) + '">';
        html += '<div class="swatch" style="' + (c.style || 'background:#e2e8f0') + '"></div>';
        html += '<div class="cname">' + escapeHtml(c.code || c.name) + '</div>';
        html += '</div>';
    });
    html += '</div>';
    return html;
}

function attrEscape(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
}
function buildStockListHtml(si, selectedMap) {
    selectedMap = selectedMap || {};
    let html = '<div class="color-stock-list">';
    Object.keys(selectedMap).forEach(function (name) {
        const stock = selectedMap[name];
        const c = (window.__flatColors || []).find(x => x.name === name || x.code === name) || {};
        const style = c.style || 'background:#e2e8f0';
        const saveName = c.name || name;
        const code = c.code || '';
        const label = code
            ? ('<strong>' + escapeHtml(code) + '</strong> <span class="text-muted">· ' + escapeHtml(c.name || name) + '</span>')
            : escapeHtml(c.name || name);
        html += '<div class="color-stock-row" data-name="' + attrEscape(saveName) + '">';
        html += '<span class="mini-swatch" style="' + style + '"></span>';
        html += '<span class="flex-grow-1">' + label + '</span>';
        html += '<label class="mb-0 small text-muted">Tồn</label>';
        html += '<input type="number" min="0" class="form-control form-control-sm color-stock-input" name="sizes[' + si + '][color_stocks][' + attrEscape(saveName) + ']" value="' + (stock || 0) + '">';
        html += '<input type="hidden" name="sizes[' + si + '][colors][]" value="' + attrEscape(saveName) + '">';
        html += '</div>';
    });
    html += '</div>';
    return html;
}

function buildSizeBlock(si, data) {
    data = data || {};
    const selected = data.colors || {};
    return `
    <div class="size-block" data-si="${si}">
        <div class="size-block-head">
            <span class="title">Size #${si + 1}</span>
            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-size">Xóa size</button>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-md-2"><label class="form-label small mb-0">Nhãn size</label>
                <input type="text" name="sizes[${si}][size_label]" class="form-control form-control-sm" value="${escapeHtml(data.size_label || '')}" placeholder="BGD16_1"></div>
            <div class="col-md-1"><label class="form-label small mb-0">Dài</label>
                <input type="number" step="0.01" name="sizes[${si}][width]" class="form-control form-control-sm" value="${data.width ?? ''}"></div>
            <div class="col-md-1"><label class="form-label small mb-0">Sâu</label>
                <input type="number" step="0.01" name="sizes[${si}][depth]" class="form-control form-control-sm" value="${data.depth ?? ''}"></div>
            <div class="col-md-1"><label class="form-label small mb-0">Cao</label>
                <input type="number" step="0.01" name="sizes[${si}][height]" class="form-control form-control-sm" value="${data.height ?? ''}"></div>
            <div class="col-md-1"><label class="form-label small mb-0">Mặt bàn</label>
                <input type="number" step="0.01" name="sizes[${si}][desktop_width]" class="form-control form-control-sm" value="${data.desktop_width ?? ''}"></div>
            <div class="col-md-2"><label class="form-label small mb-0">Giá</label>
                <input type="number" min="0" step="1000" name="sizes[${si}][price]" class="form-control form-control-sm" value="${data.price ?? ''}"></div>
            <div class="col-md-2"><label class="form-label small mb-0">Giá cũ</label>
                <input type="number" min="0" step="1000" name="sizes[${si}][price_old]" class="form-control form-control-sm" value="${data.price_old ?? ''}"></div>
        </div>
        <div class="small fw-semibold mb-1">Chọn màu (bấm ô màu — chọn nhiều):</div>
        <div class="color-grid-wrap">${buildColorGridHtml(si, selected)}</div>
        <div class="stock-wrap">${buildStockListHtml(si, selected)}</div>
    </div>`;
}

document.getElementById('btnAddSize')?.addEventListener('click', function () {
    const wrap = document.getElementById('sizeBlocks');
    wrap.insertAdjacentHTML('beforeend', buildSizeBlock(sizeIndex, {}));
    sizeIndex++;
});

document.getElementById('sizeBlocks')?.addEventListener('click', function (e) {
    const item = e.target.closest('.color-pick-item');
    if (item) {
        const block = item.closest('.size-block');
        const si = block.dataset.si;
        item.classList.toggle('active');
        // rebuild stock list from active items
        const selected = {};
        block.querySelectorAll('.color-pick-item.active').forEach(function (el) {
            const name = el.dataset.name;
            const existing = block.querySelector('.color-stock-row[data-name="' + name.replace(/"/g, '\\"') + '"] input.color-stock-input');
            selected[name] = existing ? existing.value : 0;
        });
        block.querySelector('.stock-wrap').innerHTML = buildStockListHtml(si, selected);
        return;
    }
    if (e.target.classList.contains('btn-remove-size')) {
        const blocks = document.querySelectorAll('#sizeBlocks .size-block');
        if (blocks.length <= 1) return;
        e.target.closest('.size-block').remove();
    }
});
</script>

@php
    $__catTree = $categoryTree->map(function ($root) {
        return [
            'id' => $root->id,
            'name' => $root->name,
            'children' => $root->subCategories->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'children' => $c->subSubCategories->map(function ($g) {
                        return ['id' => $g->id, 'name' => $g->name, 'children' => []];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    })->values()->all();
@endphp
<script>window.__categoryTree = @json($__catTree);</script>

<script>
(function () {
    const tree = window.__categoryTree || [];
    const level1 = document.getElementById('cat_level1');
    const level2 = document.getElementById('cat_level2');
    const level3 = document.getElementById('cat_level3');
    const wrap2 = document.getElementById('wrap_cat_level2');
    const wrap3 = document.getElementById('wrap_cat_level3');
    const hidden = document.getElementById('category_id');
    if (!level1 || !hidden) return;

    const initialId = hidden.value ? String(hidden.value) : '';

    function findPath(id) {
        id = String(id);
        for (const r of tree) {
            if (String(r.id) === id) return [r.id, null, null];
            for (const c of (r.children || [])) {
                if (String(c.id) === id) return [r.id, c.id, null];
                for (const g of (c.children || [])) {
                    if (String(g.id) === id) return [r.id, c.id, g.id];
                }
            }
        }
        return [null, null, null];
    }

    function fillSelect(select, items, placeholder) {
        select.innerHTML = '';
        const opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = placeholder;
        select.appendChild(opt0);
        (items || []).forEach(function (item) {
            const o = document.createElement('option');
            o.value = item.id;
            o.textContent = item.name;
            select.appendChild(o);
        });
    }

    function getRoot(id) {
        return tree.find(r => String(r.id) === String(id));
    }
    function getChild(rootId, childId) {
        const r = getRoot(rootId);
        if (!r) return null;
        return (r.children || []).find(c => String(c.id) === String(childId));
    }

    function syncHidden() {
        const hidSub = document.getElementById('sub_category_id');
        const hidSubSub = document.getElementById('sub_sub_category_id');
        // Cấp 1 luôn là category_id
        hidden.value = level1.value || '';
        // Cấp 2
        if (hidSub) hidSub.value = (level2 && level2.value) ? level2.value : '';
        // Cấp 3
        if (hidSubSub) hidSubSub.value = (level3 && level3.value) ? level3.value : '';
    }

    function onLevel1() {
        const root = getRoot(level1.value);
        if (wrap3) wrap3.style.display = 'none';
        if (level3) level3.value = '';
        if (root && root.children && root.children.length) {
            fillSelect(level2, root.children, '-- Danh mục con --');
            wrap2.style.display = '';
            level2.disabled = false;
        } else {
            wrap2.style.display = 'none';
            if (level2) {
                level2.innerHTML = '<option value="">-- Không có danh mục con --</option>';
                level2.value = '';
            }
        }
        syncHidden();
    }

    function onLevel2() {
        const child = getChild(level1.value, level2.value);
        if (child && child.children && child.children.length) {
            fillSelect(level3, child.children, '-- Danh mục cấp 3 --');
            wrap3.style.display = '';
        } else {
            if (wrap3) wrap3.style.display = 'none';
            if (level3) level3.value = '';
        }
        syncHidden();
    }

    level1.addEventListener('change', onLevel1);
    if (level2) level2.addEventListener('change', onLevel2);
    if (level3) level3.addEventListener('change', syncHidden);

    // Khởi tạo từ 3 cấp đã lưu trên product
    const initL1 = document.getElementById('category_id')?.value || '';
    const initL2 = document.getElementById('sub_category_id')?.value || '';
    const initL3 = document.getElementById('sub_sub_category_id')?.value || '';

    if (initL1) {
        level1.value = initL1;
        onLevel1();
        if (initL2 && level2) {
            level2.value = initL2;
            onLevel2();
            if (initL3 && level3) {
                level3.value = initL3;
            }
        }
        syncHidden();
    }

    // Đảm bảo hidden category được set trước khi submit
    const form = level1.closest('form');
    if (form) {
        form.addEventListener('submit', function () {
            syncHidden();
        });
    }
})();
</script>


<script>
(function () {
    function hexOf(select) {
        const opt = select.options[select.selectedIndex];
        return (opt && opt.dataset.hex) ? opt.dataset.hex : '';
    }
    function updateSwatch(select) {
        const cell = select.closest('.color-cell') || select.parentElement;
        let sw = cell.querySelector('.color-swatch');
        if (!sw) {
            sw = document.createElement('span');
            sw.className = 'color-swatch';
            cell.appendChild(sw);
        }
        const hex = hexOf(select);
        if (hex) {
            sw.style.background = hex;
            sw.classList.remove('is-empty');
            sw.title = hex;
        } else {
            sw.style.background = '';
            sw.classList.add('is-empty');
            sw.title = '';
        }
    }
    function wrapColorSelect(select) {
        if (select.closest('.color-cell')) {
            updateSwatch(select);
            return;
        }
        const wrap = document.createElement('div');
        wrap.className = 'color-cell';
        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(select);
        const sw = document.createElement('span');
        sw.className = 'color-swatch is-empty';
        wrap.appendChild(sw);
        select.addEventListener('change', function () { updateSwatch(select); });
        updateSwatch(select);
    }
    window.initColorSelects = function (root) {
        (root || document).querySelectorAll('select.color-select').forEach(wrapColorSelect);
    };
    document.addEventListener('DOMContentLoaded', function () {
        window.initColorSelects(document);
    });
    // Observer: when new variant row added
    const body = document.getElementById('variantsBody');
    if (body) {
        const obs = new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                m.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) window.initColorSelects(node);
                });
            });
        });
        obs.observe(body, { childList: true, subtree: true });
    }
})();
</script>

<script>
// ===== QUẢN LÝ XÓA ẢNH CŨ VÀ XEM TRƯỚC ẢNH MỚI UPLOAD =====
(function () {
    // 1. Xóa ảnh chính cũ
    const btnDelMain = document.getElementById('btn-delete-main-img');
    const btnUndoMain = document.getElementById('btn-undo-main-img');
    const inputDelMain = document.getElementById('input-delete-main-img');
    const cardOldMain = document.getElementById('old-main-image-item');
    const overlayMain = document.getElementById('overlay-main-deleted');

    if (btnDelMain) {
        btnDelMain.addEventListener('click', function () {
            inputDelMain.value = '1';
            cardOldMain.classList.add('marked-deleted');
            overlayMain.classList.remove('d-none');
        });
    }
    if (btnUndoMain) {
        btnUndoMain.addEventListener('click', function () {
            inputDelMain.value = '0';
            cardOldMain.classList.remove('marked-deleted');
            overlayMain.classList.add('d-none');
        });
    }

    // 2. Xóa ảnh gallery cũ
    document.querySelectorAll('.btn-delete-gallery-old').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const chk = document.getElementById('del-chk-' + id);
            const card = document.getElementById('gallery-item-' + id);
            const overlay = document.getElementById('overlay-gallery-' + id);
            if (chk) chk.checked = true;
            if (card) card.classList.add('marked-deleted');
            if (overlay) overlay.classList.remove('d-none');
        });
    });
    document.querySelectorAll('.btn-undo-gallery-old').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const chk = document.getElementById('del-chk-' + id);
            const card = document.getElementById('gallery-item-' + id);
            const overlay = document.getElementById('overlay-gallery-' + id);
            if (chk) chk.checked = false;
            if (card) card.classList.remove('marked-deleted');
            if (overlay) overlay.classList.add('d-none');
        });
    });

    function formatBytes(bytes) {
        if (!bytes) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // 3. Xem trước ảnh chính mới upload
    const inputMain = document.getElementById('input-main-image');
    const wrapMainPreview = document.getElementById('new-main-image-preview-wrap');
    const imgMainThumb = document.getElementById('new-main-image-thumb');
    const txtMainName = document.getElementById('new-main-filename');
    const txtMainSize = document.getElementById('new-main-filesize');
    const btnCancelMain = document.getElementById('btn-cancel-new-main');

    if (inputMain) {
        inputMain.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                imgMainThumb.src = URL.createObjectURL(file);
                txtMainName.textContent = file.name;
                txtMainSize.textContent = formatBytes(file.size);
                wrapMainPreview.classList.remove('d-none');
            } else {
                wrapMainPreview.classList.add('d-none');
                imgMainThumb.src = '';
            }
        });
    }
    if (btnCancelMain) {
        btnCancelMain.addEventListener('click', function () {
            if (inputMain) inputMain.value = '';
            wrapMainPreview.classList.add('d-none');
            imgMainThumb.src = '';
        });
    }

    // 4. Xem trước nhiều ảnh gallery mới upload & nút "x" để gỡ từng ảnh
    const inputGallery = document.getElementById('input-gallery-images');
    const wrapGalleryPreview = document.getElementById('new-gallery-preview-wrap');
    const gridGallery = document.getElementById('new-gallery-grid');
    const countGallery = document.getElementById('new-gallery-count');
    const btnClearAllGallery = document.getElementById('btn-clear-all-gallery');

    let galleryFilesArray = [];

    function renderGalleryPreviews() {
        gridGallery.innerHTML = '';
        if (galleryFilesArray.length === 0) {
            wrapGalleryPreview.classList.add('d-none');
            countGallery.textContent = '0';
            return;
        }

        wrapGalleryPreview.classList.remove('d-none');
        countGallery.textContent = galleryFilesArray.length;

        galleryFilesArray.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'image-preview-item';
            item.title = file.name + ' (' + formatBytes(file.size) + ')';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;

            const badge = document.createElement('span');
            badge.className = 'img-badge badge-new';
            badge.textContent = '#' + (index + 1);

            const btnRemove = document.createElement('button');
            btnRemove.type = 'button';
            btnRemove.className = 'btn-remove-img';
            btnRemove.title = 'Bỏ chọn ảnh này';
            btnRemove.innerHTML = '<i class="bi bi-x-lg"></i>';
            btnRemove.addEventListener('click', function () {
                galleryFilesArray.splice(index, 1);
                syncGalleryInput();
                renderGalleryPreviews();
            });

            item.appendChild(img);
            item.appendChild(badge);
            item.appendChild(btnRemove);
            gridGallery.appendChild(item);
        });
    }

    function syncGalleryInput() {
        if (!inputGallery) return;
        const dt = new DataTransfer();
        galleryFilesArray.forEach(file => dt.items.add(file));
        inputGallery.files = dt.files;
    }

    if (inputGallery) {
        inputGallery.addEventListener('change', function () {
            if (this.files && this.files.length) {
                Array.from(this.files).forEach(f => galleryFilesArray.push(f));
                syncGalleryInput();
                renderGalleryPreviews();
            }
        });
    }

    if (btnClearAllGallery) {
        btnClearAllGallery.addEventListener('click', function () {
            galleryFilesArray = [];
            if (inputGallery) inputGallery.value = '';
            renderGalleryPreviews();
        });
    }
})();
</script>

@endsection
