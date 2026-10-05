@if(auth()->check() && !auth()->user()->isAdmin() && auth()->user()->hasVerifiedEmail() && !request()->routeIs('user.check-in.index'))
@php
    $dailyStatus = app(\App\Services\CoinService::class)->status(auth()->user());
    $dailyKey = 'check-in-'.auth()->id().'-'.app(\App\Services\CoinService::class)->today().'-'.hash('sha256', session()->getId());
@endphp
@if(!$dailyStatus['claimed_today'])
<style>
#daily-check-in-popup { width:min(700px,calc(100vw - 20px));max-height:90vh;overflow:auto;border:0;border-radius:24px;padding:0;background:#fffaf3;box-shadow:0 24px 80px #0003; }
#daily-check-in-popup::backdrop { background:rgba(35,25,18,.5); }
#daily-popup-close { position:absolute;right:10px;top:10px;z-index:1;border:0;background:#ffffff24;color:white;border-radius:50%;width:28px;height:28px;display:grid;place-items:center;font-size:22px;line-height:1; }
</style>
<dialog id="daily-check-in-popup" aria-label="Điểm danh nhận xu">
<button type="button" id="daily-popup-close" aria-label="Đóng điểm danh">×</button>
@include('components.check-in-card',['status'=>$dailyStatus,'cardId'=>'popup-coin-card','claimId'=>'daily-popup-claim'])
</dialog>
<script>
(() => {
const dialog=document.getElementById('daily-check-in-popup'), key=@json($dailyKey);
let dismissed=false;try { dismissed=sessionStorage.getItem(key)==='1'; } catch(e) {}
const remember=()=>{ try { sessionStorage.setItem(key,'1'); } catch(e) {} };
dialog.addEventListener('close',remember);
window.addEventListener('coins-claimed',remember);
document.getElementById('daily-popup-close').onclick=()=>dialog.close();
if(!dismissed)dialog.showModal();
})();
</script>
@endif
@endif
