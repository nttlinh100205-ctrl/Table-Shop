@extends('layouts.app')
@section('title', 'Điểm danh nhận xu')
@section('content')
<div class="container py-5" style="max-width:850px">
    <div class="card p-4 border-0 shadow-sm" style="background:#fffaf3;border-radius:20px">
        <h1 class="h2">Điểm danh nhận xu</h1>
        <p>Số dư: <strong id="coin-balance">{{ number_format($status['balance']) }}</strong> xu · {{ config('coins.vnd_per_coin') }}đ/xu</p>
        <p>Mỗi ngày nhận một lần theo giờ Việt Nam. Không cần liên tiếp, bỏ ngày vẫn giữ tiến độ. Lần thứ 7 nhận 200 xu rồi bắt đầu chu kỳ mới.</p>
        <div class="d-flex flex-wrap gap-2 mb-4" id="check-in-days">
            @for($day=1; $day<=7; $day++)
                <div class="rounded border p-3 text-center flex-grow-1 {{ $day <= $status['cycle_day'] ? 'bg-success text-white' : 'bg-white' }}" data-day="{{ $day }}">
                    <div>Lần {{ $day }}</div><strong>{{ $day === 7 ? 200 : 100 }} xu</strong>
                    <div class="small" data-state>{{ $day <= $status['cycle_day'] ? 'Đã nhận' : 'Chưa nhận' }}</div>
                </div>
            @endfor
        </div>
        <button id="claim-coins" class="btn btn-dark py-3" @disabled($status['claimed_today'])>{{ $status['claimed_today'] ? 'Hôm nay đã điểm danh' : 'Điểm danh lần '.$status['next_day'].' — nhận xu' }}</button>
        <p id="check-in-result" class="mt-3" role="status" aria-live="polite"></p>
        <small>Xu dùng giảm tiền hàng ở checkout, không giảm phí giao hàng. Đơn sau khi dùng xu cần còn ít nhất {{ number_format(config('coins.minimum_goods_payment')) }}đ tiền hàng.</small>
        <div class="mt-3"><a href="{{ route('user.points.index') }}">Tài khoản & điểm thưởng</a> · <a href="{{ route('user.spin.index') }}">Vòng quay may mắn</a></div>
    </div>
</div>
<script>
(() => {
    const button=document.getElementById('claim-coins'), result=document.getElementById('check-in-result');
    button.onclick=async()=>{
        button.disabled=true;
        try{
            const r=await fetch(@json(route('user.check-in.store')),{method:'POST',headers:{Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())}});
            const data=await r.json();if(!r.ok)throw Error(data.message||'Không điểm danh được. Vui lòng tải lại trang.');
            document.getElementById('coin-balance').textContent=new Intl.NumberFormat('vi-VN').format(data.balance);
            result.textContent=data.awarded?`Bạn đã nhận ${data.coins} xu!`:'Bạn đã nhận xu hôm nay. Hẹn bạn ngày mai!';
            button.textContent='Hôm nay đã điểm danh';
            document.querySelectorAll('[data-day]').forEach(el=>{
                const done=Number(el.dataset.day)<=data.cycle_day;
                el.classList.toggle('bg-success',done);el.classList.toggle('text-white',done);el.classList.toggle('bg-white',!done);
                el.querySelector('[data-state]').textContent=done?'Đã nhận':'Chưa nhận';
            });
        }catch(error){result.textContent=error.message;button.disabled=false;}
    };
})();
</script>
@endsection
