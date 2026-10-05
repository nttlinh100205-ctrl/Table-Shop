@extends('layouts.admin')

@section('title', 'Sửa người dùng')

@section('content')
<div class="container" style="max-width:560px">
    <h2 class="mb-4">Chỉnh sửa người dùng</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data" class="card shadow-sm p-4">
        @csrf
        @method('PUT')
        
        <div class="mb-3 text-center">
            @if(!empty($user->avatar))
                <div class="mb-2">
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-circle border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="delete_avatar" id="delete_avatar" value="1">
                    <label class="form-check-label text-danger small" for="delete_avatar">Xóa ảnh đại diện hiện tại</label>
                </div>
            @else
                <div class="rounded-circle bg-secondary text-white mx-auto d-flex align-items-center justify-content-center fw-bold fs-3 mb-2" style="width: 80px; height: 80px;">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
                <span class="text-muted small">Chưa có ảnh đại diện</span>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label">Tên</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Vai trò</label>
            <select name="role" class="form-select" required>
                <option value="user" @selected($user->role === 'user')>Người dùng</option>
                <option value="admin" @selected($user->role === 'admin')>Quản trị</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Thay đổi ảnh đại diện (Avatar)</label>
            <input type="file" name="avatar" class="form-control" accept="image/*">
            <div class="form-text">Tải lên qua Cloudinary (JPG, PNG, WEBP &le; 5MB)</div>
        </div>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
</div>
@endsection
