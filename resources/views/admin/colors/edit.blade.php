@extends('layouts.admin')

@section('title', 'Sửa màu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-palette me-2"></i>Sửa màu</h4>
    <a href="{{ route('admin.colors.index') }}" class="btn btn-outline-secondary btn-sm">← Quay lại</a>
</div>

<div class="card border-0 shadow-sm" style="max-width:560px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.colors.update', $color) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Tên màu <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $color->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Mã màu</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                       value="{{ old('code', $color->code) }}" placeholder="VD: VG-SOI, 110T">
                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Mã hex (hiển thị ô màu)</label>
                <div class="input-group">
                    <input type="color" id="hexPicker" class="form-control form-control-color"
                           value="{{ old('hex', $color->hex ?: '#C4A574') }}" title="Chọn màu">
                    <input type="text" name="hex" id="hexInput" class="form-control @error('hex') is-invalid @enderror"
                           value="{{ old('hex', $color->hex) }}" placeholder="#C4A574">
                </div>
                @error('hex') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                <div class="mt-2 d-flex align-items-center gap-2">
                    <span class="d-inline-block rounded border"
                          style="width:64px;height:48px;{{ $color->swatch_style }}"></span>
                    @if ($color->image_url)
                        <span class="text-success small">Có ảnh texture: {{ $color->code }}</span>
                    @else
                        <span class="text-muted small">Chưa có ảnh — đặt file tại public/storage/images/colors/{{ $color->code }}.jpg</span>
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nhóm <span class="text-danger">*</span></label>
                <select name="group" class="form-select @error('group') is-invalid @enderror" required>
                    @foreach ($groups as $key => $label)
                        <option value="{{ $key }}" @selected(old('group', $color->group) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('group') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Thứ tự sắp xếp</label>
                <input type="number" name="sort_order" class="form-control"
                       value="{{ old('sort_order', $color->sort_order) }}" min="0">
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive"
                       @checked(old('is_active', $color->is_active))>
                <label class="form-check-label" for="isActive">Đang dùng</label>
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật</button>
        </form>
    </div>
</div>

<script>
document.getElementById('hexPicker').addEventListener('input', function () {
    document.getElementById('hexInput').value = this.value;
});
document.getElementById('hexInput').addEventListener('input', function () {
    let v = this.value.trim();
    if (v && v[0] !== '#') v = '#' + v;
    if (/^#[0-9A-Fa-f]{6}$/.test(v)) {
        document.getElementById('hexPicker').value = v;
    }
});
</script>
@endsection
