@if(auth()->check() && !auth()->user()->isAdmin() && auth()->user()->hasVerifiedEmail() && !request()->routeIs('user.check-in.index'))
@php
    $dailyStatus = app(\App\Services\CoinService::class)->status(auth()->user());
    $dailyKey = 'check-in-'.auth()->id().'-'.app(\App\Services\CoinService::class)->today().'-'.hash('sha256', session()->getId());
@endphp
@if(!$dailyStatus['claimed_today'])
<style>
    #daily-check-in-popup { width:min(440px,calc(100vw - 28px)); max-height:85vh; overflow:auto; border:1px solid #e5d2ad; border-radius:24px; padding:30px 24px; background:#fffaf3; color:#493626; text-align:center; box-shadow:0 24px 80px #0003; }
    #daily-check-in-popup::backdrop { background:rgba(35,25,18,.5); }
    .daily-popup-days { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin:20px 0; }
    .daily-popup-day { border:1px solid #e5d2ad; border-radius:12px; padding:10px 2px; font-size:12px; background:white; }
    .daily-popup-day.is-next { background:#5a4536; color:white; }
</style>
<dialog id="daily-check-in-popup" aria-labelledby="daily-popup-title">
    <button type="button" id="daily-popup-close" class="btn-close" style="position:absolute;right:16px;top:16px" aria-label="Đóng điểm danh"></button>
    <div aria-hidden="true" style="font-size:38px">🪙</div>
    <h2 id="daily-popup-title" class="h3">Điểm danh nhận xu</h2>
    <p class="small mb-0">Một món quà mỗi ngày dành cho bạn.<br>Không cần điểm danh liên tiếp.</p>
    <div class="daily-popup-days">
        @for($day=1;$day<=7;$day++)
        <div class="daily-popup-day {{ $day === $dailyStatus['next_day'] ? 'is-next' : '' }}">
            <div>Lần {{ $day }}</div><strong>{{ $day === 7 ? 200 : 100 }} xu</strong>
            @if($day < $dailyStatus['next_day'])<div>✓ Đã nhận</div>@endif
        </div>
        @endfor
    </div>
    <button type="button" id="daily-popup-claim" class="btn btn-dark w-100 py-3">Nhận {{ $dailyStatus['next_day'] === 7 ? 200 : 100 }} xu ngay</button>
    <p id="daily-popup-result" role="status" aria-live="polite" class="small mt-3 mb-0"></p>
    <a href="{{ route('user.check-in.index') }}" class="small d-inline-block mt-3">Xem lịch điểm danh</a>
</dialog>
<script>
(() => {
    const dialog=document.getElementById('daily-check-in-popup'), key=@json($dailyKey);
    const button=document.getElementById('daily-popup-claim'), result=document.getElementById('daily-popup-result');
    let dismissed=false;
    try { dismissed=sessionStorage.getItem(key)==='1'; } catch(e) {}
    const remember=()=>{ try { sessionStorage.setItem(key,'1'); } catch(e) {} };
    dialog.addEventListener('close',remember);
    document.getElementById('daily-popup-close').onclick=()=>dialog.close();
    if(!dismissed)dialog.showModal();
    button.onclick=async()=>{
        button.disabled=true; result.textContent='Đang nhận xu…';
        try {
            const response=await fetch(@json(route('user.check-in.store')),{method:'POST',headers:{Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())}});
            const data=await response.json();
            if(!response.ok)throw Error(data.message||'Chưa nhận được xu. Vui lòng thử lại.');
            remember(); button.textContent='Hôm nay đã điểm danh';
            document.querySelectorAll('[data-coin-balance]').forEach(el=>el.textContent=new Intl.NumberFormat('vi-VN').format(data.balance));
            result.textContent=data.awarded?`Bạn đã nhận ${data.coins} xu! Số dư: ${new Intl.NumberFormat('vi-VN').format(data.balance)} xu.`:'Bạn đã nhận xu hôm nay. Hẹn bạn ngày mai!';
            dialog.querySelector('.is-next').innerHTML += '<div>✓ Đã nhận</div>';
        } catch(e) { result.textContent=e.message; button.disabled=false; }
    };
})();
</script>
@endif
@endif
