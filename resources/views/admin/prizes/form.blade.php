@extends('layouts.admin')
@section('title',$prize->exists?'Sửa phần thưởng':'Thêm phần thưởng')
@section('content')
<div class="container-fluid" style="max-width:900px"><a href="{{ route('admin.prizes.index') }}" class="btn btn-outline-secondary mb-3">← Vòng quay may mắn</a><h2 class="mb-4">{{ $prize->exists?'Sửa phần thưởng':'Thêm phần thưởng' }}</h2>
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form class="card border-0 shadow-sm p-4" method="POST" action="{{ $prize->exists?route('admin.prizes.update',$prize):route('admin.prizes.store') }}">
@csrf @if($prize->exists) @method('PUT') @endif
<div class="row g-3">
<div class="col-12"><label for="prize-name" class="form-label">Tên hiển thị trên bánh xe</label><input id="prize-name" name="name" class="form-control" maxlength="60" required value="{{ old('name',$prize->name) }}"><small class="text-muted">Ví dụ: 10.000 điểm, Voucher 50.000đ, +2 lượt quay.</small></div>
<div class="col-md-6"><label class="form-label" for="prize-type">Loại phần thưởng</label><select id="prize-type" name="type" class="form-select">@foreach(['points'=>'Điểm thành viên','voucher'=>'Voucher giảm tiền (đ)','ticket'=>'Lượt quay','empty'=>'Không trúng thưởng'] as $key=>$label)<option value="{{ $key }}" @selected(old('type',$prize->type)===$key)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-6"><label for="prize-value" class="form-label">Giá trị</label><input id="prize-value" name="value" type="number" min="0" max="100000000" step="1" required class="form-control" value="{{ old('value',(int)$prize->value) }}"><small class="text-muted">Voucher dùng một lần, có hạn 30 ngày. Không trúng thưởng dùng giá trị 0.</small></div>
<div class="col-md-6"><label for="prize-weight" class="form-label">Trọng số xác suất</label><input id="prize-weight" name="probability" type="number" min="0" max="1000000" step="1" required class="form-control" value="{{ old('probability',$prize->probability) }}"><small class="text-muted">Trọng số 0: không thể trúng.</small></div>
<div class="col-md-6"><label for="prize-quantity" class="form-label">Số lượng còn lại</label><input id="prize-quantity" name="quantity" type="number" min="-1" max="1000000" step="1" required class="form-control" value="{{ old('quantity',$prize->quantity) }}"><small class="text-muted">-1: không giới hạn; 0: hết giải.</small></div>
<div class="col-12"><input type="hidden" name="is_active" value="0"><div class="form-check form-switch"><input id="prize-active" class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active',$prize->is_active))><label for="prize-active" class="form-check-label">Hiển thị và cho phép trao giải</label></div></div>
</div><div class="mt-4"><button class="btn btn-primary px-4">Lưu phần thưởng</button></div></form></div>
@endsection
