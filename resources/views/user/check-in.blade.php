@extends('layouts.app')
@section('title', 'Điểm danh nhận xu')
@section('content')
<div class="container py-4" style="max-width:820px">
@include('components.check-in-card',['status'=>$status,'cardId'=>'page-coin-card','claimId'=>'claim-coins'])
<p class="small text-muted mt-3 text-center">Mỗi ngày nhận một lần theo giờ Việt Nam. Bỏ ngày vẫn giữ tiến độ.<br>Xu không giảm phí giao hàng; tiền hàng sau giảm còn tối thiểu {{ number_format(config('coins.minimum_goods_payment')) }}đ.</p>
<section id="coin-history" class="card border-0 shadow-sm p-4 mt-4" style="border-radius:20px;scroll-margin-top:90px">
<h2 class="h4">Lịch sử xu</h2>
<p class="small text-muted">20 giao dịch gần nhất.</p>
@forelse($transactions as $transaction)
<div class="d-flex justify-content-between align-items-center gap-3 py-3 border-bottom">
<div><div>{{ $transaction->description }}</div><small class="text-muted">{{ \Carbon\Carbon::parse($transaction->created_at)->timezone(config('coins.timezone'))->format('d/m/Y H:i') }}</small></div>
<strong class="text-nowrap {{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">{{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount,0,',','.') }} xu</strong>
</div>
@empty
<p class="text-muted mb-0" id="coin-history-empty">Chưa có giao dịch xu. Điểm danh để nhận món quà đầu tiên!</p>
@endforelse
</section>
</div>
<script>
window.addEventListener('coins-claimed', event => {
    const data=event.detail;if(!data.awarded)return;
    document.getElementById('coin-history-empty')?.remove();
    const row=document.createElement('div');row.className='d-flex justify-content-between gap-3 py-3 border-bottom';
    const text=document.createElement('span');text.textContent=`Điểm danh lần ${data.cycle_day}/7 · Vừa nhận`;
    const amount=document.createElement('strong');amount.className='text-success text-nowrap';amount.textContent=`+${data.coins} xu`;
    row.append(text,amount);document.querySelector('#coin-history > p').after(row);
});
</script>
@endsection
