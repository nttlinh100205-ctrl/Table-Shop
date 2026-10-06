@extends('layouts.admin')
@section('title','Nhu cầu khách hàng từ AI')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div><h2>Nhu cầu khách hàng từ AI</h2><p class="text-muted mb-0">Hiểu khách đang tìm gì và lên kế hoạch bổ sung danh mục.</p></div>
        <form method="get" class="d-flex flex-wrap gap-2">
            <label class="visually-hidden" for="demand-days">Khoảng thời gian</label>
            <select id="demand-days" name="days" class="form-select w-auto">@foreach([7,30,90] as $range)<option value="{{ $range }}" @selected($days === $range)>{{ $range }} ngày gần đây</option>@endforeach</select>
            <label class="visually-hidden" for="demand-topic">Chủ đề câu hỏi</label>
            <select id="demand-topic" name="topic" class="form-select w-auto"><option value="">Tất cả chủ đề</option>@foreach(\App\Services\AiDemandAnalytics::TOPICS as $key=>$label)<option value="{{ $key }}" @selected(request('topic')===$key)>{{ $label }}</option>@endforeach</select>
            <button class="btn btn-primary">Xem thống kê</button>
        </form>
    </div>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div class="row g-3 mb-4">
        @foreach(['Lượt hỏi AI'=>$total,'Tài khoản / phiên riêng biệt'=>$visitors,'Lượt tìm chưa có kết quả khớp'=>$gaps] as $label=>$value)
        <div class="col-md-4"><div class="card h-100 border-0 shadow-sm p-4"><span class="text-muted small">{{ $label }}</span><strong class="fs-2 mt-2">{{ number_format($value) }}</strong></div></div>
        @endforeach
    </div>
    <div class="card border-0 shadow-sm mb-4"><div class="card-body">
        <h5 class="mb-3">Khách quan tâm đến điều gì?</h5>
        <div class="row g-3">@forelse($topics as $topic)<div class="col-sm-6 col-lg-4"><div class="d-flex justify-content-between small mb-2"><span>{{ \App\Services\AiDemandAnalytics::TOPICS[$topic->topic] ?? $topic->topic }}</span><strong>{{ $topic->total }} lượt</strong></div><div class="progress" style="height:6px" role="progressbar" aria-label="{{ \App\Services\AiDemandAnalytics::TOPICS[$topic->topic] ?? $topic->topic }}" aria-valuenow="{{ $topic->total }}" aria-valuemin="0" aria-valuemax="{{ $total }}"><div class="progress-bar" style="width:{{ $total ? round(100*$topic->total/$total) : 0 }}%;background:#9b7953"></div></div></div>@empty<p class="text-muted mb-0">Chưa có câu hỏi được ghi nhận. Dữ liệu sẽ xuất hiện khi khách sử dụng tư vấn AI.</p>@endforelse</div>
    </div></div>
    <div class="d-flex align-items-center justify-content-between gap-3 mb-3"><div><h4 class="mb-1">Gợi ý bổ sung sản phẩm</h4><p class="text-muted small mb-0">Ưu tiên nhu cầu có lượt tìm chưa khớp, sau đó đến số tài khoản / phiên hỏi. Dựa trên dữ liệu trong {{ $days }} ngày, không phải dự báo doanh số.</p></div></div>
    <div class="row g-3 mb-4">
    @forelse($demands as $demand)
        <div class="col-xl-6"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
            <span class="badge {{ $demand->missing > 0 ? 'bg-warning text-dark' : 'bg-success' }} mb-3">{{ $demand->missing > 0 ? 'Cần xem xét bổ sung' : 'Đã có kết quả gợi ý' }}</span>
            <h5>{{ $demand->label }}</h5>
            <p class="text-muted small">{{ $demand->total }} lượt hỏi · {{ $demand->visitors }} tài khoản / phiên · {{ $demand->missing }} lượt chưa khớp</p>
            <p>{{ $demand->missing > 0 ? 'Cân nhắc thêm sản phẩm hoặc biến thể đáp ứng điều kiện trên. Kiểm tra cả hàng hết kho và thông số còn thiếu trước khi quyết định nhập mới.' : 'Ưu tiên giới thiệu các mẫu đang phù hợp; kiểm tra sức mua và tồn kho trước khi nhập thêm.' }}</p>
            <form method="post" action="{{ route('admin.ai-demands.update',$demand->demand_key) }}">@csrf @method('PUT')
                <label class="form-label small" for="status-{{ $loop->index }}">Kế hoạch xử lý</label>
                <select class="form-select mb-2" id="status-{{ $loop->index }}" name="status">@foreach(['reviewing'=>'Đang xem xét','planned'=>'Dự kiến bổ sung','done'=>'Đã xử lý','dismissed'=>'Chưa triển khai'] as $key=>$label)<option value="{{ $key }}" @selected(($demand->plan?->status ?? 'reviewing')===$key)>{{ $label }}</option>@endforeach</select>
                <label class="visually-hidden" for="note-{{ $loop->index }}">Ghi chú kế hoạch</label><textarea id="note-{{ $loop->index }}" name="note" class="form-control mb-3" rows="2" maxlength="1000" placeholder="Ghi chú mẫu cần nhập, nhà cung cấp hoặc việc cần kiểm tra…">{{ $demand->plan?->note }}</textarea>
                <div class="d-flex flex-wrap gap-2"><button class="btn btn-outline-primary btn-sm">Lưu kế hoạch</button><a class="btn btn-primary btn-sm" href="{{ route('admin.products.create') }}">Thêm sản phẩm</a></div>
            </form>
        </div></div></div>
    @empty<div class="col-12"><div class="card border-0 shadow-sm p-4 text-muted">Chưa đủ dữ liệu nhu cầu sản phẩm. Các câu hỏi có loại sản phẩm, tầm giá, màu, phong cách hoặc kích thước sẽ được tổng hợp tại đây.</div></div>@endforelse
    </div>
    <div class="card border-0 shadow-sm mb-3"><div class="card-header py-3"><h5 class="mb-1">Câu hỏi được hỏi nhiều nhất</h5><small class="text-muted">Gộp câu giống nhau không phân biệt hoa thường, dấu và dấu câu. Bộ lọc chủ đề áp dụng cho danh sách này.</small></div>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Câu hỏi</th><th>Chủ đề</th><th>Lượt hỏi</th><th>Tài khoản / phiên</th><th>Gần nhất</th></tr></thead><tbody>
        @forelse($questions as $question)<tr><td style="min-width:240px;max-width:500px;overflow-wrap:anywhere">{{ $question->question }}</td><td>{{ \App\Services\AiDemandAnalytics::TOPICS[$question->topic] ?? $question->topic }}</td><td><strong>{{ $question->total }}</strong></td><td>{{ $question->visitors }}</td><td class="text-nowrap">{{ \Carbon\Carbon::parse($question->last_at)->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted p-4">Chưa có câu hỏi trong khoảng thời gian và chủ đề đã chọn.</td></tr>@endforelse
        </tbody></table></div><div class="card-footer">{{ $questions->links() }}</div>
    </div>
    <p class="small text-muted">Thống kê bắt đầu từ khi triển khai tính năng; không lấy lại hội thoại cũ chỉ lưu trong phiên. Lượt chưa khớp phản ánh kết quả tìm kiếm tại lúc hỏi, không khẳng định toàn bộ kho không có hàng. Email và số điện thoại được che tự động; không lưu nội dung AI trả lời hay danh tính tài khoản trong báo cáo. Phiên khách chưa đăng nhập có thể được tính riêng khi đổi trình duyệt. Câu ngoài phạm vi và lỗi AI không được dùng để gợi ý nhập hàng.</p>
</div>
@endsection
