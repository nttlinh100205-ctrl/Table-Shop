@extends('layouts.app')

@section('title', 'Chi tiết đơn #' . $order->id)

@section('content')
@php
    $statusMap = [
        'pending'     => ['Chờ xử lý', 'bg-warning text-dark'],
        'paid'        => ['Đã thanh toán', 'bg-success'],
        'paid_momo'   => ['Đã thanh toán MoMo', 'bg-success'],
        'cod_ordered' => ['COD — đã lên đơn', 'bg-primary'],
        'confirmed'   => ['Đã xác nhận', 'bg-info text-dark'],
        'completed'   => ['Hoàn thành', 'bg-success'],
        'cancel_requested' => ['Chờ duyệt hủy', 'bg-warning text-dark'],
        'cancelled'   => ['Đã hủy', 'bg-secondary'],
        'failed'      => ['Thất bại', 'bg-danger'],
    ];
    $shipMap = [
        'pending'             => ['Chờ tạo vận đơn', 'bg-light text-dark border'],
        'not_shipped'         => ['Chưa giao hàng', 'bg-light text-dark border'],
        'processing'          => ['Đang xử lý', 'bg-warning text-dark'],
        'ready_to_pick'       => ['Chờ lấy hàng', 'bg-info text-dark'],
        'picking'             => ['Đang lấy hàng', 'bg-info text-dark'],
        'picked'              => ['Đã lấy hàng', 'bg-info text-dark'],
        'storing'             => ['Đang lưu kho', 'bg-info text-dark'],
        'transporting'        => ['Đang trung chuyển', 'bg-primary'],
        'sorting'             => ['Đang phân loại', 'bg-primary'],
        'delivering'          => ['Đang giao hàng', 'bg-primary'],
        'delivered'           => ['Giao thành công', 'bg-success'],
        'return'              => ['Yêu cầu trả hàng', 'bg-warning text-dark'],
        'returning'           => ['Đang hoàn hàng', 'bg-warning text-dark'],
        'returned'            => ['Đã hoàn hàng', 'bg-secondary'],
        'return_transporting' => ['Đang chuyển hoàn', 'bg-warning text-dark'],
        'return_sorting'      => ['Đang phân loại hoàn', 'bg-warning text-dark'],
        'cancelled'           => ['Đã hủy vận chuyển', 'bg-secondary'],
    ];
    $returnMap = [
        'requested' => ['Chờ shop duyệt', 'bg-warning text-dark'],
        'approved'  => ['Đã duyệt — đang hoàn', 'bg-info text-dark'],
        'rejected'  => ['Bị từ chối', 'bg-danger'],
        'completed' => ['Hoàn tất trả hàng', 'bg-success'],
    ];
    $st = $statusMap[$order->status] ?? [$order->status, 'bg-secondary'];
    $st[0] = \App\Support\OrderStatus::orderLabel($order->status);
    $sh = $shipMap[$order->shipping_status] ?? [$order->shipping_status, 'bg-light text-dark border'];
    $canPay = method_exists($order, 'canPayMomo') && $order->canPayMomo();
    $paidMomo = $order->paymentTransactions->contains(fn ($transaction) => $transaction->gateway === 'momo' && $transaction->status === 'paid');
    $canRequestCancel = in_array($order->shipping_status, ['pending', 'processing', 'ready_to_pick'], true)
        && !in_array($order->status, ['cancelled', 'cancel_requested'], true);

    $fmtTime = function ($value) {
        if (empty($value)) {
            return '—';
        }
        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }
        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };
@endphp

<style>
    .order-detail-page {
        min-height: 60vh;
        padding: 1.5rem 0 3.5rem;
        background: #f1f5f9;
    }
    .order-detail-container { max-width: 1120px; }
    .order-detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .order-back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }
    .order-back-link:hover { color: #1d4ed8; }
    .order-detail-kicker {
        margin: 0 0 0.2rem;
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 700;
    }
    .order-detail-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.45rem;
        font-weight: 800;
    }
    .order-detail-date { margin: 0.25rem 0 0; color: #64748b; font-size: 0.8rem; }
    .order-detail-page .card {
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05) !important;
    }
    .order-detail-page .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.25rem 0.75rem;
        color: #0f172a;
        font-size: 0.92rem;
        font-weight: 750;
    }
    .order-detail-page .card-body:not(.p-0) { padding: 1rem 1.25rem 1.2rem; }
    .detail-info-list { display: grid; gap: 0.9rem; margin: 0; }
    .detail-info-row { display: flex; align-items: flex-start; gap: 0.7rem; }
    .detail-info-icon {
        display: grid;
        flex: 0 0 2rem;
        width: 2rem;
        height: 2rem;
        place-items: center;
        border-radius: 6px;
        background: #eff6ff;
        color: #2563eb;
    }
    .detail-info-label { display: block; margin-bottom: 0.1rem; color: #64748b; font-size: 0.72rem; }
    .detail-info-value { color: #0f172a; font-size: 0.87rem; font-weight: 600; overflow-wrap: anywhere; }
    .detail-status-list { display: grid; gap: 0.65rem; margin-bottom: 0.9rem; }
    .detail-status-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem; }
    .detail-status-label { color: #64748b; font-size: 0.78rem; }
    .detail-status-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid #e2e8f0;
    }
    .detail-meta-box { min-width: 0; }
    .detail-meta-value { display: block; color: #0f172a; font-size: 0.82rem; font-weight: 700; overflow-wrap: anywhere; }
    .detail-section-gap { margin-top: 1rem; }
    .detail-section-count { color: #64748b; font-size: 0.75rem; font-weight: 600; }
    .order-items-table { min-width: 560px; }
    .order-items-table thead th {
        padding: 0.75rem 1rem;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        background: #f8fafc;
        border-bottom-color: #e2e8f0;
    }
    .order-items-table tbody td { padding: 0.9rem 1rem; color: #334155; font-size: 0.84rem; }
    .order-items-table tfoot th { padding: 1rem; border-top: 1px solid #e2e8f0; }
    .order-actions-panel { display: flex; flex-wrap: wrap; gap: 0.6rem; margin-top: 1rem; }
    .order-actions-panel .btn { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; }
    .inspection-note { border-left: 4px solid #0ea5e9 !important; background: #f0f9ff; color: #075985; }
    .momo-help {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
    }
    .momo-help summary { padding: 0.9rem 1rem; color: #334155; font-size: 0.85rem; font-weight: 700; cursor: pointer; }
    .momo-help-content { padding: 0 1rem 1rem; }
    .order-detail-alert { border-radius: 7px; font-size: 0.85rem; }
    @media (max-width: 767.98px) {
        .order-detail-page { padding-top: 1rem; }
        .order-detail-header { align-items: flex-start; flex-direction: column-reverse; gap: 0.75rem; }
        .order-detail-page .card-header { padding: 0.9rem 1rem 0.7rem; }
        .order-detail-page .card-body { padding: 0.9rem 1rem 1rem; }
        .order-actions-panel { display: grid; grid-template-columns: 1fr; }
        .order-actions-panel > *, .order-actions-panel form, .order-actions-panel form .btn { width: 100%; }
    }
</style>

<div class="order-detail-page">
<div class="container order-detail-container">
    <header class="order-detail-header">
        <div>
            <p class="order-detail-kicker">THEO DÕI ĐƠN HÀNG</p>
            <h1 class="order-detail-title">Chi tiết đơn #{{ $order->id }}</h1>
            <p class="order-detail-date"><i class="bi bi-calendar3 me-1"></i>Đặt lúc {{ $fmtTime($order->created_at) }}</p>
        </div>
        <a href="{{ route('user.orders.index') }}" class="order-back-link">
            <i class="bi bi-arrow-left"></i> Danh sách đơn hàng
        </a>
    </header>

    @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show order-detail-alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show order-detail-alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show order-detail-alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->has('cancel_reason'))
        <div class="alert alert-danger order-detail-alert">{{ $errors->first('cancel_reason') }}</div>
    @endif
    @if ($order->status === 'cancel_requested')
        <div class="alert alert-warning order-detail-alert">
            Yêu cầu hủy đang chờ admin xử lý. Lý do: {{ $order->cancel_reason }}
        </div>
    @elseif ($order->cancel_reason && $order->cancel_processed_at)
        <div class="alert order-detail-alert {{ $order->status === 'cancelled' ? 'alert-success' : 'alert-danger' }}">
            Yêu cầu hủy {{ $order->status === 'cancelled' ? 'đã được chấp nhận' : 'đã bị từ chối' }}.
            @if ($order->cancel_admin_note)
                Ghi chú shop: {{ $order->cancel_admin_note }}
            @endif
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <section class="card h-100">
                <div class="card-header bg-white border-0">
                    <span><i class="bi bi-geo-alt me-2 text-primary"></i>Thông tin người nhận</span>
                </div>
                <div class="card-body">
                    <dl class="detail-info-list">
                        <div class="detail-info-row">
                            <span class="detail-info-icon"><i class="bi bi-person"></i></span>
                            <div><dt class="detail-info-label">Người nhận</dt><dd class="detail-info-value mb-0">{{ $order->name }}</dd></div>
                        </div>
                        <div class="detail-info-row">
                            <span class="detail-info-icon"><i class="bi bi-telephone"></i></span>
                            <div><dt class="detail-info-label">Số điện thoại</dt><dd class="detail-info-value mb-0">{{ $order->phone }}</dd></div>
                        </div>
                        <div class="detail-info-row">
                            <span class="detail-info-icon"><i class="bi bi-geo-alt"></i></span>
                            <div><dt class="detail-info-label">Địa chỉ giao hàng</dt><dd class="detail-info-value mb-0">{{ $order->address }}</dd></div>
                        </div>
                    </dl>
                </div>
            </section>
        </div>
        <div class="col-md-6">
            <section class="card h-100">
                <div class="card-header bg-white border-0">
                    <span><i class="bi bi-truck me-2 text-primary"></i>Trạng thái đơn hàng</span>
                </div>
                <div class="card-body">
                    <div class="detail-status-list">
                        <div class="detail-status-row">
                            <span class="detail-status-label">Đơn hàng &amp; thanh toán</span>
                            <span class="badge {{ $st[1] }}">{{ $st[0] }}</span>
                        </div>
                        <div class="detail-status-row">
                            <span class="detail-status-label">Vận chuyển</span>
                            <span class="badge {{ $sh[1] }}">{{ $sh[0] }}</span>
                        </div>
                    </div>
                    @include('user.payment.partials.shipping-progress', ['shippingStatus' => $order->shipping_status])
                    <div class="detail-status-meta">
                        <div class="detail-meta-box">
                            <span class="detail-info-label">Mã vận đơn GHN</span>
                            <span class="detail-meta-value">{{ $order->ghn_order_code ?: 'Chưa có' }}</span>
                        </div>
                        <div class="detail-meta-box">
                            <span class="detail-info-label">Phí vận chuyển</span>
                            <span class="detail-meta-value">{{ number_format($order->ghn_total_fee ?? 0, 0, ',', '.') }}đ <small class="fw-normal text-muted">thu khi nhận</small></span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="alert inspection-note border-0 mb-3 mt-3">
        <div class="d-flex gap-2">
            <i class="bi bi-eye fs-5"></i>
            <div>
                <strong>Được xem hàng khi nhận</strong>
                <div class="small mb-0">
                    Bạn có thể kiểm tra ngoại quan, đúng mẫu / màu / số lượng trước khi nhận.
                    <em>Không hỗ trợ thử lắp hoặc dùng thử tại chỗ.</em>
                    Phí vận chuyển thanh toán cho shipper khi giao hàng.
                </div>
            </div>
        </div>
    </div>

    <section class="card detail-section-gap">
        <div class="card-header bg-white border-0">
            <span><i class="bi bi-box-seam me-2 text-primary"></i>Sản phẩm trong đơn</span>
            <span class="detail-section-count">{{ $order->items->sum('quantity') }} sản phẩm</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle order-items-table">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Size / Màu</th>
                            <th class="text-center">SL</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? ('SP #' . $item->product_id) }}</td>
                                <td class="text-muted small">
                                    {{ trim(($item->size_label ?? '') . ' / ' . ($item->color ?? ''), ' /') ?: '—' }}
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">
                                    {{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Tổng tiền</th>
                            <th class="text-end text-danger fs-5">
                                {{ number_format($order->total_price, 0, ',', '.') }}đ
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>

    @if ($order->return_status)
        <section class="card detail-section-gap">
            <div class="card-header bg-white border-0">
                <span><i class="bi bi-arrow-return-left me-2 text-warning"></i>Thông tin trả hàng</span>
            </div>
            <div class="card-body">
                @php $rs = $returnMap[$order->return_status] ?? [$order->return_status, 'bg-secondary']; @endphp
                <p class="mb-1">Trạng thái: <span class="badge {{ $rs[1] }}">{{ $rs[0] }}</span></p>
                <p class="mb-1"><strong>Lý do của bạn:</strong> {{ $order->return_reason }}</p>
                @if ($order->return_admin_note)
                    <p class="mb-1"><strong>Phản hồi shop:</strong> {{ $order->return_admin_note }}</p>
                @endif
                <p class="mb-0 small text-muted">
                    Gửi lúc {{ $fmtTime($order->return_requested_at) }}
                    @if ($order->return_processed_at)
                        · Xử lý lúc {{ $fmtTime($order->return_processed_at) }}
                    @endif
                </p>
                <div class="alert alert-light border mt-2 mb-0 small">
                    Phí ship <strong>chỉ thu khi nhận hàng</strong>. Nếu bạn từ chối nhận / trả hàng đúng quy định, bạn không phải trả phí ship.
                    @if (in_array($order->status, ['paid', 'paid_momo'], true) || optional($order->paymentTransactions->firstWhere('status', 'paid')))
                        Tiền hàng đã thanh toán sẽ được hoàn sau khi shop xác nhận nhận lại hàng.
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($canPay)
        <details class="momo-help detail-section-gap">
            <summary><i class="bi bi-info-circle me-2 text-primary"></i>Hướng dẫn thanh toán MoMo Sandbox</summary>
            <div class="momo-help-content small text-muted">
                <p>Cổng MoMo Sandbox hỗ trợ cả <strong>Mã QR</strong> và <strong>Thẻ ATM nội địa</strong>. Thông tin thẻ test:</p>
                <div class="row g-2">
                    <div class="col-sm-6">
                        <div>Ngân hàng: <strong>NCB</strong></div>
                        <div>Số thẻ: <code class="text-danger fw-bold">9704198526191432198</code></div>
                    </div>
                    <div class="col-sm-6">
                        <div>Tên chủ thẻ: <strong>NGUYEN VAN A</strong></div>
                        <div>Ngày phát hành: <strong>07/15</strong> · OTP: <strong>000000</strong></div>
                    </div>
                </div>
            </div>
        </details>
    @endif

    <div class="order-actions-panel">
        @if ($canPay)
            <a href="{{ route('user.orders.momo.pay', $order) }}" class="btn btn-danger">
                <i class="bi bi-wallet2 me-1"></i>Thanh toán lại bằng MoMo
            </a>
            <form method="POST" action="{{ route('user.orders.switchCod', $order) }}" class="d-inline"
                  onsubmit="return confirm('Chuyển đơn này sang thanh toán tiền mặt khi nhận hàng (COD)? Vận đơn GHN sẽ được tạo ngay.')">
                @csrf
                <button type="submit" class="btn btn-outline-success">
                    <i class="bi bi-cash-coin me-1"></i>Chuyển sang thanh toán khi nhận hàng (COD)
                </button>
            </form>
        @endif

        @if ($canRequestCancel)
            @if ($paidMomo)
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                    <i class="bi bi-x-circle me-1"></i>Yêu cầu hủy đơn
                </button>
            @else
                <form method="POST" action="{{ route('user.orders.cancel', $order) }}"
                      onsubmit="return confirm('Bạn chắc muốn hủy đơn này?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle me-1"></i>Hủy đơn hàng
                    </button>
                </form>
            @endif
        @endif

        @if ($order->canRequestReturn())
            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#returnModal">
                <i class="bi bi-arrow-return-left me-1"></i>Yêu cầu trả hàng
            </button>
        @endif
    </div>

    @if ($canRequestCancel && $paidMomo)
        <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('user.orders.cancel', $order) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Yêu cầu hủy đơn #{{ $order->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Đơn MoMo cần admin xác nhận. Tiền chỉ được hoàn nếu yêu cầu được chấp nhận.</p>
                        <label for="cancel-reason" class="form-label">Lý do hủy <span class="text-danger">*</span></label>
                        <textarea id="cancel-reason" name="cancel_reason" class="form-control" rows="4"
                                  required minlength="10" maxlength="1000">{{ old('cancel_reason') }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-danger">Gửi yêu cầu</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($order->canRequestReturn())
    <div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('user.orders.return', $order) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Yêu cầu trả hàng — Đơn #{{ $order->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">
                        Áp dụng khi xem hàng không đúng mô tả / lỗi ngoại quan.
                        <strong>Không thử lắp / dùng thử.</strong> Phí ship không thu nếu từ chối nhận.
                    </p>
                    <label class="form-label">Lý do trả hàng <span class="text-danger">*</span></label>
                    <textarea name="return_reason" class="form-control" rows="4" required minlength="10"
                              placeholder="VD: Sai màu, trầy góc bàn, thiếu phụ kiện..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if (method_exists($order, 'paymentTransactions') && $order->relationLoaded('paymentTransactions') && $order->paymentTransactions->isNotEmpty())
        <section class="card detail-section-gap">
            <div class="card-header bg-white border-0">
                <span><i class="bi bi-credit-card me-2 text-primary"></i>Lịch sử giao dịch</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cổng</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th>Mã GD</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->paymentTransactions as $tx)
                                @php
                                    $txBadge = match ($tx->status) {
                                        'paid' => 'bg-success',
                                        'failed' => 'bg-danger',
                                        'initiated' => 'bg-info text-dark',
                                        default => 'bg-secondary',
                                    };
                                    $txTime = $tx->paid_at ?: $tx->created_at;
                                @endphp
                                <tr>
                                    <td class="text-uppercase">{{ $tx->gateway }}</td>
                                    <td>{{ number_format((float) $tx->amount, 0, ',', '.') }}đ</td>
                                    <td><span class="badge {{ $txBadge }}">{{ $tx->status }}</span></td>
                                    <td class="small text-muted">
                                        {{ $tx->transaction_id ?: ($tx->gateway_order_id ?: '—') }}
                                    </td>
                                    <td class="small">{{ $fmtTime($txTime) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif
</div>
</div>
@endsection
