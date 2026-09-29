@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h2>Sửa danh mục</h2>
            <a class="btn btn-secondary" href="{{ route('admin.categories.index') }}">Quay lại</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Dữ liệu chưa hợp lệ.</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" id="categoryForm">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header fw-bold">Danh mục chính (Cấp 1)</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>Tên danh mục</strong></label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}"
                           class="form-control" required>
                </div>
                <div class="mb-0">
                    <label class="form-label"><strong>Mô tả</strong></label>
                    <textarea class="form-control" name="description" rows="2">{{ old('description', $category->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold">Danh mục con (Cấp 2)</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddChild">
                    + Thêm danh mục con
                </button>
            </div>
            <div class="card-body" id="childrenBox">
                @foreach ($category->subCategories as $ci => $child)
                    <div class="border rounded p-3 mb-3 child-block" data-idx="{{ $ci }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <strong class="text-primary">Danh mục con #{{ $ci + 1 }}</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-child">Xóa</button>
                        </div>
                        <input type="hidden" name="children[{{ $ci }}][id]" value="{{ $child->id }}">
                        <div class="mb-2">
                            <input type="text" name="children[{{ $ci }}][name]" class="form-control"
                                   value="{{ $child->name }}" placeholder="Tên danh mục con">
                        </div>
                        <div class="ms-3 border-start ps-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted fw-semibold">Danh mục cấp 3</small>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-add-grand"
                                        data-child="{{ $ci }}">+ Thêm cấp 3</button>
                            </div>
                            <div class="grand-box" data-child="{{ $ci }}">
                                @foreach ($child->subSubCategories as $gi => $grand)
                                    <div class="input-group input-group-sm mb-2">
                                        <input type="hidden" name="children[{{ $ci }}][children][{{ $gi }}][id]"
                                               value="{{ $grand->id }}">
                                        <input type="text" name="children[{{ $ci }}][children][{{ $gi }}][name]"
                                               class="form-control" value="{{ $grand->name }}"
                                               placeholder="Tên danh mục cấp 3">
                                        <button type="button" class="btn btn-outline-danger btn-remove-grand">Xóa</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
                @if ($category->subCategories->isEmpty())
                    <p class="text-muted mb-0" id="childrenEmpty">Chưa có danh mục con.</p>
                @endif
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary px-4">Cập nhật</button>
        </div>
    </form>
</div>

<script>
(function () {
    let childIndex = {{ $category->subCategories->count() }};
    const box = document.getElementById('childrenBox');
    const emptyHint = document.getElementById('childrenEmpty');
    const grandCounters = {};

    @foreach ($category->subCategories as $ci => $child)
        grandCounters[{{ $ci }}] = {{ $child->subSubCategories->count() }};
    @endforeach

    function hideEmpty() {
        if (emptyHint) emptyHint.style.display = 'none';
    }

    function bindRemoveChild(wrap) {
        wrap.querySelector('.btn-remove-child').addEventListener('click', function () {
            wrap.remove();
        });
    }

    function bindAddGrand(btn, childIdx, grandBox) {
        btn.addEventListener('click', function () {
            addGrand(childIdx, grandBox);
        });
    }

    function addGrand(childIdx, grandBox) {
        if (!grandCounters[childIdx]) grandCounters[childIdx] = 0;
        const g = grandCounters[childIdx]++;
        const row = document.createElement('div');
        row.className = 'input-group input-group-sm mb-2';
        row.innerHTML = `
            <input type="text" name="children[${childIdx}][children][${g}][name]"
                   class="form-control" placeholder="Tên danh mục cấp 3">
            <button type="button" class="btn btn-outline-danger btn-remove-grand">Xóa</button>
        `;
        row.querySelector('.btn-remove-grand').addEventListener('click', function () {
            row.remove();
        });
        grandBox.appendChild(row);
    }

    function addChild() {
        hideEmpty();
        const idx = childIndex++;
        grandCounters[idx] = 0;
        const wrap = document.createElement('div');
        wrap.className = 'border rounded p-3 mb-3 child-block';
        wrap.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <strong class="text-primary">Danh mục con #${idx + 1}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-child">Xóa</button>
            </div>
            <div class="mb-2">
                <input type="text" name="children[${idx}][name]" class="form-control"
                       placeholder="Tên danh mục con">
            </div>
            <div class="ms-3 border-start ps-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-semibold">Danh mục cấp 3</small>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-add-grand"
                            data-child="${idx}">+ Thêm cấp 3</button>
                </div>
                <div class="grand-box" data-child="${idx}"></div>
            </div>
        `;
        box.appendChild(wrap);
        bindRemoveChild(wrap);
        bindAddGrand(wrap.querySelector('.btn-add-grand'), idx, wrap.querySelector('.grand-box'));
    }

    document.querySelectorAll('.child-block').forEach(function (wrap) {
        bindRemoveChild(wrap);
        const idx = parseInt(wrap.dataset.idx, 10);
        const btn = wrap.querySelector('.btn-add-grand');
        const grandBox = wrap.querySelector('.grand-box');
        if (btn && grandBox) bindAddGrand(btn, idx, grandBox);
    });

    document.querySelectorAll('.btn-remove-grand').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.input-group').remove();
        });
    });

    document.getElementById('btnAddChild').addEventListener('click', addChild);
})();
</script>
@endsection
