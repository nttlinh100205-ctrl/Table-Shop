@php
    $activeDay = $status['claimed_today'] ? $status['cycle_day'] : $status['next_day'];
    $completedDays = $status['claimed_today'] ? $status['cycle_day'] : $status['next_day'] - 1;
@endphp
<style>
    .coin-card { background:linear-gradient(135deg,#684831,#ac8050); color:#fff6e8; border-radius:24px; padding:24px; }
    .coin-card-top { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:24px; padding-right:22px; }
    .coin-card-balance { display:flex; align-items:center; gap:10px; font-size:32px; font-weight:800; }
    .coin-card-history { color:#fff6e8; background:#ffffff18; padding:8px 14px; border:1px solid #ffffff30; border-radius:30px; font-size:13px; text-decoration:none; }
    .coin-card-body { background:#fffcf7; color:#493626; border-radius:18px; padding:0 20px 24px; text-align:center; }
    .coin-card-title { display:inline-block; background:#f1e2cc; border-radius:0 0 24px 24px; padding:14px 30px; margin:0 0 24px; font-size:28px; font-weight:700; color:#61432b; }
    .coin-days { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:10px; }
    .coin-day-tile { position:relative; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:14px; padding:16px 2px; min-height:112px; background:#f3eee7; border:2px solid transparent; border-radius:12px; }
    .coin-day.is-current .coin-day-tile { border-color:#a7783f; background:#fff9ec; }
    .coin-day.is-done .coin-day-tile { background:#eee7dc; }
    .coin-day.is-done .reward-coin { filter:saturate(.45); }
    .coin-day:last-child .coin-day-tile { background:radial-gradient(ellipse at bottom,#f9dfa0,#e9c383); }
    .coin-day-amount { font-size:18px; color:#76532f; font-weight:800; }
    .coin-day-label { display:block; margin-top:9px; font-size:12px; color:#897765; }
    .coin-day.is-current .coin-day-label { color:#8c5b2e; font-weight:800; }
    .reward-coin { display:inline-grid; place-items:center; width:40px; height:40px; flex-shrink:0; border-radius:50%; border:3px solid #f7db92; outline:1px solid #c28b35; background:linear-gradient(135deg,#fff0b0,#deb254 55%,#f9df8a); box-shadow:inset 0 0 0 2px #c79542,0 3px 5px #8e612229; color:#926526; font-size:19px; font-weight:800; font-family:Georgia,serif; text-shadow:0 1px #fff3bf; }
    .coin-day:last-child .reward-coin { box-shadow:5px 3px 0 #cb9a42,8px 6px 0 #f5d57f; }
    .coin-claim { border:0; border-radius:12px; color:#fff8ed; background:#5a4536; width:100%; margin-top:25px; padding:16px; font-size:18px; font-weight:700; }
    .coin-claim:hover { background:#3f2f24; }
    .coin-claim:disabled { opacity:.65; cursor:default; }
    .coin-card-note { font-size:12px; color:#8a7764; margin:14px 0 0; line-height:1.7; }
    @media(max-width:575px) {
        .coin-card { padding:14px 10px; border-radius:20px; }
        .coin-card-top { margin-bottom:16px; padding-left:5px; padding-right:25px; }
        .coin-card-balance { font-size:27px; }
        .coin-card-body { padding:0 8px 18px; }
        .coin-card-title { font-size:23px; padding:12px 15px; margin-bottom:18px; }
        .coin-days { gap:4px; }
        .coin-day-tile { min-height:89px; gap:14px; border-radius:8px; }
        .coin-day .reward-coin { width:25px; height:25px; font-size:12px; border-width:2px; }
        .coin-day-amount { font-size:12px; }
        .coin-day-label { font-size:10px; }
        .coin-claim { font-size:16px; }
    }
</style>
<section class="coin-card" id="{{ $cardId }}" aria-label="Điểm danh nhận xu">
    <div class="coin-card-top">
        <div class="coin-card-balance"><span class="reward-coin" aria-hidden="true">T</span><span data-coin-balance>{{ number_format($status['balance'], 0, ',', '.') }}</span><span class="visually-hidden">xu</span></div>
        <a class="coin-card-history" href="{{ route('user.check-in.index') }}#coin-history">Lịch sử <i class="bi bi-chevron-right" aria-hidden="true"></i></a>
    </div>
    <div class="coin-card-body">
        <h2 class="coin-card-title">Điểm danh nhận xu</h2>
        <div class="coin-days">
            @for($day=1;$day<=7;$day++)
            <div class="coin-day {{ $day === $activeDay ? 'is-current' : '' }} {{ $day <= $completedDays ? 'is-done' : '' }}" data-reward-day="{{ $day }}">
                <div class="coin-day-tile"><strong class="coin-day-amount">+{{ $day === 7 ? 200 : 100 }}</strong><span class="reward-coin" aria-hidden="true">T</span></div>
                <span class="coin-day-label">{{ $day <= $completedDays ? '✓ Đã nhận' : ($day === $activeDay ? 'Hôm nay' : 'Lần '.$day) }}</span>
            </div>
            @endfor
        </div>
        <button type="button" id="{{ $claimId }}" class="coin-claim" @disabled($status['claimed_today'])>{{ $status['claimed_today'] ? 'Hôm nay đã điểm danh' : 'Nhận ngay '.($status['next_day'] === 7 ? 200 : 100).' xu' }}</button>
        <p class="coin-card-note">Không cần liên tiếp · Lần thứ 7 nhận 200 xu<br>1 xu = 1đ giảm tiền hàng</p>
        <p data-claim-result role="status" aria-live="polite" class="small mt-2 mb-0"></p>
    </div>
</section>
<script>
(() => {
    const card=document.getElementById(@json($cardId)), button=document.getElementById(@json($claimId)), result=card.querySelector('[data-claim-result]');
    button.onclick=async()=>{
        button.disabled=true;result.textContent='Đang nhận xu…';
        try {
            const r=await fetch(@json(route('user.check-in.store')),{method:'POST',headers:{Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())}});
            const data=await r.json();if(!r.ok)throw Error(data.message||'Chưa nhận được xu. Vui lòng thử lại.');
            document.querySelectorAll('[data-coin-balance]').forEach(el=>el.textContent=new Intl.NumberFormat('vi-VN').format(data.balance));
            button.textContent='Hôm nay đã điểm danh';
            result.textContent=data.awarded?`Bạn đã nhận ${data.coins} xu!`:'Bạn đã nhận xu hôm nay. Hẹn bạn ngày mai!';
            card.querySelectorAll('[data-reward-day]').forEach(el=>{
                const day=Number(el.dataset.rewardDay),done=day<=data.cycle_day;
                el.classList.toggle('is-done',done);el.classList.toggle('is-current',day===data.cycle_day);
                el.querySelector('.coin-day-label').textContent=done?'✓ Đã nhận':`Lần ${day}`;
            });
            window.dispatchEvent(new CustomEvent('coins-claimed',{detail:data}));
        } catch(e) { result.textContent=e.message;button.disabled=false; }
    };
})();
</script>
