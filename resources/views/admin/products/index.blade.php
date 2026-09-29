@extends('layouts.admin')

@section('title', 'Quản lý Sản phẩm')

@section('content')
@php
    $q = $q ?? request('q', '');
    $categoryId = $categoryId ?? request('category_id');
    $totalProducts = $totalProducts ?? ($products->total() ?? 0);
@endphp

<div class="container-fluid px-0">
    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1" style="color:#0f172a;">Sản phẩm</h2>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted" style="font-size:0.82rem;">Tổng</span>
                <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:0.78rem;font-weight:700;padding:0.25rem 0.6rem;border-radius:20px;">
                    {{ number_format($totalProducts) }} sản phẩm
                </span>
            </div>
        </div>
        <a href="{{ route('admin.products.create') }}"
           class="btn btn-primary d-flex align-items-center gap-2"
           style="border-radius:8px; font-size:0.875rem; font-weight:600; padding:0.5rem 1rem;">
            <i class="bi bi-plus-lg"></i>Thêm sản phẩm
        </a>
    </div>

    {{-- Filter Card --}}
    <form method="GET" action="{{ route('admin.products.index') }}"
          class="admin-card mb-4">
        <div class="admin-card-body" style="padding:1rem 1.25rem;">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;color:#64748b;margin-bottom:0.3rem;">TÌM KIẾM</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-right:none;border-color:#e2e8f0;">
                            <i class="bi bi-search text-muted" style="font-size:0.85rem;"></i>
                        </span>
                        <input type="text" name="q" value="{{ $q }}" class="form-control"
                               placeholder="Tên hoặc mã SP (SKU)..."
                               style="border-left:none;border-color:#e2e8f0;font-size:0.875rem;">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;color:#64748b;margin-bottom:0.3rem;">DANH MỤC</label>
                    <select name="category_id" class="form-select"
                            style="border-color:#e2e8f0;font-size:0.875rem;">
                        <option value="">Tất cả danh mục</option>
                        @foreach ($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" @selected((string)$categoryId === (string)$cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"
                            style="border-radius:8px;font-size:0.875rem;font-weight:600;">
                        <i class="bi bi-funnel me-1"></i>Lọc
                    </button>
                    @if ($q || $categoryId)
                        <a href="{{ route('admin.products.index') }}"
                           class="btn d-flex align-items-center justify-content-center"
                           style="border:1px solid #e2e8f0;border-radius:8px;color:#64748b;background:#f8fafc;"
                           title="Xóa lọc">
                            <i class="bi bi-x-lg" style="font-size:0.85rem;"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    @if ($products->isEmpty())
        {{-- Empty State --}}
        <div class="admin-card">
            <div style="padding:3.5rem 1.5rem;text-align:center;">
                <div style="width:64px;height:64px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-box-seam" style="font-size:1.75rem;color:#94a3b8;"></i>
                </div>
                <p class="fw-semibold mb-1" style="color:#0f172a;">Không tìm thấy sản phẩm</p>
                <p class="text-muted mb-3" style="font-size:0.875rem;">Thử đổi từ khóa hoặc thêm sản phẩm mới.</p>
                <a href="{{ route('admin.products.create') }}"
                   class="btn btn-primary btn-sm"
                   style="border-radius:8px;font-weight:600;">
                    <i class="bi bi-plus-lg me-1"></i>Thêm sản phẩm
                </a>
            </div>
        </div>
    @else
        {{-- Products Table --}}
        <div class="admin-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="padding-left:1.25rem;width:72px;">Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th class="text-end">Giá từ</th>
                            <th class="text-center">Biến thể</th>
                            <th class="text-end" style="padding-right:1.25rem;width:120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            @php
                                $thumb = $product->image
                                    ? asset('storage/' . $product->image)
                                    : ($product->images->first()
                                        ? asset('storage/' . $product->images->first()->path)
                                        : null);
                                $variantCount = $product->variants_count ?? $product->variants->count();
                                $minPrice = $product->min_price ?? $product->price ?? 0;
                            @endphp
                            <tr>
                                <td style="padding-left:1.25rem;">
                                    @if ($thumb)
                                        <img src="{{ $thumb }}" alt="{{ $product->name }}"
                                             style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;">
                                    @else
                                        <div style="width:48px;height:48px;border-radius:8px;border:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold" style="color:#0f172a;font-size:0.875rem;">{{ $product->name }}</div>
                                    <div style="margin-top:3px;">
                                        @if ($product->sku)
                                            <span style="display:inline-block;background:#f8fafc;border:1px solid #e2e8f0;color:#64748b;font-size:0.72rem;font-weight:600;padding:0.15rem 0.5rem;border-radius:5px;font-family:monospace;">
                                                {{ $product->sku }}
                                            </span>
                                        @else
                                            <span style="font-size:0.78rem;color:#cbd5e1;">Không có SKU</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size:0.82rem;color:#64748b;">
                                        {{ $product->category->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span style="font-weight:700;color:#dc2626;font-size:0.9rem;">
                                        {{ number_format($minPrice, 0, ',', '.') }}₫
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span style="display:inline-block;background:#f0fdf4;color:#16a34a;font-size:0.75rem;font-weight:700;padding:0.2rem 0.6rem;border-radius:20px;">
                                        {{ $variantCount }} biến thể
                                    </span>
                                </td>
                                <td class="text-end" style="padding-right:1.25rem;">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('admin.products.show', $product) }}"
                                           class="action-btn action-btn-view" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.products.edit', $product) }}"
                                           class="action-btn action-btn-edit" title="Chỉnh sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Xóa sản phẩm «{{ addslashes($product->name) }}»?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn action-btn-delete" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($products->hasPages())
                <div style="padding:1rem 1.25rem;border-top:1px solid #f1f5f9;display:flex;justify-content:center;">
                    {{ $products->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
