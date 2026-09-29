@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h2>Danh mục</h2>
            <a class="btn btn-success" href="{{ route('admin.categories.create') }}">Thêm danh mục</a>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p class="mb-0">{{ $message }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            <p class="mb-0">{{ session('error') }}</p>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            @forelse ($tree as $i => $category)
                <div class="border-bottom p-3">
                    {{-- Cấp 1 --}}
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong class="fs-5">{{ $category->name }}</strong>
                
                            @if ($category->description)
                                <div class="text-muted small mt-1">{{ \Illuminate\Support\Str::limit($category->description, 80) }}</div>
                            @endif
                        </div>
                        <div class="text-nowrap">
                            <a class="btn btn-info btn-sm" href="{{ route('admin.categories.show', $category->id) }}">Xem</a>
                            <a class="btn btn-primary btn-sm" href="{{ route('admin.categories.edit', $category->id) }}">Sửa</a>
                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Xóa danh mục này và toàn bộ danh mục con?')">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Cấp 2 --}}
                    @if ($category->subCategories->count())
                        <ul class="list-unstyled mt-3 mb-0 ms-3 border-start ps-3">
                            @foreach ($category->subCategories as $sub)
                                <li class="mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-secondary">└</span>
                                        <strong>{{ $sub->name }}</strong>
                                       
                                    </div>

                                    {{-- Cấp 3 --}}
                                    @if ($sub->subSubCategories->count())
                                        <ul class="list-unstyled mt-1 mb-0 ms-4">
                                            @foreach ($sub->subSubCategories as $grand)
                                                <li class="text-muted small">
                                                    <span class="me-1">└</span>{{ $grand->name }}
                                                   
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small mt-2 mb-0 ms-3">Chưa có danh mục con.</p>
                    @endif
                </div>
            @empty
                <div class="p-4 text-center text-muted">Chưa có danh mục nào.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
