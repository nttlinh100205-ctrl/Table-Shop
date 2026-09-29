@extends('layouts.admin')

@section('title', 'Bảng màu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="bi bi-palette me-2"></i>Bảng màu dùng chung</h4>
        <p class="text-muted small mb-0">Danh sách màu cố định của hệ thống. Sản phẩm chọn màu từ bảng này (không thêm màu riêng từng SP).</p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
@endif

<form method="GET" class="row g-2 mb-4">
    <div class="col-auto">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Tìm tên / mã...">
    </div>
    <div class="col-auto">
        <select name="group" class="form-select form-select-sm">
            <option value="">— Tất cả nhóm —</option>
            @foreach ($groups as $key => $label)
                <option value="{{ $key }}" @selected(request('group') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary btn-sm">Lọc</button>
        <a href="{{ route('admin.colors.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
</form>

@forelse ($colors as $groupKey => $items)
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
            <span>{{ $groups[$groupKey] ?? $groupKey }}</span>
            <span class="badge bg-secondary">{{ $items->count() }}</span>
        </div>
        <div class="card-body">
            <div class="color-admin-grid">
                @foreach ($items as $color)
                    <div class="color-admin-item {{ !$color->is_active ? 'is-inactive' : '' }}">
                        <div class="color-admin-swatch"
                             style="{{ $color->swatch_style }}"
                             title="{{ $color->code }} {{ $color->hex }}">
                            @if (!$color->hex && !$color->image_url)
                                <span class="text-muted small">?</span>
                            @endif
                        </div>
                        <div class="color-admin-meta">
                            <div class="fw-semibold text-truncate" title="{{ $color->name }}">{{ $color->name }}</div>
                            <div class="text-muted small">{{ $color->code ?: '—' }} · {{ $color->hex ?: 'no hex' }}</div>
                            <div class="mt-1">
                                <a href="{{ route('admin.colors.edit', $color) }}" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size:0.72rem;">Sửa</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-light border">
        Chưa có dữ liệu màu. Chạy seeder:
        <code>php artisan db:seed --class=ColorSeeder</code>
    </div>
@endforelse

<style>
.color-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 12px;
}
.color-admin-item {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    transition: box-shadow .15s;
}
.color-admin-item:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); }
.color-admin-item.is-inactive { opacity: .4; }
.color-admin-swatch {
    height: 64px;
    display: flex; align-items: center; justify-content: center;
    border-bottom: 1px solid #f1f5f9;
}
.color-admin-meta { padding: 8px 10px 10px; font-size: 0.82rem; }
</style>
@endsection
