@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h2>Thêm danh mục</h2>
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

    <form action="{{ route('admin.categories.store') }}" method="POST" id="categoryForm">
        @csrf

        <div class="card mb-3">
            <div class="card-header fw-bold">Danh mục chính</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><strong>Tên danh mục</strong></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control"
                           placeholder="VD: Bàn Văn Phòng" required>
                </div>
                <div class="mb-0">
                    <label class="form-label"><strong>Mô tả</strong></label>
                    <textarea class="form-control" name="description" rows="2"
                              placeholder="Mô tả (tuỳ chọn)">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold">Danh mục con</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddChild">
                    + Thêm danh mục con
                </button>
            </div>
            <div class="card-body" id="childrenBox">
                {{-- JS sẽ thêm dòng vào đây --}}
                <p class="text-muted mb-0" id="childrenEmpty">Chưa có danh mục con. Bấm "Thêm danh mục con" để thêm.</p>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary px-4">Lưu</button>
        </div>
    </form>
</div>

<script>
(function () {
    let childIndex = 0;
    const box = document.getElementById('childrenBox');
    const emptyHint = document.getElementById('childrenEmpty');

    function hideEmpty() {
        if (emptyHint) emptyHint.style.display = 'none';
    }

    function addChild(prefillName) {
        hideEmpty();
        const idx = childIndex++;
        const wrap = document.createElement('div');
        wrap.className = 'border rounded p-3 mb-3 child-block';
        wrap.dataset.idx = idx;
        wrap.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <strong class="text-primary">Danh mục con #${idx + 1}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-child">Xóa</button>
            </div>
            <div class="mb-2">
                <input type="text" name="children[${idx}][name]" class="form-control"
                       placeholder="Tên danh mục con" value="${prefillName || ''}">
            </div>
            <div class="ms-3 border-start ps-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-semibold">Danh mục cấp 3 (con của mục này)</small>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-add-grand" data-child="${idx}">
                        + Thêm cấp 3
                    </button>
                </div>
                <div class="grand-box" data-child="${idx}"></div>
            </div>
        `;
        box.appendChild(wrap);

        wrap.querySelector('.btn-remove-child').addEventListener('click', function () {
            wrap.remove();
        });
        wrap.querySelector('.btn-add-grand').addEventListener('click', function () {
            addGrand(idx, wrap.querySelector('.grand-box'));
        });
    }

    let grandCounters = {};

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

    document.getElementById('btnAddChild').addEventListener('click', function () {
        addChild('');
    });
})();
</script>
@endsection
