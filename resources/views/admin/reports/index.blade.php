@extends('layouts.admin')

@section('title', 'Báo cáo doanh thu')

@section('content')
<div class="container-fluid">
    <h2>Báo cáo doanh thu</h2>
    <nav class="nav nav-pills my-3">
        <a class="nav-link active" href="{{ route('admin.reports.index') }}">Bảng số liệu</a>
        <a class="nav-link" href="{{ route('admin.reports.charts') }}">Biểu đồ</a>
    </nav>
    <p class="text-muted">Doanh thu theo ngày tạo đơn — chỉ gồm đơn đã thanh toán, chưa hủy/hoàn.</p>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card card-body h-100 shadow-sm">
                <span class="text-muted">Tổng số đơn hàng</span>
                <h3 class="mb-0">{{ number_format($totalOrders) }}</h3>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card card-body h-100 shadow-sm">
                <span class="text-muted">Tổng số khách hàng</span>
                <h3 class="mb-0">{{ number_format($totalCustomers) }}</h3>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card card-body h-100 shadow-sm">
                <span class="text-muted">Tổng doanh thu</span>
                <h3 class="mb-0 text-success">{{ number_format($totalRevenue, 0, ',', '.') }} đ</h3>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header"><strong>Doanh thu theo danh mục</strong></div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Danh mục</th>
                        <th class="text-end">Số lượng bán</th>
                        <th class="text-end">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categoryRevenue as $revenue)
                        <tr>
                            <td>{{ $revenue->category_name ?? ('Danh mục #'.$revenue->category_id) }}</td>
                            <td class="text-end">{{ number_format($revenue->total_qty) }}</td>
                            <td class="text-end">{{ number_format($revenue->total_revenue, 0, ',', '.') }} đ</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Chưa có doanh thu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach([
        ['Doanh thu theo ngày', 'Ngày', 'date', $revenueByDate, 'd/m/Y'],
        ['Doanh thu theo tháng', 'Tháng', 'month', $revenueByMonth, 'm/Y'],
        ['Doanh thu theo năm', 'Năm', 'year', $revenueByYear, null],
    ] as [$title, $label, $field, $rows, $format])
        <div class="card mb-4 shadow-sm">
            <div class="card-header fw-semibold">{{ $title }}</div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>{{ $label }}</th>
                            <th class="text-end">Số đơn đã thanh toán</th>
                            <th class="text-end">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $revenue)
                            <tr>
                                <td>
                                    @if($format)
                                        {{ \Carbon\Carbon::parse($revenue->{$field} . ($field === 'month' ? '-01' : ''))->format($format) }}
                                    @else
                                        {{ $revenue->{$field} }}
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($revenue->order_count) }}</td>
                                <td class="text-end">{{ number_format($revenue->total_revenue, 0, ',', '.') }} đ</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Chưa có doanh thu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
