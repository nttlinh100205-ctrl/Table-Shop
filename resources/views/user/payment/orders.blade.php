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
    .orders-page { padding: 2rem 0 5rem; background: #FAF6F0; min-height: 60vh; font-family: 'Manrope', sans-serif; color: #3A2E26; }

    /* Page header */
    .orders-page-header {
        display: flex; align-items: flex-start; justify-content: space-between;
        flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem;
    }
    .orders-heading {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 2rem; font-weight: 600; color: #3A2E26; margin: 0;
        letter-spacing: -0.01em;
    }
    .orders-sub { font-size: 0.84rem; color: #7E7065; margin: 0.25rem 0 0; }
    .btn-shop-more {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.6rem 1.15rem; border-radius: 2px;
        background: #5A4536;
        color: #FAF6F0; font-size: 0.82rem; font-weight: 600;
        text-decoration: none; transition: background .15s, transform .15s;
        white-space: nowrap; letter-spacing: 0.03em;
    }
    .btn-shop-more:hover { background: #3F2F24; transform: translateY(-1px); color: #fff; }

    /* Tabs */
    .orders-tabs {
        display: flex; gap: 0.4rem; overflow-x: auto;
        padding-bottom: 0.5rem; margin-bottom: 1.5rem;
        scrollbar-width: none;
    }
    .orders-tabs::-webkit-scrollbar { display: none; }
    .orders-tab {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.5rem 1rem; border-radius: 2px;
        font-size: 0.82rem; font-weight: 500; text-decoration: none;
        transition: all .15s; white-space: nowrap;
        color: #7E7065; background: #fff;
        border: 1px solid #E6D8C8;
    }
    .orders-tab:hover { border-color: #5A4536; color: #5A4536; }
    .orders-tab.active {
        background: #F3E9DC; border-color: #5A4536;
        color: #5A4536; font-weight: 600;
    }
    .tab-count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 18px; height: 18px; border-radius: 2px; padding: 0 4px;
        font-size: 0.68rem; font-weight: 700;
        background: #FAF6F0; color: #7E7065;
    }
    .orders-tab.active .tab-count { background: #5A4536; color: #FAF6F0; }

    /* Order cards */
    .order-card {
        background: #fff; border-radius: 2px;
        border: 1px solid #E6D8C8;
        box-shadow: 0 4px 20px rgba(90, 69, 54, 0.04);
        overflow: hidden; transition: box-shadow .15s;
    }
    .order-card:hover { box-shadow: 0 6px 24px rgba(90, 69, 54, 0.08); }
    .order-card.needs-payment {
        border-color: #C29D62;
        box-shadow: 0 0 0 1px #C29D62, 0 4px 16px rgba(90, 69, 54, 0.06);
    }

    /* Card header */
    .order-card-header {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 0.5rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid #E6D8C8;
        background: #FAF6F0;
    }
    .order-id {
        font-size: 0.82rem; font-weight: 700; color: #3A2E26;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .order-id .order-num {
        background: #5A4536; color: #FAF6F0;
        padding: 0.2rem 0.6rem; border-radius: 2px;
        font-size: 0.75rem; letter-spacing: 0.03em;
    }
    .order-date { font-size: 0.75rem; color: #7E7065; font-weight: 400; }
    .order-total {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.25rem; font-weight: 700; color: #5A4536;
    }
    .order-ship-fee { font-size: 0.72rem; color: #7E7065; }

    /* Card body */
    .order-card-body { padding: 1.2rem 1.25rem; }
    .order-product-summary {
        font-size: 0.88rem; color: #3A2E26; font-weight: 500;
        margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;
    }
    .order-more-badge {
        display: inline-flex; align-items: center;
        font-size: 0.72rem; background: #F3E9DC; color: #5A4536;
        padding: 0.15rem 0.45rem; border-radius: 2px; font-weight: 600;
    }

    /* Badges */
    .status-badges { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.875rem; }
    .status-group { display: flex; flex-direction: column; align-items: flex-start; gap: 0.25rem; }
    .status-group-label { color: #7E7065; font-size: 0.65rem; font-weight: 700; }
    .status-chip {
        display: inline-flex; align-items: center; gap: 0.3rem;
        font-size: 0.72rem; font-weight: 600;
        padding: 0.25rem 0.65rem; border-radius: 2px;
        border: 1px solid transparent;
    }
    .status-chip-warning { background: #FEF8ED; color: #9A6513; border-color: #EED7A1; }
    .status-chip-success { background: #F0FDF4; color: #166534; border-color: #BBF7D0; }
    .status-chip-primary { background: #F3E9DC; color: #5A4536; border-color: #E6D8C8; }
    .status-chip-info { background: #FAF6F0; color: #5A4536; border-color: #E6D8C8; }
    .status-chip-secondary { background: #FAF6F0; color: #7E7065; border-color: #E6D8C8; }
    .status-chip-danger { background: #FDF2F2; color: #991B1B; border-color: #FECACA; }

    /* Card footer */
    .order-card-footer {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 0.5rem;
        padding: 0.85rem 1.25rem;
        border-top: 1px solid #FAF6F0;
    }
    .order-recipient {
        display: flex; align-items: center; gap: 0.4rem;
        font-size: 0.78rem; color: #7E7065; font-weight: 500;
    }
    .order-actions { display: flex; gap: 0.5rem; }
    .btn-detail {
        padding: 0.45rem 1rem; border-radius: 2px;
        font-size: 0.78rem; font-weight: 600;
        background: transparent; color: #5A4536;
        border: 1px solid #5A4536; text-decoration: none;
        transition: all .15s;
        display: inline-flex; align-items: center; gap: 0.3rem;
    }
    .btn-detail:hover { background: #F3E9DC; color: #3F2F24; border-color: #3F2F24; }
    .btn-pay-momo {
        padding: 0.45rem 1rem; border-radius: 2px;
        font-size: 0.78rem; font-weight: 700;
        background: #A50064;
        color: #fff; border: none; text-decoration: none;
        transition: opacity .15s;
        display: inline-flex; align-items: center; gap: 0.35rem;
    }
    .btn-pay-momo:hover { opacity: 0.9; color: #fff; }

    /* GHN code */
    .ghn-code {
        display: inline-flex; align-items: center; gap: 0.3rem;
        font-size: 0.7rem; font-weight: 700;
        padding: 0.22rem 0.55rem; border-radius: 2px;
        background: #5A4536; color: #F3E9DC;
        font-family: monospace;
    }

    /* Needs payment banner */
    .needs-payment-banner {
        background: #FDF8ED; border-bottom: 1px solid #EED7A1;
        padding: 0.5rem 1.25rem;
        display: flex; align-items: center; gap: 0.5rem;
        font-size: 0.78rem; color: #9A6513; font-weight: 600;
    }

    /* Empty state */
    .orders-empty {
        background: #fff; border-radius: 2px; border: 1px solid #E6D8C8;
        box-shadow: 0 4px 20px rgba(90, 69, 54, 0.04);
        padding: 4rem 1.5rem; text-align: center;
    }
    .orders-empty-icon {
        width: 80px; height: 80px; border-radius: 50%;
        background: #FAF6F0; margin: 0 auto 1.25rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: #5A4536;
    }
    .orders-empty h4 {
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.35rem; font-weight: 600; color: #3A2E26; margin-bottom: 0.4rem;
    }
    .orders-empty p { font-size: 0.84rem; color: #7E7065; margin-bottom: 1.25rem; }

    /* Flash alerts */
    .flash-alert {
        border-radius: 2px; padding: 0.75rem 1.25rem;
        margin-bottom: 1rem; font-size: 0.85rem;
        display: flex; align-items: center; gap: 0.5rem;
        border: 1px solid #E6D8C8; background: #fff;
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
