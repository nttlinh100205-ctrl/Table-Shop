@extends('layouts.admin')

@section('title', 'Chi tiết người dùng')

@section('content')
<div class="container" style="max-width:560px">
    <h2 class="mb-4">Thông tin người dùng</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $user->id }}</p>
            <p><strong>Tên:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Vai trò:</strong> {{ $user->role }}</p>
            <p class="mb-0"><strong>Tạo lúc:</strong> {{ $user->created_at?->format('d/m/Y H:i') }}</p>
        </div>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mt-3">← Quay lại</a>
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary mt-3">Sửa</a>
</div>
@endsection
