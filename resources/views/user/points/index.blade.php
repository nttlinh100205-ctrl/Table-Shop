@extends('layouts.app')

@section('title', 'Điểm thưởng & Hạng thành viên')

@section('content')
<div class="container py-3">
    <div class="card p-3">
        <h2 class="h5">Giới thiệu bạn bè</h2>
        <p>Xu điểm danh: <strong>{{ number_format(auth()->user()->coin_balance) }}</strong> xu · <a href="{{ route('user.check-in.index') }}">Điểm danh nhận xu mỗi ngày</a></p>
        <p>Mã của bạn: <strong>{{ auth()->user()->referral_code }}</strong> · Điểm hiện có: {{ number_format(auth()->user()->points_balance) }}</p>
        <a href="{{ route('user.spin.index') }}">Vòng quay may mắn · {{ auth()->user()->spin_tickets }} lượt</a>
        @foreach(auth()->user()->notifications()->latest()->limit(5)->get() as $notification)
            <p class="mb-1 mt-2">{{ $notification->data['message'] ?? 'Thông báo điểm thưởng' }}</p>
        @endforeach
    </div>
</div>
<div class="py-4" style="background-color: #FAF6F0; min-height: 80vh;">
    <div class="container" style="max-width: 1140px;">

        {{-- Thông báo Flash message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
                <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
                <span class="align-middle fw-medium">{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i>
                <span class="align-middle fw-medium">{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- BREADCRUMB --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('user.home') }}" class="text-decoration-none" style="color: #7E7065;">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="color: #3F2F24; font-weight: 600;">Điểm thưởng &amp; Hạng thành viên</li>
            </ol>
        </nav>

        {{-- ===== THẺ HẠNG THÀNH VIÊN VÀNG/BẠC/ĐỒNG/KIM CƯƠNG ===== --}}
        @php
            $tierKey = $tier['key'] ?? 'bronze';
            $tierGradients = [
                'bronze'  => 'linear-gradient(135deg, #4A3326 0%, #7A533D 60%, #9C6C50 100%)',
                'silver'  => 'linear-gradient(135deg, #334155 0%, #64748B 60%, #94A3B8 100%)',
                'gold'    => 'linear-gradient(135deg, #78350F 0%, #B45309 50%, #D97706 100%)',
                'diamond' => 'linear-gradient(135deg, #0C4A6E 0%, #0369A1 50%, #0284C7 100%)',
            ];
            $cardBg = $tierGradients[$tierKey] ?? $tierGradients['bronze'];
        @endphp

        <div class="card border-0 shadow-sm text-white mb-4 position-relative overflow-hidden" 
             style="background: {{ $cardBg }}; border-radius: 16px;">
            <div class="position-absolute end-0 top-0 opacity-10 p-3 pe-4 pointer-events-none" style="font-size: 14rem; line-height: 1; user-select: none;">
                <i class="bi bi-award"></i>
            </div>

            <div class="card-body p-4 p-md-5 position-relative">
                <div class="row align-items-center g-4">
                    {{-- Avatar & Thông tin User --}}
                    <div class="col-lg-7">
                        <div class="d-flex align-items-center gap-3 gap-md-4">
                            {{-- Avatar tròn có nút cập nhật ảnh Cloudinary --}}
                            <div class="position-relative">
                                @if(!empty($user->avatar))
                                    <img src="{{ $user->avatar }}" 
                                         alt="{{ $user->name }}" 
                                         class="rounded-circle border border-3 border-white shadow"
                                         style="width: 84px; height: 84px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle border border-3 border-white shadow d-flex align-items-center justify-content-center text-white fw-bold fs-2"
                                         style="width: 84px; height: 84px; background: rgba(255,255,255,0.2);">
                                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <button type="button" 
                                        class="btn btn-sm btn-light position-absolute bottom-0 end-0 rounded-circle p-1 shadow-sm"
                                        style="width: 28px; height: 28px; line-height: 1;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#avatarModal" 
                                        title="Thay đổi ảnh đại diện">
                                    <i class="bi bi-camera" style="font-size: 0.85rem;"></i>
                                </button>
                            </div>

                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <h4 class="fw-bold mb-0 text-white font-serif" style="font-size: 1.5rem;">{{ $user->name }}</h4>
                                    <span class="badge bg-white text-dark fw-bold px-2 py-1 shadow-sm" style="font-size: 0.78rem; border-radius: 6px;">
                                        <i class="bi bi-shield-fill-check text-warning me-1"></i>{{ $tier['name'] }}
                                    </span>
                                </div>
                                <div class="small opacity-75 mb-2">{{ $user->email }}</div>
                                <div class="d-inline-flex align-items-center gap-2 px-2 py-1 rounded" style="background: rgba(0,0,0,0.2); font-size: 0.8rem;">
                                    <span>Tích lũy trọn đời: <strong>{{ number_format($lifetimePoints, 0, ',', '.') }}</strong> điểm</span>
                                </div>
                            </div>
                        </div>

                        {{-- Thanh tiến trình lên hạng tiếp theo --}}
                        <div class="mt-4 pt-2" style="max-width: 520px;">
                            <div class="d-flex justify-content-between small mb-1 opacity-90">
                                <span>Tiến trình nâng hạng</span>
                                @if(!empty($tier['next_tier']))
                                    <span>Lên <strong>{{ $tier['next_tier']['name'] }}</strong>: còn {{ number_format($tier['points_needed'], 0, ',', '.') }} điểm</span>
                                @else
                                    <span class="text-warning fw-bold"><i class="bi bi-gem me-1"></i>Hạng cao nhất</span>
                                @endif
                            </div>
                            <div class="progress" style="height: 8px; background: rgba(255,255,255,0.25); border-radius: 10px;">
                                <div class="progress-bar bg-warning" role="progressbar" 
                                     style="width: {{ $tier['progress_percent'] }}%; border-radius: 10px;" 
                                     aria-valuenow="{{ $tier['progress_percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Khối Số dư điểm khả dụng & Cảnh báo hạn điểm --}}
                    <div class="col-lg-5 text-lg-end">
                        <div class="p-3 rounded-3 d-inline-block text-start text-lg-end" style="background: rgba(0,0,0,0.22); min-width: 260px;">
                            <div class="small text-uppercase opacity-75 fw-semibold mb-1" style="letter-spacing: 0.05em;">Số dư điểm khả dụng</div>
                            <div class="display-6 fw-bold text-warning font-serif mb-1" style="line-height: 1;">
                                {{ number_format($pointsBalance, 0, ',', '.') }}
                                <span class="fs-6 text-white fw-normal font-sans">điểm</span>
                            </div>
                            
                            @if($expiringPoints > 0)
                                <div class="mt-2 pt-2 border-top border-white border-opacity-10 small text-warning-emphasis bg-warning bg-opacity-25 px-2 py-1 rounded">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Có <strong>{{ number_format($expiringPoints, 0, ',', '.') }}</strong> điểm sẽ hết hạn trong 30 ngày tới.
                                </div>
                            @else
                                <div class="small opacity-75 mt-1">
                                    <i class="bi bi-shield-check me-1"></i>Hạn dùng 1 năm từ ngày nhận
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== DANH SÁCH VOUCHER CỦA BẠN (NẾU CÓ) ===== --}}
        @if($myVouchers->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <h5 class="card-title fw-bold mb-0 text-dark font-serif" style="font-size: 1.15rem;">
                        <i class="bi bi-ticket-perforated-fill text-danger me-2"></i>Voucher của bạn đã đổi ({{ $myVouchers->count() }})
                    </h5>
                    <small class="text-muted">Áp dụng ngay tại bước thanh toán</small>
                </div>
                <div class="card-body p-4 pt-1">
                    <div class="row g-3">
                        @foreach($myVouchers as $v)
                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 bg-white rounded-3 border position-relative d-flex justify-content-between align-items-center"
                                     style="border-color: #E6D8C8 !important; border-left: 4px solid #C29D62 !important;">
                                    <div>
                                        <div class="fw-bold text-dark mb-1">{{ $v->name }}</div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <code class="px-2 py-1 bg-light text-primary fw-bold rounded border" style="font-size: 0.9rem;">{{ $v->code }}</code>
                                            <button type="button" class="btn btn-sm btn-link p-0 text-muted copy-voucher-btn" data-code="{{ $v->code }}" title="Sao chép mã">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block">
                                            Đơn từ {{ number_format($v->min_order_amount, 0, ',', '.') }}đ · HSD: {{ $v->end_date ? $v->end_date->format('d/m/Y') : 'Vô thời hạn' }}
                                        </small>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Khả dụng</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== DANH MỤC GÓI ĐỔI ĐIỂM SANG VOUCHER ===== --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title fw-bold mb-1 text-dark font-serif" style="font-size: 1.25rem;">
                            <i class="bi bi-gift-fill text-warning me-2"></i>Đổi điểm lấy Voucher ưu đãi
                        </h5>
                        <div class="text-muted small">Tích lũy 1 điểm / 10đ tiền hàng. Hạng càng cao đổi được gói giá trị càng lớn với tỷ lệ điểm ưu đãi hơn!</div>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2">
                        Số dư: <strong class="text-warning-emphasis">{{ number_format($pointsBalance, 0, ',', '.') }}</strong> điểm
                    </span>
                </div>
            </div>

            <div class="card-body p-4 pt-2">
                <div class="row g-3">
                    @foreach($packages as $pkgKey => $pkg)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border transition-all" 
                                 style="border-radius: 12px; border-color: {{ $pkg['can_redeem'] ? '#C29D62' : '#E2E8F0' }} !important; background: {{ $pkg['can_redeem'] ? '#FFFFFF' : '#FAFAFA' }};">
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge {{ $pkg['can_redeem'] ? 'bg-warning text-dark' : 'bg-secondary' }}" style="font-size: 0.72rem;">
                                                {{ $pkg['badge'] ?? 'Ưu đãi' }}
                                            </span>
                                            <small class="text-muted">HSD {{ $pkg['valid_days'] }} ngày</small>
                                        </div>

                                        <h6 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">{{ $pkg['name'] }}</h6>
                                        <div class="text-danger fw-bold fs-5 mb-2">
                                            -{{ number_format($pkg['discount_value'], 0, ',', '.') }}đ
                                        </div>

                                        <ul class="list-unstyled small text-muted mb-3" style="line-height: 1.8;">
                                            <li><i class="bi bi-coin text-warning me-1"></i>Điểm cần: <strong class="text-dark">{{ number_format($pkg['points_required'], 0, ',', '.') }} điểm</strong></li>
                                            <li><i class="bi bi-bag-check me-1"></i>Đơn tối thiểu: {{ number_format($pkg['min_order'], 0, ',', '.') }}đ</li>
                                            <li><i class="bi bi-award me-1"></i>Hạng tối thiểu: {{ config("membership.tiers.{$pkg['min_tier']}.name", $pkg['min_tier']) }}</li>
                                        </ul>
                                    </div>

                                    <div>
                                        @if($pkg['can_redeem'])
                                            <form action="{{ route('user.points.redeem') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn đổi {{ number_format($pkg['points_required'], 0, ',', '.') }} điểm để lấy gói {{ $pkg['name'] }}?');">
                                                @csrf
                                                <input type="hidden" name="package_id" value="{{ $pkgKey }}">
                                                <button type="submit" class="btn w-100 fw-bold text-white shadow-sm" style="background: #5A4536; border-radius: 8px;">
                                                    <i class="bi bi-arrow-repeat me-1"></i>Đổi ngay ({{ number_format($pkg['points_required'], 0, ',', '.') }} đ)
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn w-100 btn-light border text-muted fw-semibold" disabled style="border-radius: 8px;">
                                                <i class="bi bi-lock-fill me-1"></i>{{ $pkg['lock_reason'] ?: 'Chưa đủ điều kiện' }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== LỊCH SỬ BIẾN ĐỘNG ĐIỂM THƯỞNG ===== --}}
        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0 text-dark font-serif" style="font-size: 1.2rem;">
                    <i class="bi bi-journal-text text-primary me-2"></i>Lịch sử biến động điểm
                </h5>
                <span class="text-muted small">Lô điểm cũ được sử dụng trước (FIFO)</span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Thời gian</th>
                                <th>Loại giao dịch</th>
                                <th>Biến động</th>
                                <th>Mô tả chi tiết</th>
                                <th>Hạn sử dụng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $tx)
                                @php
                                    $isPlus = $tx->points > 0;
                                    $typeBadge = match($tx->type) {
                                        'earn'   => ['bg-success-subtle text-success border border-success-subtle', 'Tích điểm đơn hàng'],
                                        'redeem' => ['bg-warning-subtle text-warning-emphasis border border-warning-subtle', 'Đổi Voucher'],
                                        'refund' => ['bg-danger-subtle text-danger border border-danger-subtle', 'Thu hồi điểm'],
                                        'expire' => ['bg-secondary-subtle text-secondary border border-secondary-subtle', 'Hết hạn sử dụng'],
                                        default  => ['bg-light text-dark border', $tx->type],
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-4 text-muted small">
                                        {{ $tx->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $typeBadge[0] }} px-2 py-1" style="font-size: 0.78rem;">
                                            {{ $typeBadge[1] }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="{{ $isPlus ? 'text-success' : 'text-danger' }}" style="font-size: 1rem;">
                                            {{ $isPlus ? '+' : '' }}{{ number_format($tx->points, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>
                                        <div>{{ $tx->description }}</div>
                                        @if($tx->order_id)
                                            <a href="{{ route('user.orders.show', $tx->order_id) }}" class="small text-decoration-none" style="color: #5A4536;">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Xem đơn #{{ $tx->order_id }}
                                            </a>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        @if($tx->type === 'earn')
                                            @if($tx->is_expired)
                                                <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Đã hết hạn</span>
                                            @elseif($tx->expires_at)
                                                <span>{{ $tx->expires_at->format('d/m/Y') }}</span>
                                            @else
                                                —
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        Bạn chưa có giao dịch tích lũy hoặc đổi điểm nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                    <div class="p-3 border-top d-flex justify-content-end">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- ===== MODAL THAY ĐỔI AVATAR CLOUDINARY ===== --}}
<div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: 1px solid #E6D8C8;">
            <form action="{{ route('user.profile.avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header text-white" style="background: #5A4536;">
                    <h6 class="modal-title fw-bold mb-0" id="avatarModalLabel">
                        <i class="bi bi-camera-fill me-1"></i>Ảnh đại diện
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 text-center">
                    <div class="mb-3">
                        @if(!empty($user->avatar))
                            <img src="{{ $user->avatar }}" id="avatarModalPreview" class="rounded-circle border shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <div id="avatarModalPlaceholder" class="rounded-circle border mx-auto d-flex align-items-center justify-content-center bg-light text-muted fs-1 fw-bold shadow-sm" style="width: 100px; height: 100px;">
                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                            </div>
                            <img src="" id="avatarModalPreview" class="rounded-circle border shadow-sm d-none mx-auto" style="width: 100px; height: 100px; object-fit: cover;">
                        @endif
                    </div>
                    <div class="mb-2">
                        <label for="avatar_file" class="form-label small fw-bold text-muted d-block text-start">Chọn ảnh mới (JPG, PNG, WEBP &le; 5MB):</label>
                        <input type="file" name="avatar" id="avatar_file" class="form-control form-control-sm" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2 justify-content-end">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-sm text-white fw-bold" style="background: #5A4536;">Tải lên</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sao chép mã voucher
        document.querySelectorAll('.copy-voucher-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const code = this.getAttribute('data-code');
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(code).then(() => {
                        alert('Đã sao chép mã voucher: ' + code);
                    });
                }
            });
        });

        // Xem trước avatar tải lên
        const avatarInput = document.getElementById('avatar_file');
        const avatarPreview = document.getElementById('avatarModalPreview');
        const avatarPlaceholder = document.getElementById('avatarModalPlaceholder');

        if (avatarInput) {
            avatarInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (avatarPreview) {
                            avatarPreview.src = e.target.result;
                            avatarPreview.classList.remove('d-none');
                        }
                        if (avatarPlaceholder) {
                            avatarPlaceholder.classList.add('d-none');
                        }
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
</script>
@endsection
