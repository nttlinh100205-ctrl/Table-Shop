@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h2>Chi tiết danh mục</h2>
            <a class="btn btn-secondary" href="{{ route('admin.categories.index') }}">Quay lại</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Tên (Cấp 1):</strong> {{ $category->name }}</p>
            <p><strong>Mô tả:</strong> {{ $category->description ?? 'Không có mô tả' }}</p>
            <p><strong>Số sản phẩm (trực tiếp cấp 1):</strong> {{ $category->products->count() }}</p>
            <p><strong>Ngày tạo:</strong> {{ $category->created_at ? $category->created_at->format('d/m/Y H:i') : 'N/A' }}</p>

            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-primary">Sửa</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header fw-bold">
            Danh mục con
            <span class="badge bg-info text-dark">{{ $category->subCategories->count() }} cấp 2</span>
        </div>
        <div class="card-body p-0">
            @if ($category->subCategories->isEmpty())
                <p class="text-muted p-3 mb-0">Không có danh mục con.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($category->subCategories as $sub)
                        <li class="list-group-item">
                            <strong>{{ $sub->name }}</strong>
                            <span class="badge bg-secondary">Cấp 2</span>
                            @if ($sub->subSubCategories->count())
                                <ul class="mt-2 mb-0">
                                    @foreach ($sub->subSubCategories as $grand)
                                        <li>{{ $grand->name }} <span class="badge bg-light text-dark">Cấp 3</span></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
