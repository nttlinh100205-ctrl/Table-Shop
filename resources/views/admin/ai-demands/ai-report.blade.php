<section class="card border-0 shadow-sm mb-4" aria-labelledby="ai-advisor-title">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><h4 id="ai-advisor-title" class="mb-1"><i class="bi bi-stars me-1"></i> AI đề xuất sản phẩm nên bổ sung</h4><p class="small text-muted mb-0">Phân tích thuộc tính nhu cầu và số liệu tổng hợp trong {{ $days }} ngày. Câu hỏi nguyên văn được giữ trong website để đối chiếu.</p></div>
        <form id="ai-advisor-form" method="post" action="{{ route('admin.ai-demands.analyze') }}">@csrf<input type="hidden" name="days" value="{{ $days }}"><button class="btn btn-primary" id="ai-advisor-submit" type="submit"><i class="bi bi-stars me-1"></i>{{ $aiReport ? 'Cập nhật phân tích AI' : 'Phân tích bằng AI' }}</button><div id="ai-advisor-progress" class="small text-muted mt-2" role="status" hidden>AI đang phân tích, vui lòng chờ…</div></form>
    </div>
    <div class="card-body p-4">
    @if($aiReport)
        <div class="small text-muted mb-3">Bản phân tích {{ $aiReport->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }} · Dữ liệu từ {{ $aiReport->period_start->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }} đến {{ $aiReport->period_end->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}.<br>Đã phân tích {{ number_format($aiReport->sampled_count) }}/{{ number_format($aiReport->question_count) }} lượt hỏi sản phẩm, trong tối đa 15 nhóm phổ biến. Bấm cập nhật để lấy dữ liệu mới.</div>
        <p style="white-space:pre-line;overflow-wrap:anywhere">{{ $aiReport->result['summary'] }}</p>
        @php($evidence = collect($aiReport->evidence)->keyBy('id'))
        <div class="row g-3">
        @forelse($aiReport->result['recommendations'] as $recommendation)
            <div class="col-xl-6"><article class="border rounded-3 p-3 h-100" style="background:#fffdf9;overflow-wrap:anywhere">
                <span class="badge bg-light text-dark border mb-2">{{ ['high'=>'Ưu tiên cao','medium'=>'Ưu tiên vừa','low'=>'Khảo sát thêm'][$recommendation['priority']] }}</span>
                <h5>{{ $recommendation['title'] }}</h5>
                <p class="small mb-2"><strong>Đặc điểm đề xuất:</strong> {{ $recommendation['specs'] }}</p>
                <p class="small mb-2"><strong>Lý do:</strong> {{ $recommendation['reason'] }}</p>
                <p class="small"><strong>Bước tiếp theo:</strong> {{ $recommendation['action'] }}</p>
                <details class="small"><summary class="mb-2" style="cursor:pointer">Xem câu hỏi làm căn cứ</summary>
                    @foreach($recommendation['evidence_ids'] as $reference)
                        @php($source = $evidence[$reference])
                        <div class="border-top py-2"><strong>{{ $source['questions'] }} lượt hỏi · {{ $source['visitors'] }} tài khoản / phiên</strong><p class="mb-1">“{{ $source['question'] }}”</p><div class="text-muted">{{ $source['demand'] }}</div><div class="text-muted">{{ $source['unmatched_at_recording'] }} lượt tìm chưa khớp khi ghi nhận; {{ $source['imported_questions'] }} câu từ phiên cũ.</div>
                        @if($source['current_candidates'])<div class="mt-1">Mẫu phù hợp tìm được lúc phân tích: {{ collect($source['current_candidates'])->pluck('name')->implode(', ') }}.</div>@else<div class="mt-1">Chưa có mẫu khớp trong dữ liệu đối chiếu; cần kiểm tra tồn kho và thông số.</div>@endif</div>
                    @endforeach
                </details>
            </article></div>
        @empty<div class="col-12 text-muted">AI chưa thấy đủ căn cứ để đề xuất mặt hàng bổ sung.</div>@endforelse
        </div>
        <p class="small text-muted mt-3 mb-0">Đây là đề xuất hỗ trợ quyết định, không tự thêm sản phẩm hoặc đặt hàng. Số người giữa các nhóm có thể trùng nhau. Cần kiểm tra tồn kho, giá nhập và nhà cung cấp trước khi triển khai.</p>
    @else
        <p class="text-muted mb-0">Bấm “Phân tích bằng AI” để nhận đề xuất cụ thể về loại sản phẩm, màu, kích thước và tầm giá dựa trên dữ liệu khách đã hỏi. Kết quả được lưu tại đây.</p>
    @endif
    </div>
</section>
<script>
(() => {
    const form = document.getElementById('ai-advisor-form');
    const button = document.getElementById('ai-advisor-submit');
    const progress = document.getElementById('ai-advisor-progress');
    form.addEventListener('submit', () => { button.disabled = true; progress.hidden = false; });
    window.addEventListener('pageshow', () => { button.disabled = false; progress.hidden = true; });
})();
</script>
