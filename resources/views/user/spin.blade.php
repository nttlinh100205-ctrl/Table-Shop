@extends('layouts.app')
@section('title', 'Vòng quay may mắn')
@section('content')
<div class="container py-5 text-center">
    <h1>Vòng quay may mắn</h1>
    <p>Bạn có <strong id="spin-tickets">{{ auth()->user()->spin_tickets }}</strong> lượt quay.</p>
    <div style="position:relative;width:min(340px,85vw);margin:25px auto">
        <span style="position:absolute;top:-24px;left:calc(50% - 12px);z-index:2;font-size:28px;color:#b45309">▼</span>
        <div id="spin-wheel" style="width:100%;aspect-ratio:1;border-radius:50%;border:8px solid #493626;transition:transform 4s cubic-bezier(.15,.8,.15,1);position:relative"></div>
    </div>
    <button id="spin-button" class="btn btn-dark px-5" @disabled($prizes->isEmpty())>Quay ngay</button>
    <p id="spin-result" class="mt-3" role="status" aria-live="polite"></p>
    <p class="text-muted">Các ô có kích thước bằng nhau; xác suất trúng được tính theo trọng số và giải còn trong kho.</p>
    <ul class="list-unstyled">@foreach($prizes as $prize)<li>{{ $loop->iteration }}. {{ $prize->name }} · Trọng số {{ $prize->probability }} · {{ $prize->quantity === -1 ? 'Không giới hạn' : 'Còn '.$prize->quantity }}</li>@endforeach</ul>
    <h2 class="h4 mt-4">20 lượt quay gần nhất</h2>
    <ul id="spin-history" class="list-unstyled">@foreach($histories as $history)<li>{{ $history->created_at->format('d/m/Y H:i') }} — {{ $history->prize_name }}: {{ $history->reward_detail }}</li>@endforeach</ul>
    <a href="{{ route('user.points.index') }}">Xem điểm & voucher của bạn</a>
</div>
@php($wheelPrizes = $prizes->map->only(['id', 'name'])->values())
<script>
(() => {
    const prizes=@json($wheelPrizes), wheel=document.getElementById('spin-wheel'), button=document.getElementById('spin-button'), result=document.getElementById('spin-result');
    const step=360/prizes.length, colors=['#c29d62','#5a4536','#eadbc7','#a87957'];let rotation=0,pending=null;
    wheel.style.background='conic-gradient('+prizes.map((p,i)=>`${colors[i%4]} ${i*step}deg ${(i+1)*step}deg`).join(',')+')';
    prizes.forEach((p,i)=>{const label=document.createElement('span');label.textContent=String(i+1);label.style.cssText=`position:absolute;left:calc(50% - 10px);top:calc(50% - 10px);color:white;font-weight:bold;transform:rotate(${i*step+step/2}deg) translateY(-110px) rotate(${-i*step-step/2}deg)`;wheel.append(label);});
    button.onclick=async()=>{
        button.disabled=true;result.textContent='Đang quay…';
        // Retain this key after a network failure, so retry cannot consume another ticket.
        if(!pending)pending=crypto.randomUUID();
        try{
            const response=await fetch(@json(route('user.spin.store')),{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({request_id:pending})});
            const data=await response.json();if(!response.ok){if(response.status===422)pending=null;throw Error(Object.values(data.errors||{}).flat()[0]||data.message||'Không thể quay.');}
            pending=null;const index=prizes.findIndex(p=>p.id===data.result.prize_id);
            const target=index<0?0:360-(index+.5)*step;rotation+=1800+((target-rotation%360+360)%360);wheel.style.transform=`rotate(${rotation}deg)`;
            await new Promise(resolve=>setTimeout(resolve,4100));
            document.getElementById('spin-tickets').textContent=data.tickets;result.textContent=data.result.prize_name+' — '+data.result.reward_detail;
            const li=document.createElement('li');li.textContent=result.textContent;document.getElementById('spin-history').prepend(li);
        }catch(error){result.textContent=(error.message||'Mất kết nối.')+(pending?' Nhấn quay để kiểm tra lại cùng lượt.':'');}
        finally{button.disabled=false;}
    };
})();
</script>
@endsection
