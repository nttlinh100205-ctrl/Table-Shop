@extends('layouts.admin')

@section('title', 'Thêm người dùng')

@section('content')
<div class="container" style="max-width:560px">
    <h2 class="mb-4">Thêm người dùng</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="card shadow-sm p-4">
        @csrf
        <div class="mb-3">
            <label class="form-label">Tên</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Vai trò</label>
            <select name="role" class="form-select" required>
                <option value="user">Người dùng</option>
                <option value="admin">Quản trị</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Lưu</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
</div>
@endsection
