@extends('layouts.admin')
@section('title','Quản lý vòng quay')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><div><h2>Vòng quay may mắn</h2><p class="text-muted mb-0">Phần thưởng, số lượng và lịch sử trao giải.</p></div><a href="{{ route('admin.prizes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm phần thưởng</a></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @php($totalWeight=$prizes->filter(fn($p)=>$p->isAvailable())->sum('probability'))
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm p-3"><small class="text-muted">Tổng phần thưởng</small><strong class="fs-3">{{ $prizes->count() }}</strong></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm p-3"><small class="text-muted">Đang có thể trúng</small><strong class="fs-3">{{ $prizes->filter(fn($p)=>$p->isAvailable() && $p->probability>0)->count() }}</strong></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm p-3"><small class="text-muted">Lượt đã quay</small><strong class="fs-3">{{ $histories->total() }}</strong></div></div>
    </div>
    @if(!$totalWeight)<div class="alert alert-warning">Chưa có giải khả dụng. Khách hiện không thể quay.</div>@endif
    <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white py-3"><strong>Danh sách phần thưởng</strong><div class="small text-muted mt-1">Tỷ lệ = trọng số / tổng trọng số giải đang bật và còn hàng. Tắt giải để ngừng trao, lịch sử vẫn được giữ.</div></div>
    <div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th>Phần thưởng</th><th>Loại / giá trị</th><th>Trọng số</th><th>Tỷ lệ hiện tại</th><th>Còn lại</th><th>Trạng thái</th><th></th></tr></thead><tbody>
    @foreach($prizes as $prize)<tr><td>{{ $prize->name }}</td><td>{{ ['points'=>'Điểm','voucher'=>'Voucher (đ)','ticket'=>'Lượt quay','empty'=>'Không trúng'][$prize->type] }} · {{ number_format($prize->value) }}</td><td>{{ $prize->probability }}</td><td>{{ $totalWeight && $prize->isAvailable() ? number_format(100*$prize->probability/$totalWeight,2) : '0' }}%</td><td>{{ $prize->quantity===-1?'Không giới hạn':$prize->quantity }}</td><td><span class="badge {{ $prize->is_active?'bg-success':'bg-secondary' }}">{{ $prize->is_active?'Đang bật':'Đã tắt' }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.prizes.edit',$prize) }}">Chỉnh sửa</a></td></tr>@endforeach
    </tbody></table></div></div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white py-3"><strong>Lịch sử quay thưởng</strong></div><div class="table-responsive"><table class="table mb-0 align-middle"><thead class="table-light"><tr><th>Thời gian</th><th>Khách hàng</th><th>Phần thưởng</th><th>Chi tiết</th></tr></thead><tbody>
    @forelse($histories as $history)<tr><td>{{ $history->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td><td>{{ $history->user?->name ?? 'Tài khoản đã xóa' }}</td><td>{{ $history->prize_name }}</td><td>{{ $history->reward_detail }}</td></tr>@empty<tr><td colspan="4" class="p-4 text-center text-muted">Chưa có lượt quay.</td></tr>@endforelse
    </tbody></table></div><div class="card-footer bg-white">{{ $histories->links() }}</div></div>
</div>
@endsection
