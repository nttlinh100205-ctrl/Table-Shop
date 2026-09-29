@extends('layouts.admin')

@section('title', 'Thêm sản phẩm')

@section('content')

<style>
.color-cell { display: flex; align-items: center; gap: 6px; min-width: 140px; }
.color-cell .color-select { flex: 1; min-width: 110px; }
.color-swatch {
    width: 22px; height: 22px; border-radius: 4px;
    border: 1px solid #ccc; flex-shrink: 0;
    background: #fff;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,.06);
}
.color-swatch.is-empty { background: repeating-conic-gradient(#eee 0% 25%, #fff 0% 50%) 50% / 8px 8px; }
.color-option-preview {
    display: inline-block; width: 12px; height: 12px;
    border-radius: 2px; border: 1px solid #bbb; margin-right: 6px;
    vertical-align: middle;
}
</style>

<div class="container">
    <div class="row mb-3">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h2>Thêm sản phẩm mới</h2>
            <a class="btn btn-secondary" href="{{ route('admin.products.index') }}">Quay lại</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

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
                        <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id') }}" required>
                        <input type="hidden" name="sub_category_id" id="sub_category_id" value="{{ old('sub_category_id') }}">
                        <input type="hidden" name="sub_sub_category_id" id="sub_sub_category_id" value="{{ old('sub_sub_category_id') }}">
                        <div class="form-text">Chọn danh mục cha, sau đó chọn danh mục con (nếu có).</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><strong>Mã sản phẩm (SKU)</strong></label>
                        <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" placeholder="VD: BGĐ16-G">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Tên sản phẩm <span class="text-danger">*</span></strong></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><strong>Chất liệu</strong></label>
                        <input type="text" name="material" class="form-control" value="{{ old('material') }}" placeholder="MDF phủ Melamine, chân sắt...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><strong>Phong cách</strong></label>
                        <input type="text" name="style" class="form-control" value="{{ old('style') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><strong>Bảo hành</strong></label>
                        <input type="text" name="warranty" class="form-control" value="{{ old('warranty') }}" placeholder="12 Tháng">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Mô tả</strong></label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Mô tả chung về sản phẩm...">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Ưu điểm</strong></label>
                    <textarea name="advantages" class="form-control" rows="3" placeholder="Mỗi dòng một ưu điểm...">{{ old('advantages') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Hướng dẫn sử dụng</strong></label>
                    <textarea name="usage_guide" class="form-control" rows="3" placeholder="Hướng dẫn lắp đặt, sử dụng, bảo quản...">{{ old('usage_guide') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Ảnh chính</strong></label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label"><strong>Thư viện ảnh (nhiều ảnh)</strong></label>
                    <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                    <div class="form-text">Có thể chọn nhiều ảnh. Ảnh sẽ hiện dạng thumbnail dưới ảnh chính.</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>Biến thể (Size + Mặt bàn + Màu + Giá)</strong>
                <button type="button" class="btn btn-light btn-sm" id="btnAddVariant">+ Thêm size</button>
            </div>
            <div class="card-body">
                <p class="text-muted small">VD: 160 x 120 x 75cm (mặt bàn 60cm) — mỗi size một giá.</p>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="variantsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Nhãn size</th>
                                <th>Dài</th>
                                <th>Sâu</th>
                                <th>Cao</th>
                                <th>Mặt bàn</th>
                                <th>Màu</th>
                                <th>Giá *</th>
                                <th>Giá cũ</th>
                                <th>Tồn</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="variantsBody">
                            <tr class="variant-row">
                                <td><input type="text" name="variants[0][size_label]" class="form-control form-control-sm" placeholder="1m6 x 1m2 x 75cm"></td>
                                <td><input type="number" name="variants[0][width]" class="form-control form-control-sm" step="0.01" placeholder="160"></td>
                                <td><input type="number" name="variants[0][depth]" class="form-control form-control-sm" step="0.01" placeholder="120"></td>
                                <td><input type="number" name="variants[0][height]" class="form-control form-control-sm" step="0.01" placeholder="75"></td>
                                <td><input type="number" name="variants[0][desktop_width]" class="form-control form-control-sm" step="0.01" placeholder="60"></td>
                                <td>
                                    <select name="variants[0][color]" class="form-select form-select-sm color-select">
                                        <option value="">-- Chọn màu --</option>
                                        @foreach (\App\Models\Color::GROUPS as $gKey => $gLabel)
                                            @if(isset($colors[$gKey]) && $colors[$gKey]->count())
                                                <optgroup label="{{ $gLabel }}">
                                                    @foreach ($colors[$gKey] as $c)
                                                        <option value="{{ $c->name }}" data-hex="{{ $c->hex }}">{{ $c->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="variants[0][price]" class="form-control form-control-sm" min="0" step="1000" placeholder="8500000"></td>
                                <td><input type="number" name="variants[0][price_old]" class="form-control form-control-sm" min="0" step="1000" placeholder="14500000"></td>
                                <td><input type="number" name="variants[0][stock]" class="form-control form-control-sm" min="0" value="0"></td>
                                <td><button type="button" class="btn btn-danger btn-sm btn-remove-row">×</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center mb-4">
            <button type="submit" class="btn btn-success px-5">Lưu sản phẩm</button>
        </div>
    </form>
</div>


@php
    $__colorOpts = '';
    foreach (\App\Models\Color::GROUPS as $gKey => $gLabel) {
        if (isset($colors[$gKey]) && $colors[$gKey]->count()) {
            $__colorOpts .= '<optgroup label="'.e($gLabel).'">';
            foreach ($colors[$gKey] as $c) {
                $hex = e($c->hex ?? '');
                $name = e($c->name);
                $__colorOpts .= '<option value="'.$name.'" data-hex="'.$hex.'">'.$name.'</option>';
            }
            $__colorOpts .= '</optgroup>';
        }
    }
@endphp
<script>window.__colorOptionsHtml = {!! json_encode($__colorOpts) !!};</script>

<script>
let variantIndex = 1;

function buildVariantRowHtml(idx) {
    return `
        <td><input type="text" name="variants[${idx}][size_label]" class="form-control form-control-sm"></td>
        <td><input type="number" name="variants[${idx}][width]" class="form-control form-control-sm" step="0.01"></td>
        <td><input type="number" name="variants[${idx}][depth]" class="form-control form-control-sm" step="0.01"></td>
        <td><input type="number" name="variants[${idx}][height]" class="form-control form-control-sm" step="0.01"></td>
        <td><input type="number" name="variants[${idx}][desktop_width]" class="form-control form-control-sm" step="0.01"></td>
        <td><select name="variants[${idx}][color]" class="form-select form-select-sm color-select"><option value="">-- Chọn màu --</option>${window.__colorOptionsHtml || ''}</select></td>
        <td><input type="number" name="variants[${idx}][price]" class="form-control form-control-sm" min="0" step="1000"></td>
        <td><input type="number" name="variants[${idx}][price_old]" class="form-control form-control-sm" min="0" step="1000"></td>
        <td><input type="number" name="variants[${idx}][stock]" class="form-control form-control-sm" min="0" value="0"></td>
        <td><button type="button" class="btn btn-danger btn-sm btn-remove-row">×</button></td>
    `;
}

document.getElementById('btnAddVariant').addEventListener('click', function () {
    const tbody = document.getElementById('variantsBody');
    const row = document.createElement('tr');
    row.className = 'variant-row';
    row.innerHTML = buildVariantRowHtml(variantIndex);
    tbody.appendChild(row);
    variantIndex++;
});

document.getElementById('variantsBody').addEventListener('click', function (e) {
    if (e.target.classList.contains('btn-remove-row')) {
        if (document.querySelectorAll('#variantsBody .variant-row').length > 1) {
            e.target.closest('tr').remove();
        }
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

    if (initialId) {
        const path = findPath(initialId);
        const r = path[0], c = path[1], g = path[2];
        if (r) {
            level1.value = r;
            onLevel1();
            if (c && level2) {
                level2.value = c;
                onLevel2();
                if (g && level3) level3.value = g;
            }
            syncHidden();
        }
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

@endsection
