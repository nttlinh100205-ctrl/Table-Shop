@extends('layouts.admin')
@section('title','Chi tiết thành viên')
@section('content')
<div class="container-fluid">
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4"><div><h2>{{ $user->name }}</h2><p class="text-muted mb-0">{{ $user->email }} · #{{ $user->id }} · {{ $user->role }}</p></div><div><a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">← Người dùng</a> <a class="btn btn-primary" href="{{ route('admin.users.edit',$user) }}">Sửa thông tin</a></div></div>
<div class="row g-3 mb-4">
@foreach(['Hạng thành viên'=>$user->tier['name'],'Điểm hiện có'=>number_format($user->points_balance),'Điểm tích lũy xếp hạng'=>number_format($user->lifetime_points),'Số dư xu'=>number_format($user->coin_balance)] as $label=>$value)
<div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm p-3 h-100"><small class="text-muted">{{ $label }}</small><strong class="fs-4">{{ $value }}</strong></div></div>
@endforeach
</div>
<div class="card border-0 shadow-sm p-3 mb-4"><div class="d-flex gap-4 flex-wrap"><span>Mã giới thiệu: <strong>{{ $user->referral_code }}</strong></span><span>Lượt quay: <strong>{{ $user->spin_tickets }}</strong></span><span>Email: {{ $user->email_verified_at ? 'Đã xác thực' : ($user->email_verification_exempt ? 'Miễn xác thực • tài khoản cũ' : 'Chưa xác thực') }}</span></div><small class="text-muted mt-2">Hạng được tính tự động theo điểm tích lũy. Điểm thành viên và xu là hai số dư riêng.</small></div>
<nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Thông tin thành viên">
@foreach(['payments'=>'Thanh toán','points'=>'Lịch sử điểm','coins'=>'Lịch sử xu','voucher-orders'=>'Voucher đã dùng','owned-vouchers'=>'Voucher sở hữu'] as $id=>$label)<a class="btn btn-outline-primary btn-sm" href="#{{ $id }}">{{ $label }}</a>@endforeach
</nav>
<section id="payments" class="card border-0 shadow-sm mb-4"><div class="card-header bg-white py-3"><strong>Giao dịch thanh toán</strong></div><div class="table-responsive"><table class="table mb-0 align-middle"><thead class="table-light"><tr><th>Thời gian</th><th>Đơn hàng</th><th>Phương thức</th><th>Số tiền</th><th>Trạng thái</th></tr></thead><tbody>
@forelse($payments as $tx)<tr><td>{{ $tx->created_at->format('d/m/Y H:i') }}</td><td><a href="{{ route('admin.orders.show',$tx->order_id) }}">#{{ $tx->order_id }}</a></td><td>{{ strtoupper($tx->gateway) }}</td><td>{{ number_format($tx->amount) }}đ</td><td>{{ $tx->status }}</td></tr>@empty<tr><td colspan="5" class="text-muted p-3">Chưa có giao dịch.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $payments->withQueryString()->fragment('payments')->links() }}</div></section>
<section id="points" class="card border-0 shadow-sm mb-4"><div class="card-header bg-white py-3"><strong>Lịch sử điểm thành viên</strong></div><div class="table-responsive"><table class="table mb-0"><thead class="table-light"><tr><th>Ngày</th><th>Loại</th><th>Điểm</th><th>Nội dung</th></tr></thead><tbody>
@forelse($points as $tx)<tr><td>{{ $tx->created_at->format('d/m/Y H:i') }}</td><td>{{ $tx->type }}</td><td>{{ number_format($tx->points) }}</td><td>{{ $tx->description }}</td></tr>@empty<tr><td colspan="4" class="text-muted p-3">Chưa có lịch sử điểm.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $points->withQueryString()->fragment('points')->links() }}</div></section>
<section id="coins" class="card border-0 shadow-sm mb-4"><div class="card-header bg-white py-3"><strong>Lịch sử xu</strong></div><div class="table-responsive"><table class="table mb-0"><thead class="table-light"><tr><th>Ngày</th><th>Biến động</th><th>Nội dung</th></tr></thead><tbody>
@forelse($coins as $tx)<tr><td>{{ \Carbon\Carbon::parse($tx->created_at)->format('d/m/Y H:i') }}</td><td class="{{ $tx->amount>0?'text-success':'text-danger' }}">{{ $tx->amount>0?'+':'' }}{{ number_format($tx->amount) }} xu</td><td>{{ $tx->description }}</td></tr>@empty<tr><td colspan="3" class="text-muted p-3">Chưa có lịch sử xu.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $coins->withQueryString()->fragment('coins')->links() }}</div></section>
<section id="voucher-orders" class="card border-0 shadow-sm mb-4"><div class="card-header bg-white py-3"><strong>Voucher áp dụng vào đơn hàng</strong></div><div class="table-responsive"><table class="table mb-0"><thead class="table-light"><tr><th>Đơn</th><th>Mã voucher</th><th>Đã giảm</th><th>Trạng thái đơn</th></tr></thead><tbody>
@forelse($voucherOrders as $order)<tr><td><a href="{{ route('admin.orders.show',$order) }}">#{{ $order->id }}</a></td><td>{{ $order->coupon_code }}</td><td>{{ number_format($order->discount_amount) }}đ</td><td>{{ $order->status }}</td></tr>@empty<tr><td colspan="4" class="text-muted p-3">Chưa sử dụng voucher.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $voucherOrders->withQueryString()->fragment('voucher-orders')->links() }}</div></section>
<section id="owned-vouchers" class="card border-0 shadow-sm mb-4"><div class="card-header bg-white py-3"><strong>Voucher sở hữu</strong></div><div class="table-responsive"><table class="table mb-0"><thead class="table-light"><tr><th>Mã</th><th>Ưu đãi</th><th>Lượt dùng / Giới hạn</th><th>Hết hạn</th></tr></thead><tbody>
@forelse($vouchers as $voucher)<tr><td>{{ $voucher->code }}</td><td>{{ $voucher->name }}</td><td>{{ $voucher->used_count }} / {{ $voucher->usage_limit ?? '∞' }}</td><td>{{ $voucher->end_date?->format('d/m/Y') ?? 'Không giới hạn' }}</td></tr>@empty<tr><td colspan="4" class="text-muted p-3">Chưa có voucher riêng.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $vouchers->withQueryString()->fragment('owned-vouchers')->links() }}</div></section>
</div>
@endsection
