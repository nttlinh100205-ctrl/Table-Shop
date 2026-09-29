@extends('layouts.admin')

@section('title', 'Biểu đồ báo cáo')

@section('content')
<style>
    .chart-wrap { min-height: 360px; }
    .chart-wrap canvas { width: 100% !important; height: 360px !important; }
</style>

<div class="container-fluid">
    <h2>Biểu đồ báo cáo doanh thu</h2>
    <nav class="nav nav-pills my-3">
        <a class="nav-link" href="{{ route('admin.reports.index') }}">Bảng số liệu</a>
        <a class="nav-link active" href="{{ route('admin.reports.charts') }}">Biểu đồ</a>
    </nav>

    <div id="report-chart-error" class="alert alert-warning d-none">
        Không tải được thư viện biểu đồ. Xem <a href="{{ route('admin.reports.index') }}">Bảng số liệu</a>.
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">Doanh thu theo danh mục</div>
                <div class="card-body chart-wrap"><canvas id="categoryRevenueChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">Doanh thu theo ngày (30 ngày)</div>
                <div class="card-body chart-wrap"><canvas id="revenueByDateChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">Doanh thu theo tháng (12 tháng)</div>
                <div class="card-body chart-wrap"><canvas id="revenueByMonthChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">Doanh thu theo năm</div>
                <div class="card-body chart-wrap"><canvas id="revenueByYearChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">Doanh thu theo phương thức thanh toán</div>
                <div class="card-body chart-wrap"><canvas id="revenueByPaymentMethodChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

@php
    $chartData = [
        'catLabels'             => $catLabels ?? [],
        'catRevenue'            => $catRevenue ?? [],
        'revDateLabels'         => $revDateLabels ?? [],
        'revDateData'           => $revDateData ?? [],
        'revMonthLabels'        => $revMonthLabels ?? [],
        'revMonthData'          => $revMonthData ?? [],
        'revYearLabels'         => $revYearLabels ?? [],
        'revYearData'           => $revYearData ?? [],
        'paymentMethodLabels'   => $paymentMethodLabels ?? [],
        'paymentMethodRevenue'  => $paymentMethodRevenue ?? [],
    ];
@endphp
<div id="report-chart-data" hidden data-chart-data='@json($chartData)'></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') {
        document.getElementById('report-chart-error').classList.remove('d-none');
        return;
    }
    const reportData = JSON.parse(document.getElementById('report-chart-data').dataset.chartData);
    const mk = (el, type, labels, data, label) => new Chart(el, {
        type,
        data: { labels, datasets: [{ label, data, fill: type === 'line', tension: 0.3 }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });
    mk(document.getElementById('categoryRevenueChart'), 'bar', reportData.catLabels, reportData.catRevenue.map(Number), 'Doanh thu (VNĐ)');
    mk(document.getElementById('revenueByDateChart'), 'line', reportData.revDateLabels, reportData.revDateData.map(Number), 'Doanh thu (VNĐ)');
    mk(document.getElementById('revenueByMonthChart'), 'bar', reportData.revMonthLabels, reportData.revMonthData.map(Number), 'Doanh thu (VNĐ)');
    mk(document.getElementById('revenueByYearChart'), 'bar', reportData.revYearLabels, reportData.revYearData.map(Number), 'Doanh thu (VNĐ)');
    new Chart(document.getElementById('revenueByPaymentMethodChart'), {
        type: 'pie',
        data: { labels: reportData.paymentMethodLabels, datasets: [{ data: reportData.paymentMethodRevenue.map(Number) }] },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>
@endsection
