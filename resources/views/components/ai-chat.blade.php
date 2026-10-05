<style>
#ai-toggle{position:fixed;bottom:24px;left:20px;z-index:1050;background:#493626;color:white;border:0;border-radius:30px;padding:12px 20px}
#ai-panel{position:fixed;bottom:82px;left:20px;width:min(370px,calc(100vw - 40px));z-index:1050;background:#fffaf3;border:1px solid #c29d62;border-radius:16px;padding:16px;box-shadow:0 8px 40px #0003}
#ai-messages{height:300px;overflow:auto;white-space:pre-wrap;overflow-wrap:anywhere;font-size:14px}#ai-messages p{padding:10px;border-radius:10px;background:#eee5d8}#ai-messages p[data-user]{background:#dfebdf}
</style>
<button id="ai-toggle" type="button" aria-expanded="false" aria-controls="ai-panel">✦ Tư vấn AI</button>
<section id="ai-panel" hidden aria-label="Tư vấn sản phẩm AI">
    <div class="d-flex justify-content-between"><strong>Trợ lý nội thất AI</strong><button type="button" id="ai-close" aria-label="Đóng">×</button></div>
    <small>Tin nhắn và sở thích xem hàng được gửi tới Google AI để tư vấn.</small>
    <div id="ai-messages" role="log" aria-live="polite"></div>
    <form id="ai-form" class="d-flex gap-2"><input id="ai-input" class="form-control" maxlength="2000" required aria-label="Câu hỏi tư vấn" placeholder="Bạn cần tìm sản phẩm gì?"><button class="btn btn-dark">Gửi</button></form>
    @auth<a href="{{ route('user.spin.index') }}">Vòng quay may mắn</a> · <a href="{{ route('user.profile') }}">Điểm & mã giới thiệu</a>@endauth
</section>
<script>
(() => {
    const panel=document.getElementById('ai-panel'), toggle=document.getElementById('ai-toggle'), list=document.getElementById('ai-messages'), form=document.getElementById('ai-form'), input=document.getElementById('ai-input');
    let greeted=false;
    function add(text,user=false){
        const p=document.createElement('p'); if(user)p.dataset.user='true';
        // Create text nodes and allow only local product links; never render AI HTML.
        const pattern=/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g; let match,last=0;
        while((match=pattern.exec(text))!==null){
            p.append(document.createTextNode(text.slice(last,match.index)));
            let url;try{url=new URL(match[2]);}catch(e){}
            if(url && url.origin===location.origin && /\/(?:user\/)?products\/\d+$/.test(url.pathname)){
                const a=document.createElement('a');a.href=url.href;a.textContent=match[1];p.append(a);
            }else p.append(document.createTextNode(match[1]));last=pattern.lastIndex;
        }
        p.append(document.createTextNode(text.slice(last)));list.append(p);list.scrollTop=list.scrollHeight;
    }
    toggle.onclick=async()=>{
        panel.hidden=!panel.hidden;toggle.setAttribute('aria-expanded',String(!panel.hidden));
        if(!panel.hidden && !greeted){greeted=true;try{const r=await fetch(@json(route('ai.greeting')),{headers:{Accept:'application/json'}});if(!r.ok)throw Error();add((await r.json()).reply);}catch(e){greeted=false;add('Không tải được lời chào. Vui lòng thử lại.');}}
    };
    document.getElementById('ai-close').onclick=()=>{panel.hidden=true;toggle.setAttribute('aria-expanded','false');};
    form.onsubmit=async e=>{
        e.preventDefault();const message=input.value.trim();if(!message)return;
        const button=form.querySelector('button');button.disabled=true;input.disabled=true;add(message,true);
        try{const r=await fetch(@json(route('ai.send')),{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({message})});const data=await r.json();if(!r.ok)throw Error(data.message||'Không thể kết nối AI.');add(data.reply);input.value='';}
        catch(error){add(error.message||'Kết nối gián đoạn. Vui lòng thử lại.');}
        finally{button.disabled=false;input.disabled=false;input.focus();}
    };
})();
</script>
