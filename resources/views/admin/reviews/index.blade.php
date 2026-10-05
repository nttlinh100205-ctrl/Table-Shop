@extends('layouts.admin')
@section('title','Đánh giá & phản hồi')
@section('content')
<div class="container-fluid"><h2>Đánh giá & phản hồi</h2><p class="text-muted">Theo dõi trải nghiệm và phản hồi trực tiếp cho khách hàng.</p>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="row g-3 mb-4">@foreach(['Tổng đánh giá'=>$total,'Điểm trung bình'=>number_format($average??0,1).'/5','Chưa hài lòng • cần xử lý'=>$unhappy] as $label=>$value)<div class="col-md-4"><div class="card border-0 shadow-sm p-3"><span class="text-muted">{{ $label }}</span><strong class="fs-3">{{ $value }}</strong></div></div>@endforeach</div>
<form class="card border-0 shadow-sm p-3 mb-4" method="GET"><div class="row g-2">
<div class="col-md-4"><input name="q" class="form-control" placeholder="Tìm tên hoặc email khách" aria-label="Tên hoặc email khách" value="{{ request('q') }}"></div>
<div class="col-md-3"><select name="satisfaction" class="form-select" aria-label="Mức hài lòng">@foreach([''=>'Mọi mức hài lòng','happy'=>'Hài lòng (4–5 sao)','neutral'=>'Bình thường (3 sao)','unhappy'=>'Chưa hài lòng (1–2 sao)'] as $k=>$v)<option value="{{ $k }}" @selected(request('satisfaction','')===$k)>{{ $v }}</option>@endforeach</select></div>
<div class="col-md-3"><select name="status" class="form-select" aria-label="Trạng thái xử lý">@foreach([''=>'Mọi trạng thái','pending'=>'Cần xử lý','resolved'=>'Đã xử lý'] as $k=>$v)<option value="{{ $k }}" @selected(request('status','')===$k)>{{ $v }}</option>@endforeach</select></div><div class="col-md-2"><button class="btn btn-primary w-100">Lọc đánh giá</button></div></div></form>
@forelse($reviews as $review)
<article class="card border-0 shadow-sm mb-3"><div class="card-body p-4"><div class="row g-4"><div class="col-lg-7">
<div class="d-flex flex-wrap gap-2 align-items-center mb-2"><strong>{{ $review->user?->name ?? 'Khách hàng' }}</strong><span class="badge {{ $review->rating<=2?'bg-danger':($review->rating===3?'bg-secondary':'bg-success') }}">{{ $review->rating }}/5 · {{ $review->rating<=2?'Chưa hài lòng':($review->rating===3?'Bình thường':'Hài lòng') }}</span><span class="badge bg-light text-dark border">{{ $review->resolution_status==='resolved'?'Đã xử lý':'Cần xử lý' }}</span></div>
<p class="small text-muted">{{ $review->product?->name }} · <a href="{{ route('admin.orders.show',$review->order_id) }}">Đơn #{{ $review->order_id }}</a> · {{ $review->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</p>
<p style="white-space:pre-line;overflow-wrap:anywhere">{{ $review->comment }}</p>
<div class="d-flex gap-2 flex-wrap">@foreach($review->images??[] as $url)<a href="{{ $url }}" target="_blank" rel="noopener"><img src="{{ $url }}" alt="Ảnh khách đánh giá" width="70" height="70" class="rounded border" style="object-fit:cover"></a>@endforeach</div>
</div><div class="col-lg-5 border-start"><form method="POST" action="{{ route('admin.reviews.update',$review) }}">@csrf @method('PUT')
<label class="form-label" for="reply-{{ $review->id }}">Phản hồi của cửa hàng</label><textarea id="reply-{{ $review->id }}" name="admin_reply" class="form-control mb-2" rows="3" minlength="5" maxlength="2000" required>{{ $review->admin_reply }}</textarea>
<div class="d-flex gap-2"><select name="resolution_status" class="form-select" aria-label="Trạng thái đánh giá #{{ $review->id }}"><option value="pending" @selected($review->resolution_status==='pending')>Cần xử lý</option><option value="resolved" @selected($review->resolution_status==='resolved')>Đã xử lý</option></select><button class="btn btn-primary text-nowrap">Lưu phản hồi</button></div><small class="text-muted">Phản hồi hiển thị cho khách ở đơn hàng và trang sản phẩm.</small></form></div></div></div></article>
@empty<div class="card p-5 text-center text-muted">Không có đánh giá phù hợp.</div>@endforelse
{{ $reviews->links() }}</div>
@endsection
