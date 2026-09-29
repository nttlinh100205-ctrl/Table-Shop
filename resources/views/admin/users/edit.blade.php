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

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="card shadow-sm p-4">
        @csrf
        @method('PUT')
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
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
</div>
@endsection
