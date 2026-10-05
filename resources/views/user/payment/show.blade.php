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
        padding: 2rem 0 4.5rem;
        background: #FAF6F0;
        font-family: 'Manrope', sans-serif;
        color: #3A2E26;
    }
    .order-detail-container { max-width: 1120px; }
    .order-detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .order-back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #7E7065;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: color 0.15s;
    }
    .order-back-link:hover { color: #5A4536; }
    .order-detail-kicker {
        margin: 0 0 0.2rem;
        color: #7E7065;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .order-detail-title {
        margin: 0;
        color: #3A2E26;
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 2rem;
        font-weight: 600;
        letter-spacing: -0.01em;
    }
    .order-detail-date { margin: 0.25rem 0 0; color: #7E7065; font-size: 0.82rem; }
    .order-detail-page .card {
        overflow: hidden;
        border: 1px solid #E6D8C8 !important;
        border-radius: 2px;
        background: #fff;
        box-shadow: 0 4px 20px rgba(90, 69, 54, 0.04) !important;
    }
    .order-detail-page .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.25rem 0.85rem;
        color: #3A2E26;
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-size: 1.15rem;
        font-weight: 600;
        background: #FAF6F0;
        border-bottom: 1px solid #E6D8C8;
    }
    .order-detail-page .card-body:not(.p-0) { padding: 1.2rem 1.25rem; }
    .detail-info-list { display: grid; gap: 0.9rem; margin: 0; }
    .detail-info-row { display: flex; align-items: flex-start; gap: 0.7rem; }
    .detail-info-icon {
        display: grid;
        flex: 0 0 2rem;
        width: 2rem;
        height: 2rem;
        place-items: center;
        border-radius: 2px;
        background: #F3E9DC;
        color: #5A4536;
        border: 1px solid #E6D8C8;
    }
    .detail-info-label { display: block; margin-bottom: 0.1rem; color: #7E7065; font-size: 0.72rem; }
    .detail-info-value { color: #3A2E26; font-size: 0.88rem; font-weight: 600; overflow-wrap: anywhere; }
    .detail-status-list { display: grid; gap: 0.65rem; margin-bottom: 0.9rem; }
    .detail-status-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem; }
    .detail-status-label { color: #7E7065; font-size: 0.78rem; }
    .detail-status-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid #E6D8C8;
    }
    .detail-meta-box { min-width: 0; }
    .detail-meta-value { display: block; color: #3A2E26; font-size: 0.84rem; font-weight: 700; overflow-wrap: anywhere; }
    .detail-section-gap { margin-top: 1rem; }
    .detail-section-count { color: #7E7065; font-size: 0.75rem; font-weight: 600; }
    .order-items-table { min-width: 560px; }
    .order-items-table thead th {
        padding: 0.85rem 1rem;
        color: #7E7065;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        background: #FAF6F0;
        border-bottom: 1px solid #E6D8C8;
    }
    .order-items-table tbody td { padding: 1rem; color: #3A2E26; font-size: 0.86rem; border-bottom: 1px solid #FAF6F0; }
    .order-items-table tfoot th { padding: 1rem; border-top: 1px solid #E6D8C8; font-family: 'Cormorant Garamond', serif; font-size: 1.15rem; color: #3A2E26; }
    .order-actions-panel { display: flex; flex-wrap: wrap; gap: 0.6rem; margin-top: 1rem; }
    .order-actions-panel .btn { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 2px; }
    .inspection-note { border-left: 4px solid #5A4536 !important; background: #FAF6F0; color: #5A4536; }
    .momo-help {
        border: 1px solid #E6D8C8;
        border-radius: 2px;
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
                            @if($order->status === 'completed')
                                <th class="text-center" style="min-width: 150px;">Đánh giá</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            @php
                                $itemReview = $order->reviews->firstWhere('product_id', $item->product_id);
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item->product->name ?? ('SP #' . $item->product_id) }}</div>
                                    @if(!empty($item->product->image))
                                        <small class="text-muted">Mã SP: #{{ $item->product_id }}</small>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ trim(($item->size_label ?? '') . ' / ' . ($item->color ?? ''), ' /') ?: '—' }}
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">
                                    {{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ
                                </td>
                                @if($order->status === 'completed')
                                    <td class="text-center">
                                        @if($itemReview)
                                            <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1" title="Bạn đã đánh giá sản phẩm này">
                                                @for($s = 1; $s <= 5; $s++)
                                                    <i class="bi bi-star{{ $s <= $itemReview->rating ? '-fill text-warning' : ' text-muted' }}"></i>
                                                @endfor
                                                <span class="ms-1 fw-bold">{{ $itemReview->rating }}/5</span>
                                            </span>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning text-dark fw-bold btn-open-review"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#reviewModal"
                                                    data-product-id="{{ $item->product_id }}"
                                                    data-product-name="{{ $item->product->name ?? ('SP #' . $item->product_id) }}">
                                                <i class="bi bi-star-fill text-warning me-1"></i>Đánh giá ngay
                                            </button>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php $colSpan = $order->status === 'completed' ? 4 : 3; @endphp
                        @if(($order->discount_amount ?? 0) > 0)
                            <tr>
                                <th colspan="{{ $colSpan }}" class="text-end text-muted fw-normal">Tạm tính hàng hóa</th>
                                <th class="text-end text-muted fw-normal">
                                    {{ number_format($order->total_price + $order->discount_amount, 0, ',', '.') }}đ
                                </th>
                            </tr>
                            <tr>
                                <th colspan="{{ $colSpan }}" class="text-end text-success fw-normal">
                                    <i class="bi bi-tag-fill me-1"></i>Giảm giá khuyến mãi
                                    @if($order->coupon_code)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">{{ $order->coupon_code }}</span>
                                    @endif
                                </th>
                                <th class="text-end text-success fw-bold">
                                    -{{ number_format($order->discount_amount, 0, ',', '.') }}đ
                                </th>
                            </tr>
                        @endif
                        <tr>
                            <th colspan="{{ $colSpan }}" class="text-end">Tổng tiền hàng</th>
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

    {{-- ===== SECTION: ĐÁNH GIÁ SẢN PHẨM TỪ BẠN ===== --}}
    @if ($order->reviews->isNotEmpty())
        <section class="card detail-section-gap border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <span class="fw-bold" style="font-size: 1.05rem; color: #5A4536;">
                    <i class="bi bi-chat-heart me-2 text-warning"></i>Đánh giá trải nghiệm của bạn ({{ $order->reviews->count() }})
                </span>
                <span class="badge bg-warning-subtle text-dark border border-warning">Đã xác nhận mua hàng</span>
            </div>
            <div class="card-body p-4" style="background: #FAF6F0;">
                <div class="row g-3">
                    @foreach ($order->reviews as $rev)
                        @php
                            $revProduct = $order->items->firstWhere('product_id', $rev->product_id)?->product;
                        @endphp
                        <div class="col-12">
                            <div class="p-3 bg-white rounded-3 border" style="border-color: #E6D8C8 !important;">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">
                                            {{ $revProduct->name ?? ('Sản phẩm #' . $rev->product_id) }}
                                        </h6>
                                        <div class="text-warning small mb-1">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="bi bi-star{{ $i <= $rev->rating ? '-fill text-warning' : ' text-muted' }}"></i>
                                            @endfor
                                            <span class="ms-1 fw-bold text-dark">{{ $rev->rating }}/5 sao</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $rev->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <p class="mb-2 text-secondary" style="white-space: pre-line; line-height: 1.6;">{{ $rev->comment }}</p>

                                @if (!empty($rev->images) && is_array($rev->images))
                                    <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                                        @foreach ($rev->images as $imgUrl)
                                            <a href="{{ $imgUrl }}" target="_blank" rel="noopener noreferrer" class="d-inline-block position-relative rounded overflow-hidden shadow-sm" style="width: 80px; height: 80px; border: 1px solid #E6D8C8;">
                                                <img src="{{ $imgUrl }}" alt="Ảnh đánh giá" style="width: 100%; height: 100%; object-fit: cover;">
                                                <span class="position-absolute bottom-0 end-0 bg-dark text-white px-1" style="font-size: 0.65rem; opacity: 0.85;">
                                                    <i class="bi bi-arrows-fullscreen"></i>
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
</div>

{{-- ===== MODAL ĐÁNH GIÁ SẢN PHẨM ===== --}}
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: 1px solid #E6D8C8;">
            <form action="{{ route('user.reviews.store', $order->id) }}" method="POST" enctype="multipart/form-data" id="reviewForm">
                @csrf
                <input type="hidden" name="product_id" id="review_product_id" value="">

                <div class="modal-header text-white" style="background: linear-gradient(135deg, #5A4536, #3F2F24);">
                    <h5 class="modal-title fw-bold" id="reviewModalLabel">
                        <i class="bi bi-star-fill text-warning me-2"></i>Đánh giá sản phẩm
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4" style="background: #FAF6F0;">
                    <div class="mb-3 p-2 bg-white rounded border d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-primary fs-4"></i>
                        <div>
                            <small class="text-muted d-block">Sản phẩm đánh giá:</small>
                            <strong id="review_product_name" class="text-dark">Tên sản phẩm</strong>
                        </div>
                    </div>

                    {{-- Chọn số sao tương tác --}}
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold d-block mb-1">Mức độ hài lòng của bạn</label>
                        <div class="star-rating d-inline-flex gap-2 py-1 px-3 bg-white rounded-pill border" id="starContainer">
                            @for ($s = 1; $s <= 5; $s++)
                                <i class="bi bi-star-fill star-item fs-3" data-rating="{{ $s }}" style="cursor: pointer; color: #ffc107; transition: transform 0.15s;"></i>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="rating_input" value="5">
                        <div class="small fw-bold text-muted mt-1" id="rating_label">5/5 - Rất hài lòng</div>
                    </div>

                    {{-- Nội dung trải nghiệm --}}
                    <div class="mb-3">
                        <label for="review_comment" class="form-label fw-bold">Chia sẻ trải nghiệm thực tế <span class="text-danger">*</span></label>
                        <textarea name="comment" 
                                  id="review_comment" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Chất lượng bàn ghế, đóng gói, độ bền và cảm nhận khi sử dụng..."
                                  required 
                                  minlength="5" 
                                  maxlength="2000"
                                  style="border-color: #E6D8C8; font-size: 0.95rem;"></textarea>
                        <div class="form-text">Tối thiểu 5 ký tự. Đóng góp của bạn giúp cộng đồng chọn được sản phẩm tốt hơn.</div>
                    </div>

                    {{-- Upload ảnh thực tế Cloudinary --}}
                    <div class="mb-2">
                        <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-camera me-1"></i>Hình ảnh thực tế (Tối đa 5 ảnh)</span>
                            <small class="text-muted fw-normal">Định dạng JPG, PNG, WEBP (≤5MB)</small>
                        </label>
                        <input type="file" 
                               name="images[]" 
                               id="review_images_input" 
                               class="form-control" 
                               accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" 
                               multiple
                               style="border-color: #E6D8C8;">
                        <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn text-white fw-bold px-4" id="btnSubmitReview" style="background: #5A4536;">
                        <i class="bi bi-send me-1"></i>Gửi đánh giá
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Mở modal đánh giá và gán đúng product_id + product_name
        const reviewModal = document.getElementById('reviewModal');
        const productIdInput = document.getElementById('review_product_id');
        const productNameLabel = document.getElementById('review_product_name');

        document.querySelectorAll('.btn-open-review').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const pid = this.getAttribute('data-product-id');
                const pname = this.getAttribute('data-product-name');
                if (productIdInput) productIdInput.value = pid;
                if (productNameLabel) productNameLabel.textContent = pname;
            });
        });

        // 2. Tương tác sao rating
        const starLabels = {
            1: '1/5 - Rất tệ',
            2: '2/5 - Chưa hài lòng',
            3: '3/5 - Bình thường',
            4: '4/5 - Hài lòng',
            5: '5/5 - Rất hài lòng, tuyệt vời!'
        };
        const stars = document.querySelectorAll('#starContainer .star-item');
        const ratingInput = document.getElementById('rating_input');
        const ratingLabel = document.getElementById('rating_label');

        function updateStars(val) {
            stars.forEach(function (st) {
                const r = parseInt(st.getAttribute('data-rating'));
                if (r <= val) {
                    st.style.color = '#ffc107';
                    st.classList.remove('bi-star');
                    st.classList.add('bi-star-fill');
                } else {
                    st.style.color = '#cbd5e1';
                    st.classList.remove('bi-star-fill');
                    st.classList.add('bi-star');
                }
            });
            if (ratingLabel) {
                ratingLabel.textContent = starLabels[val] || (val + '/5 sao');
            }
        }

        stars.forEach(function (st) {
            st.addEventListener('mouseenter', function () {
                const r = parseInt(this.getAttribute('data-rating'));
                updateStars(r);
            });
            st.addEventListener('click', function () {
                const r = parseInt(this.getAttribute('data-rating'));
                if (ratingInput) ratingInput.value = r;
                updateStars(r);
            });
        });

        const starContainer = document.getElementById('starContainer');
        if (starContainer) {
            starContainer.addEventListener('mouseleave', function () {
                const currentVal = parseInt(ratingInput ? ratingInput.value : 5);
                updateStars(currentVal);
            });
        }

        // 3. Xem trước ảnh tải lên (Preview)
        const imgInput = document.getElementById('review_images_input');
        const previewBox = document.getElementById('imagePreviewContainer');

        if (imgInput && previewBox) {
            imgInput.addEventListener('change', function () {
                previewBox.innerHTML = '';
                const files = Array.from(this.files);
                if (files.length > 5) {
                    alert('Chỉ được chọn tối đa 5 hình ảnh thực tế.');
                    this.value = '';
                    return;
                }

                files.forEach(function (file) {
                    if (!file.type.startsWith('image/')) return;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const thumb = document.createElement('div');
                        thumb.className = 'position-relative rounded overflow-hidden shadow-sm';
                        thumb.style.width = '64px';
                        thumb.style.height = '64px';
                        thumb.style.border = '1px solid #E6D8C8';
                        thumb.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
                        previewBox.appendChild(thumb);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        // 4. Form submit loading state
        const reviewForm = document.getElementById('reviewForm');
        const btnSubmit = document.getElementById('btnSubmitReview');
        if (reviewForm && btnSubmit) {
            reviewForm.addEventListener('submit', function () {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Đang tải ảnh & gửi...`;
            });
        }
    });
</script>
@endsection
