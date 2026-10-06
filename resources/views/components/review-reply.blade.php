@if($rev->admin_reply)
<div class="rounded border-start border-3 p-3 mt-3" style="background:#f8f3eb;border-color:#c29d62!important">
    <strong class="small">Phản hồi từ Table Shop</strong>
    @if($rev->reply_source === 'ai')<span class="badge bg-light text-dark border ms-1">Trợ lý AI</span>@endif
    <p class="mb-0 mt-1 small" style="white-space:pre-line">{{ $rev->admin_reply }}</p>
</div>
@elseif($rev->ai_reply_status === 'queued')
<p class="small text-muted mt-2 mb-0">Trợ lý AI đang chuẩn bị phản hồi. Bạn có thể tải lại trang sau ít giây để xem.</p>
@endif
