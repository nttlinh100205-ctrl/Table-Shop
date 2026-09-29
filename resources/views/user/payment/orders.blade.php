@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
@php
    $filter = $filter ?? 'all';
    $counts = $counts ?? ['all' => 0, 'unpaid' => 0, 'shipping' => 0, 'done' => 0, 'cancelled' => 0];

    // Label đồng bộ với OrderStatus::ORDER (source of truth)
    // [label dự phòng, màu chip, icon Bootstrap Icons]
    $statusMap = [
        'pending'          => [\App\Support\OrderStatus::orderLabel('pending'),          'warning',   'bi-clock'],
        'paid'             => [\App\Support\OrderStatus::orderLabel('paid'),             'success',   'bi-check-circle'],
        'paid_momo'        => [\App\Support\OrderStatus::orderLabel('paid_momo'),        'success',   'bi-check-circle'],
        'cod_ordered'      => [\App\Support\OrderStatus::orderLabel('cod_ordered'),      'primary',   'bi-cash'],
        'cod_paid'         => [\App\Support\OrderStatus::orderLabel('cod_paid'),         'success',   'bi-cash-coin'],
        'confirmed'        => [\App\Support\OrderStatus::orderLabel('confirmed'),        'info',      'bi-check2-circle'],
        'completed'        => [\App\Support\OrderStatus::orderLabel('completed'),        'success',   'bi-bag-check'],
        'cancel_requested' => [\App\Support\OrderStatus::orderLabel('cancel_requested'), 'warning',   'bi-hourglass'],
        'cancelled'        => [\App\Support\OrderStatus::orderLabel('cancelled'),        'secondary', 'bi-x-circle'],
        'failed'           => [\App\Support\OrderStatus::orderLabel('failed'),           'danger',    'bi-exclamation-circle'],
    ];
    $shipMap = [
        'pending'             => ['Chờ tạo vận đơn',   'secondary', 'bi-clock'],
        'not_shipped'         => ['Chưa giao hàng',    'secondary', 'bi-box'],
        'processing'          => ['Đang xử lý',        'warning',   'bi-gear'],
        'ready_to_pick'       => ['Chờ lấy hàng',      'info',      'bi-box-seam'],
        'picking'             => ['Đang lấy hàng',     'info',      'bi-person-walking'],
        'picked'              => ['Đã lấy hàng',       'info',      'bi-box2'],
        'storing'             => ['Đang lưu kho',      'info',      'bi-boxes'],
        'transporting'        => ['Đang trung chuyển', 'primary',   'bi-truck'],
        'sorting'             => ['Đang phân loại',    'primary',   'bi-arrow-left-right'],
        'delivering'          => ['Đang giao hàng',    'primary',   'bi-truck'],
        'delivered'           => ['Giao thành công',   'success',   'bi-house-check'],
        'return'              => ['Yêu cầu trả hàng',  'warning',   'bi-arrow-return-left'],
        'returning'           => ['Đang hoàn hàng',    'warning',   'bi-arrow-return-left'],
        'returned'            => ['Đã hoàn hàng',      'secondary', 'bi-arrow-counterclockwise'],
        'return_transporting' => ['Đang chuyển hoàn',  'warning',   'bi-truck'],
        'return_sorting'      => ['Đang phân loại hoàn','warning',  'bi-boxes'],
        'cancelled'           => ['Đã hủy vận chuyển', 'secondary', 'bi-x-circle'],
    ];

    $tabs = [
        'all'       => ['Tất cả',          $counts['all']       ?? 0, 'bi-list-ul'],
        'unpaid'    => ['Chờ thanh toán',  $counts['unpaid']    ?? 0, 'bi-clock-history'],
        'shipping'  => ['Đang xử lý & giao', $counts['shipping']  ?? 0, 'bi-truck'],
        'done'      => ['Đã tiếp nhận / giao xong', $counts['done'] ?? 0, 'bi-check-circle'],
        'cancelled' => ['Đã hủy',          $counts['cancelled'] ?? 0, 'bi-x-circle'],
    ];

@endphp

<style>
    .orders-page { padding: 1.5rem 0 4rem; background: #f1f5f9; min-height: 60vh; }

    /* Page header */
    .orders-page-header {
        display: flex; align-items: flex-start; justify-content: space-between;
        flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;
    }
    .orders-heading { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; }
    .orders-sub { font-size: 0.82rem; color: #64748b; margin: 0.15rem 0 0; }
    .btn-shop-more {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.5rem 1rem; border-radius: 9px;
        background: linear-gradient(135deg,#2563eb,#7c3aed);
        color: #fff; font-size: 0.82rem; font-weight: 700;
        text-decoration: none; transition: opacity .15s, transform .15s;
        white-space: nowrap;
    }
    .btn-shop-more:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }

    /* Tabs */
    .orders-tabs {
        display: flex; gap: 0.35rem; overflow-x: auto;
        padding-bottom: 0.5rem; margin-bottom: 1.25rem;
        scrollbar-width: none;
    }
    .orders-tabs::-webkit-scrollbar { display: none; }
    .orders-tab {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.45rem 0.9rem; border-radius: 8px;
        font-size: 0.8rem; font-weight: 600; text-decoration: none;
        transition: all .15s; white-space: nowrap; border: 1.5px solid transparent;
        color: #64748b; background: #fff;
        border-color: #e2e8f0;
    }
    .orders-tab:hover { border-color: #3b82f6; color: #2563eb; }
    .orders-tab.active {
        background: #eff6ff; border-color: #3b82f6;
        color: #2563eb;
    }
    .tab-count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 18px; height: 18px; border-radius: 10px; padding: 0 4px;
        font-size: 0.68rem; font-weight: 700;
        background: #e2e8f0; color: #64748b;
    }
    .orders-tab.active .tab-count { background: #2563eb; color: #fff; }

    /* Order cards */
    .order-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04);
        overflow: hidden; transition: box-shadow .15s;
    }
    .order-card:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.09); }
    .order-card.needs-payment {
        border-color: #fca5a5;
        box-shadow: 0 0 0 2px rgba(239,68,68,0.12), 0 4px 12px rgba(0,0,0,0.04);
    }

    /* Card header */
    .order-card-header {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 0.5rem;
        padding: 0.875rem 1.25rem;
        border-bottom: 1px solid #f8fafc;
        background: #fafbfc;
    }
    .order-id {
        font-size: 0.82rem; font-weight: 800; color: #0f172a;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .order-id .order-num {
        background: #0f172a; color: #fff;
        padding: 0.15rem 0.6rem; border-radius: 6px;
        font-size: 0.75rem;
    }
    .order-date { font-size: 0.75rem; color: #94a3b8; font-weight: 400; }
    .order-total {
        font-size: 1rem; font-weight: 900; color: #dc2626;
    }
    .order-ship-fee { font-size: 0.72rem; color: #94a3b8; }

    /* Card body */
    .order-card-body { padding: 1rem 1.25rem; }
    .order-product-summary {
        font-size: 0.85rem; color: #334155; font-weight: 500;
        margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;
    }
    .order-more-badge {
        display: inline-flex; align-items: center;
        font-size: 0.72rem; background: #f1f5f9; color: #64748b;
        padding: 0.1rem 0.45rem; border-radius: 6px; font-weight: 600;
    }

    /* Badges */
    .status-badges { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.875rem; }
    .status-group { display: flex; flex-direction: column; align-items: flex-start; gap: 0.25rem; }
    .status-group-label { color: #64748b; font-size: 0.65rem; font-weight: 700; }
    .status-chip {
        display: inline-flex; align-items: center; gap: 0.3rem;
        font-size: 0.72rem; font-weight: 700;
        padding: 0.25rem 0.65rem; border-radius: 20px;
        border: 1px solid transparent;
    }
    .status-chip-warning { background: #fef3c7; color: #92400e; border-color: #f59e0b; }
    .status-chip-success { background: #dcfce7; color: #166534; border-color: #22c55e; }
    .status-chip-primary { background: #dbeafe; color: #1e40af; border-color: #3b82f6; }
    .status-chip-info { background: #e0f2fe; color: #0c4a6e; border-color: #0ea5e9; }
    .status-chip-secondary { background: #f1f5f9; color: #475569; border-color: #94a3b8; }
    .status-chip-danger { background: #fee2e2; color: #991b1b; border-color: #ef4444; }

    /* Card footer */
    .order-card-footer {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border-top: 1px solid #f1f5f9;
    }
    .order-recipient {
        display: flex; align-items: center; gap: 0.4rem;
        font-size: 0.78rem; color: #64748b; font-weight: 500;
    }
    .order-actions { display: flex; gap: 0.5rem; }
    .btn-detail {
        padding: 0.4rem 0.9rem; border-radius: 8px;
        font-size: 0.78rem; font-weight: 700;
        background: #fff; color: #0f172a;
        border: 1.5px solid #e2e8f0; text-decoration: none;
        transition: all .15s;
        display: inline-flex; align-items: center; gap: 0.3rem;
    }
    .btn-detail:hover { border-color: #3b82f6; color: #2563eb; }
    .btn-pay-momo {
        padding: 0.4rem 0.9rem; border-radius: 8px;
        font-size: 0.78rem; font-weight: 700;
        background: linear-gradient(135deg,#dc2626,#b91c1c);
        color: #fff; border: none; text-decoration: none;
        transition: opacity .15s;
        display: inline-flex; align-items: center; gap: 0.35rem;
    }
    .btn-pay-momo:hover { opacity: 0.9; color: #fff; }

    /* GHN code */
    .ghn-code {
        display: inline-flex; align-items: center; gap: 0.3rem;
        font-size: 0.7rem; font-weight: 700;
        padding: 0.22rem 0.55rem; border-radius: 6px;
        background: #0f172a; color: #e4cd92;
        font-family: monospace;
    }

    /* Needs payment banner */
    .needs-payment-banner {
        background: #fef2f2; border-bottom: 1px solid #fca5a5;
        padding: 0.45rem 1.25rem;
        display: flex; align-items: center; gap: 0.5rem;
        font-size: 0.78rem; color: #dc2626; font-weight: 700;
    }

    /* Empty state */
    .orders-empty {
        background: #fff; border-radius: 14px; border: 1px solid #e2e8f0;
        padding: 4rem 1.5rem; text-align: center;
    }
    .orders-empty-icon {
        width: 80px; height: 80px; border-radius: 50%;
        background: #f1f5f9; margin: 0 auto 1.25rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: #94a3b8;
    }
    .orders-empty h4 { font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 0.4rem; }
    .orders-empty p { font-size: 0.82rem; color: #64748b; margin-bottom: 1.25rem; }

    /* Flash alerts */
    .flash-alert {
        border-radius: 10px; padding: 0.65rem 1rem;
        margin-bottom: 0.875rem; font-size: 0.85rem;
        display: flex; align-items: center; gap: 0.5rem;
        border: none; border-left: 4px solid;
    }
    .flash-success { background: #f0fdf4; color: #166534; border-color: #22c55e; }
    .flash-danger  { background: #fef2f2; color: #991b1b; border-color: #ef4444; }
    .flash-warning { background: #fffbeb; color: #92400e; border-color: #f59e0b; }
    @media (max-width: 575.98px) {
        .orders-page { padding-top: 1rem; }
        .orders-tabs { margin-right: -0.75rem; padding-right: 0.75rem; }
        .order-card-header, .order-card-body, .order-card-footer { padding-left: 0.9rem; padding-right: 0.9rem; }
        .order-card-footer { align-items: flex-start; }
        .order-actions { width: 100%; }
        .order-actions > * { flex: 1; justify-content: center; }
    }
</style>

<div class="orders-page">
<div class="container" style="max-width:960px;">

    {{-- Page Header --}}
    <div class="orders-page-header">
        <div>
            <h1 class="orders-heading">
                <i class="bi bi-bag-heart" style="color:#2563eb;"></i>
                Đơn hàng của tôi
            </h1>
            <p class="orders-sub">Theo dõi thanh toán &amp; vận chuyển đơn hàng</p>
        </div>
        <a href="{{ route('user.home') }}" class="btn-shop-more">
            <i class="bi bi-bag-plus"></i> Mua thêm
        </a>
    </div>

    {{-- Flash messages --}}
    @foreach (['success' => 'flash-success', 'error' => 'flash-danger', 'warning' => 'flash-warning'] as $flash => $cls)
        @if (session($flash))
            <div class="flash-alert {{ $cls }}">
                <i class="bi {{ $cls === 'flash-success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' }}"></i>
                {{ session($flash) }}
            </div>
        @endif
    @endforeach

    {{-- Filter Tabs --}}
    <div class="orders-tabs">
        @foreach ($tabs as $key => [$label, $count, $icon])
            <a href="{{ route('user.orders.index', ['status' => $key]) }}"
               class="orders-tab {{ $filter === $key ? 'active' : '' }}">
                <i class="bi {{ $icon }}"></i>
                {{ $label }}
                <span class="tab-count">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    {{-- Order list --}}
    @if ($orders->isEmpty())
        <div class="orders-empty">
            <div class="orders-empty-icon">
                <i class="bi bi-inbox"></i>
            </div>
            <h4>Không có đơn hàng nào</h4>
            <p>Không có đơn nào trong mục "{{ $tabs[$filter][0] ?? 'này' }}".</p>
            <a href="{{ route('user.home') }}" class="btn-shop-more">
                <i class="bi bi-shop"></i> Khám phá sản phẩm
            </a>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:0.875rem;">
            @foreach ($orders as $order)
                @php
                    $st     = $statusMap[$order->status]         ?? [\App\Support\OrderStatus::orderLabel($order->status), 'secondary', 'bi-dot'];
                    $sh     = $shipMap[$order->shipping_status]  ?? [$order->shipping_status, 'secondary', 'bi-truck'];
                    $canPay = method_exists($order, 'canPayMomo') && $order->canPayMomo();
                    $itemCount = $order->items->sum('quantity');
                    $firstName = optional($order->items->first())->product->name
                        ?? optional($order->items->first())->product_id
                        ?? 'Sản phẩm';
                    $more = max(0, $order->items->count() - 1);
                @endphp


                <div class="order-card {{ $canPay ? 'needs-payment' : '' }}">
                    {{-- Needs payment banner --}}
                    @if($canPay)
                        <div class="needs-payment-banner">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Đơn hàng này chưa được thanh toán — vui lòng thanh toán để xử lý đơn.
                        </div>
                    @endif

                    {{-- Header --}}
                    <div class="order-card-header">
                        <div>
                            <div class="order-id">
                                <span class="order-num">#{{ $order->id }}</span>
                                <span class="order-date">
                                    <i class="bi bi-calendar2 me-1"></i>{{ $order->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div class="order-total">{{ number_format($order->total_price, 0, ',', '.') }}đ</div>
                            @if ($order->ghn_total_fee)
                                <div class="order-ship-fee">
                                    <i class="bi bi-truck"></i>
                                    Ship {{ number_format($order->ghn_total_fee, 0, ',', '.') }}đ
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="order-card-body">
                        <div class="order-product-summary">
                            <i class="bi bi-box-seam" style="color:#64748b;"></i>
                            <span>{{ $firstName }}</span>
                            @if ($more > 0)
                                <span class="order-more-badge">+{{ $more }} SP</span>
                            @endif
                            <span style="color:#94a3b8;">·</span>
                            <span style="color:#64748b;">{{ $itemCount }} sản phẩm</span>
                        </div>

                        <div class="status-badges">
                                                        <div class="status-group">
                                                                <span class="status-group-label">ĐƠN HÀNG &amp; THANH TOÁN</span>
                                                                  <span class="status-chip status-chip-{{ $st[1] }}">
                                                                        <i class="bi {{ $st[2] }}"></i>{{ $st[0] }}
                                                                </span>
                                                        </div>

                                                        <div class="status-group">
                                                                <span class="status-group-label">VẬN CHUYỂN</span>
                                                                  <span class="status-chip status-chip-{{ $sh[1] }}">
                                                                        <i class="bi {{ $sh[2] }}"></i>{{ $sh[0] }}
                                                                </span>
                                                        </div>

                            {{-- GHN code --}}
                            @if ($order->ghn_order_code)
                                <span class="ghn-code">
                                    <i class="bi bi-upc-scan"></i>{{ $order->ghn_order_code }}
                                </span>
                            @endif
                        </div>

                        @include('user.payment.partials.shipping-progress', ['shippingStatus' => $order->shipping_status])
                    </div>

                    {{-- Footer --}}
                    <div class="order-card-footer">
                        <div class="order-recipient">
                            <i class="bi bi-person-circle" style="font-size:1rem;color:#94a3b8;"></i>
                            <span>{{ $order->name }}</span>
                            @if ($order->phone)
                                <span style="color:#cbd5e1;">·</span>
                                <span>{{ $order->phone }}</span>
                            @endif
                        </div>
                        <div class="order-actions">
                            <a href="{{ route('user.orders.show', $order) }}" class="btn-detail">
                                <i class="bi bi-eye" style="font-size:0.75rem;"></i> Chi tiết
                            </a>
                            @if ($canPay)
                                <a href="{{ route('user.orders.momo.pay', $order) }}" class="btn-pay-momo">
                                    <i class="bi bi-wallet2"></i> Thanh toán MoMo
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($orders->hasPages())
            <div style="margin-top:1.5rem;display:flex;justify-content:center;">
                {{ $orders->onEachSide(1)->links() }}
            </div>
        @endif
    @endif

</div>
</div>
@endsection
