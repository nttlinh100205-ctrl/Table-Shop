@extends('layouts.admin')

@section('title', 'Đơn #' . $order->id)

@section('content')
<style>
.badge.bg-success { background-color: #ecfdf5 !important; color: #065f46 !important; border: 1px solid #a7f3d0 !important; }
.badge.bg-primary { background-color: #eff6ff !important; color: #1e40af !important; border: 1px solid #bfdbfe !important; }
.badge.bg-danger  { background-color: #fef2f2 !important; color: #991b1b !important; border: 1px solid #fecdd3 !important; }
.badge.bg-warning { background-color: #fffbeb !important; color: #b45309 !important; border: 1px solid #fde68a !important; }
.badge.bg-info    { background-color: #f0fdfa !important; color: #0f766e !important; border: 1px solid #99f6e4 !important; }
.badge.bg-secondary { background-color: #f8fafc !important; color: #475569 !important; border: 1px solid #e2e8f0 !important; }
.badge { font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 9999px; }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Đơn hàng #{{ $order->id }}</h2>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">← Danh sách</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Khách hàng</div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $order->name }}</strong></p>
                    <p class="mb-1">{{ $order->phone }}</p>
                    <p class="mb-0 text-muted">{{ $order->address }}</p>
                    @if($order->user)
                        <p class="mb-0 small mt-2">Tài khoản: {{ $order->user->email }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Trạng thái</div>
                <div class="card-body">
                    @php
                        $oLabel = $orderLabels[$order->status] ?? $order->status;
                        $sLabel = $shippingLabels[$order->shipping_status] ?? $order->shipping_status;
                        $oBadge = \App\Support\OrderStatus::orderBadge($order->status ?? 'pending');
                        $sBadge = \App\Support\OrderStatus::shipBadge($order->shipping_status ?? 'pending');
                    @endphp
                    <p class="mb-1">Đơn: <span class="badge {{ $oBadge }}">{{ $oLabel }}</span>
                        <span class="text-muted small">({{ $order->status }})</span>
                    </p>
                    <p class="mb-1">Vận chuyển: <span class="badge {{ $sBadge }}">{{ $sLabel }}</span>
                        <span class="text-muted small">({{ $order->shipping_status }})</span>
                    </p>
                    @if($order->return_status)
                        <p class="mb-1">Trả hàng:
                            <span class="badge bg-warning text-dark">{{ $returnLabels[$order->return_status] ?? $order->return_status }}</span>
                        </p>
                    @endif
                    <p class="mb-1">GHN: <strong>{{ $order->ghn_order_code ?: '—' }}</strong></p>
                    @if(!$order->ghn_order_code
                        && !in_array($order->status, ['cancelled', 'cancel_requested'], true)
                        && $order->shipping_status !== 'processing'
                        && empty($order->return_status)
                        && ($order->paymentTransactions->contains(fn ($transaction) => $transaction->gateway === 'cod')
                            || $order->paymentTransactions->contains(fn ($transaction) => $transaction->gateway === 'momo' && $transaction->status === 'paid')))
                        <form method="POST" action="{{ route('admin.orders.ghnRetry', $order) }}" class="mb-2"
                              onsubmit="return confirm('Thử tạo lại vận đơn GHN cho đơn #{{ $order->id }}?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">Thử tạo mã vận đơn GHN</button>
                        </form>
                    @endif
                    <p class="mb-0">Phí ship: {{ number_format($order->ghn_total_fee ?? 0, 0, ',', '.') }}đ
                        <span class="text-muted small">(thu khi nhận)</span>
                    </p>
                    <p class="mb-0 small text-muted mt-2">Đặt lúc {{ $order->created_at?->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($order->status === 'cancel_requested')
        <div class="card shadow-sm mb-3 border-danger">
            <div class="card-header fw-semibold text-danger">Yêu cầu hủy đơn đã thanh toán MoMo</div>
            <div class="card-body">
                <p class="mb-1"><strong>Lý do khách hàng:</strong></p>
                <p class="mb-3">{{ $order->cancel_reason }}</p>
                <p class="small text-muted">Gửi lúc {{ $order->cancel_requested_at?->format('d/m/Y H:i') }}</p>
                <form method="POST" action="{{ route('admin.orders.processCancel', $order) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label small mb-0">Ghi chú xử lý</label>
                        <input type="text" name="note" class="form-control form-control-sm" maxlength="500">
                    </div>
                    <div class="col-md-6 d-flex flex-wrap gap-2">
                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Từ chối yêu cầu hủy đơn?')">Từ chối</button>
                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-danger"
                                onclick="return confirm('Duyệt hủy đơn và bắt đầu hoàn tiền MoMo?')">Duyệt hủy + hoàn tiền</button>
                    </div>
                </form>
            </div>
        </div>
    @elseif($order->cancel_reason && $order->cancel_processed_at)
        <div class="alert {{ $order->status === 'cancelled' ? 'alert-success' : 'alert-warning' }}">
            Yêu cầu hủy {{ $order->status === 'cancelled' ? 'đã được duyệt' : 'đã bị từ chối' }}.
            Lý do: {{ $order->cancel_reason }}
            @if($order->cancel_admin_note)
                <br>Ghi chú admin: {{ $order->cancel_admin_note }}
            @endif
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Sản phẩm</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Size / Màu</th>
                        <th class="text-center">SL</th>
                        <th class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? ('SP #'.$item->product_id) }}</td>
                            <td class="small text-muted">{{ trim(($item->size_label ?? '').' / '.($item->color ?? ''), ' /') ?: '—' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @if(($order->discount_amount ?? 0) > 0)
                        <tr>
                            <th colspan="3" class="text-end text-muted fw-normal">Tạm tính hàng hóa</th>
                            <th class="text-end text-muted fw-normal">{{ number_format($order->total_price + $order->discount_amount + $order->coin_discount_amount, 0, ',', '.') }}đ</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end text-success fw-normal">
                                <i class="bi bi-tag-fill me-1"></i>Giảm giá khuyến mãi
                                @if($order->coupon_code)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">{{ $order->coupon_code }}</span>
                                @endif
                            </th>
                            <th class="text-end text-success fw-bold">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</th>
                        </tr>
                    @endif
                    @if($order->coins_used > 0)
                        <tr><th colspan="3" class="text-end text-success">Dùng {{ number_format($order->coins_used) }} xu</th><th class="text-end text-success">-{{ number_format($order->coin_discount_amount) }}đ</th></tr>
                        @endif
                        <tr>
                        <th colspan="3" class="text-end">Tổng tiền hàng</th>
                        <th class="text-end text-danger fs-6">{{ number_format($order->total_price, 0, ',', '.') }}đ</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($order->paymentTransactions->isNotEmpty())
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>Giao dịch</span>
                @php
                    // Chỉ hiện nút hoàn khi đang/đã xử lý trả hàng hoặc GD chờ hoàn
                    $canRefund = in_array($order->return_status, ['requested', 'approved', 'completed'], true)
                        && $order->paymentTransactions->contains(fn ($t) => in_array($t->status, ['paid', 'refund_pending'], true));
                @endphp
                @if($canRefund)
                    <form method="POST" action="{{ route('admin.orders.refund', $order) }}" class="d-flex align-items-center gap-2"
                          onsubmit="return confirm('Xác nhận hoàn tiền hàng đơn #{{ $order->id }}?')">
                        @csrf
                        <label class="small mb-0 d-flex align-items-center gap-1">
                            <input type="checkbox" name="force_manual" value="1"> Hoàn thủ công nếu MoMo lỗi
                        </label>
                        <button class="btn btn-sm btn-warning">Hoàn tiền hàng</button>
                    </form>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table mb-0 table-sm">
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
                        @foreach($order->paymentTransactions as $tx)
                            <tr>
                                <td>{{ strtoupper($tx->gateway) }}</td>
                                <td>{{ number_format($tx->amount, 0, ',', '.') }}đ</td>
                                <td>
                                    <span class="badge {{ $tx->status === 'refunded' ? 'bg-success' : ($tx->status === 'paid' ? 'bg-primary' : 'bg-secondary') }}">
                                        {{ $tx->status }}
                                    </span>
                                </td>
                                <td class="small">{{ $tx->transaction_id ?: '—' }}</td>
                                <td class="small">{{ $tx->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card shadow-sm mb-3">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted">Chưa có giao dịch thanh toán</span>
                <form method="POST" action="{{ route('admin.orders.refund', $order) }}"
                      onsubmit="return confirm('Ghi nhận hoàn thủ công cho đơn #{{ $order->id }}?')">
                    @csrf
                    <button class="btn btn-sm btn-outline-warning">Ghi nhận hoàn thủ công</button>
                </form>
            </div>
        </div>
    @endif

    @if($order->return_status)
        <div class="card shadow-sm mb-3 border-warning">
            <div class="card-header fw-semibold">Yêu cầu trả hàng</div>
            <div class="card-body">
                <p class="mb-1">Trạng thái: <strong>{{ $order->return_status }}</strong></p>
                <p class="mb-1">Lý do KH: {{ $order->return_reason }}</p>
                @if($order->return_admin_note)
                    <p class="mb-1">Ghi chú admin: {{ $order->return_admin_note }}</p>
                @endif
                <p class="mb-3 small text-muted">
                    Gửi: {{ $order->return_requested_at?->format('d/m/Y H:i') }}
                    @if($order->return_processed_at)
                        · Xử lý: {{ $order->return_processed_at->format('d/m/Y H:i') }}
                    @endif
                </p>
                @if(in_array($order->return_status, ['requested', 'approved'], true))
                    <form method="POST" action="{{ route('admin.orders.return', $order) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-5">
                            <label class="form-label small mb-0">Ghi chú</label>
                            <input type="text" name="note" class="form-control form-control-sm" placeholder="Ghi chú">
                        </div>
                        <div class="col-md-7 d-flex flex-wrap gap-2">
                            @if($order->return_status === 'requested')
                                <button name="action" value="approve" class="btn btn-sm btn-success">Duyệt trả hàng</button>
                                <button name="action" value="reject" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Từ chối yêu cầu trả?')">Từ chối</button>
                            @endif
                            @if($order->return_status === 'approved')
                                <button name="action" value="complete" class="btn btn-sm btn-primary"
                                        onclick="return confirm('Xác nhận đã nhận hàng hoàn & hoàn tiền?')">
                                    Hoàn tất + hoàn tiền hàng
                                </button>
                            @endif
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- Chỉ còn nút thao tác rõ ràng — không dropdown --}}
    <div class="d-flex flex-wrap gap-2 align-items-center">
        @php
            $blocked = ['delivering', 'picked', 'storing', 'transporting', 'sorting', 'delivered', 'return', 'returning', 'returned'];
            $hasPaidMomo = $order->paymentTransactions->contains(fn ($transaction) => $transaction->gateway === 'momo' && $transaction->status === 'paid');
            $canCancel = !in_array($order->shipping_status, $blocked, true)
                && !in_array($order->status, ['cancelled', 'cancel_requested'], true)
                && !$hasPaidMomo;
            $canMarkDelivered = !in_array($order->shipping_status, ['delivered', 'return', 'returning', 'returned', 'cancelled'], true)
                && $order->status !== 'cancelled'
                && empty($order->return_status);
            $done = in_array($order->shipping_status, ['delivered', 'returned', 'cancelled'], true)
                || $order->status === 'cancelled'
                || $order->return_status === 'completed';
        @endphp

        @if($canCancel)
            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}"
                  onsubmit="return confirm('Hủy đơn #{{ $order->id }}?')">
                @csrf
                <button class="btn btn-outline-danger">Hủy đơn</button>
            </form>
        @endif

        @if($canMarkDelivered)
            <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}">
                @csrf
                <input type="hidden" name="shipping_status" value="delivering">
                <button class="btn btn-outline-primary">Đang giao</button>
            </form>
            <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}">
                @csrf
                <input type="hidden" name="shipping_status" value="delivered">
                <button class="btn btn-success"
                        onclick="return confirm('Đánh dấu đơn #{{ $order->id }} đã giao?')">
                    Đánh dấu đã giao
                </button>
            </form>
        @endif

        @if(!empty($nextShip) && empty($order->return_status) && !$canMarkDelivered)
            <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}">
                @csrf
                <input type="hidden" name="shipping_status" value="{{ $nextShip }}">
                <button class="btn btn-outline-success">
                    Bước tiếp → {{ $shippingLabels[$nextShip] ?? $nextShip }}
                </button>
            </form>
        @endif

        @if($done)
            <span class="text-muted small">Đơn đã kết thúc — không còn thao tác trạng thái.</span>
        @endif
    </div>
</div>
@endsection
