@if($rev->admin_reply)
<div class="rounded border-start border-3 p-3 mt-3" style="background:#f8f3eb;border-color:#c29d62!important">
    <strong class="small">Phản hồi từ Table Shop</strong>
    <p class="mb-0 mt-1 small" style="white-space:pre-line">{{ $rev->admin_reply }}</p>
</div>
@endif
