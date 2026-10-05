@extends('layouts.admin')

@section('title', 'Quản lý người dùng')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Danh sách người dùng</h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-success">+ Thêm người dùng</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <form method="GET" class="card-body d-flex gap-2"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Tìm tên hoặc email" aria-label="Tìm người dùng"><button class="btn btn-primary">Tìm</button></form>
        <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Hạng thành viên</th><th>Điểm / Xu</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-secondary' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <span class="badge bg-primary">{{ $user->tier['name'] }}</span>
                            </td>
                            <td>{{ number_format($user->points_balance) }} điểm<br><small class="text-muted">{{ number_format($user->coin_balance) }} xu</small></td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info btn-sm">Xem</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">Sửa</a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Bạn chắc chắn xóa?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Không có người dùng nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="card-footer">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection
