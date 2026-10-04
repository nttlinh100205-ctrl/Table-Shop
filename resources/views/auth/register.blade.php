<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký thành viên — Nội Thất Tinh Hoa</title>
    <meta name="description" content="Tạo tài khoản thành viên Nội Thất Tinh Hoa để nhận ưu đãi và quản lý đơn hàng.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Manrope', -apple-system, sans-serif;
            background-color: #FAF6F0;
            background-image: radial-gradient(#EFE3D3 1px, transparent 1px);
            background-size: 24px 24px;
            color: #3A2E26;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            -webkit-font-smoothing: antialiased;
            margin: 0;
        }

        .nth-auth-card {
            background: #FFFFFF;
            border: 1px solid #E6D8C8;
            border-radius: 2px;
            box-shadow: 0 16px 48px rgba(58, 46, 38, 0.08);
            width: 100%;
            max-width: 460px;
            overflow: hidden;
            position: relative;
        }

        .nth-auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #5A4536;
        }

        .nth-auth-header {
            padding: 2.25rem 2.25rem 1.25rem;
            text-align: center;
        }

        .nth-brand-badge {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #3A2E26;
            margin-bottom: 1.25rem;
        }
        .nth-brand-badge__title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 26px;
            font-weight: 600;
            letter-spacing: 0.03em;
            color: #3A2E26;
            line-height: 1.1;
        }
        .nth-brand-badge__sub {
            font-size: 9px;
            letter-spacing: 0.28em;
            color: #7E7065;
            margin-top: 4px;
            text-transform: uppercase;
        }

        .nth-auth-title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 28px;
            font-weight: 600;
            color: #3A2E26;
            margin-bottom: 0.35rem;
            line-height: 1.2;
        }
        .nth-auth-subtitle {
            font-size: 13px;
            color: #7E7065;
            margin-bottom: 0;
            line-height: 1.5;
        }

        .nth-auth-body {
            padding: 0 2.25rem 2rem;
        }

        .form-label {
            font-size: 12.5px;
            font-weight: 600;
            color: #3A2E26;
            margin-bottom: 0.35rem;
            letter-spacing: 0.02em;
        }

        .form-control {
            background: #FAF6F0;
            border: 1px solid #E6D8C8;
            border-radius: 2px;
            font-size: 13.5px;
            padding: 0.65rem 0.95rem;
            color: #3A2E26;
            transition: all 0.2s ease;
            font-family: 'Manrope', sans-serif;
        }
        .form-control:focus {
            background: #FFFFFF;
            border-color: #5A4536;
            box-shadow: 0 0 0 3px rgba(90, 69, 54, 0.12);
            color: #3A2E26;
        }
        .form-control.is-invalid {
            border-color: #9B3327;
            background-color: #FFF7F6;
        }
        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(155, 51, 39, 0.12);
        }

        .input-group .form-control {
            border-right: none;
        }
        .input-group .btn-show-pass {
            background: #FAF6F0;
            border: 1px solid #E6D8C8;
            border-left: none;
            border-radius: 0 2px 2px 0;
            color: #7E7065;
            padding: 0 0.85rem;
            transition: all 0.2s;
        }
        .input-group .form-control:focus + .btn-show-pass {
            border-color: #5A4536;
            background: #FFFFFF;
        }
        .input-group .btn-show-pass:hover {
            color: #3A2E26;
        }

        .password-hint {
            font-size: 11.5px;
            color: #7E7065;
            margin-top: 5px;
        }

        .btn-nth-auth {
            width: 100%;
            padding: 0.75rem;
            background: #5A4536;
            border: 1px solid #5A4536;
            border-radius: 2px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #FAF6F0;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .btn-nth-auth:hover {
            background: #3F2F24;
            border-color: #3F2F24;
            color: #FAF6F0;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(90, 69, 54, 0.22);
        }

        .nth-auth-footer {
            text-align: center;
            padding: 1.15rem 2.25rem 1.5rem;
            border-top: 1px solid #EFE3D3;
            background: #FAF6F0;
            font-size: 13px;
            color: #7E7065;
        }
        .nth-auth-footer a {
            color: #5A4536;
            font-weight: 700;
            text-decoration: none;
            margin-left: 4px;
            transition: color 0.15s;
        }
        .nth-auth-footer a:hover {
            color: #3F2F24;
            text-decoration: underline;
        }

        .nth-back-home {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 1.25rem;
            color: #7E7065;
            font-size: 12.5px;
            text-decoration: none;
            transition: color 0.2s;
        }
        .nth-back-home:hover {
            color: #5A4536;
        }

        .alert {
            border-radius: 2px;
            font-size: 13px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            border: 1px solid transparent;
        }
        .alert-danger {
            background: #FDF4F4;
            color: #9B2C2C;
            border-color: #F3C8C8;
            border-left: 4px solid #9B3327;
        }
        .invalid-feedback {
            font-size: 12px;
            color: #9B3327;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="d-flex flex-column align-items-center w-100">
        <div class="nth-auth-card">
            <div class="nth-auth-header">
                {{-- Logo thương hiệu Nội Thất Tinh Hoa --}}
                <a href="{{ route('user.home') }}" class="nth-brand-badge">
                    <span class="nth-brand-badge__title">Nội Thất Tinh Hoa</span>
                    <span class="nth-brand-badge__sub">GỖ ĐẸP CHO NHÀ</span>
                </a>
                <h1 class="nth-auth-title">Đăng Ký Thành Viên</h1>
                <p class="nth-auth-subtitle">Cùng kiến tạo không gian sống gỗ tự nhiên độc bản</p>
            </div>

            <div class="nth-auth-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-1"></i>
                            <ul class="mb-0 ps-0" style="list-style:none;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Họ và tên</label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               required autofocus
                               placeholder="Nguyễn Văn A">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Địa chỉ Email</label>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               required
                               placeholder="nhap-email@vidu.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <div class="input-group">
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required
                                   placeholder="Tối thiểu 6 ký tự">
                            <button type="button" class="btn-show-pass" id="togglePassword" tabindex="-1"
                                    aria-label="Hiện/ẩn mật khẩu">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="password-hint"><i class="bi bi-info-circle me-1"></i>Tối thiểu 6 ký tự</div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                        <div class="input-group">
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   class="form-control"
                                   required
                                   placeholder="Nhập lại mật khẩu">
                            <button type="button" class="btn-show-pass" id="toggleConfirm" tabindex="-1"
                                    aria-label="Hiện/ẩn xác nhận mật khẩu">
                                <i class="bi bi-eye" id="toggleIconConfirm"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-nth-auth">Tạo Tài Khoản</button>
                </form>
            </div>

            <div class="nth-auth-footer">
                Đã có tài khoản?
                <a href="{{ route('login') }}">Đăng nhập ngay</a>
            </div>
        </div>

        {{-- Lối quay về trang chủ --}}
        <a href="{{ route('user.home') }}" class="nth-back-home">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            <span>Quay lại trang chủ Nội Thất Tinh Hoa</span>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setupToggle(btnId, inputId, iconId) {
            const btn   = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            btn?.addEventListener('click', function () {
                const isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }
        setupToggle('togglePassword', 'password', 'toggleIcon');
        setupToggle('toggleConfirm', 'password_confirmation', 'toggleIconConfirm');
    </script>
</body>
</html>
