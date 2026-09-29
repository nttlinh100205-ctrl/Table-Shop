<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký — Store</title>
    <meta name="description" content="Tạo tài khoản mới để bắt đầu mua sắm.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1d4ed8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            -webkit-font-smoothing: antialiased;
        }
        .auth-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3), 0 4px 16px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .auth-card-header {
            padding: 2rem 2rem 1.25rem;
            text-align: center;
        }
        .auth-logo {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.4rem;
            color: #fff;
            box-shadow: 0 4px 12px rgba(37,99,235,0.35);
        }
        .auth-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }
        .auth-subtitle {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0;
        }
        .auth-card-body {
            padding: 0 2rem 2rem;
        }
        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.4rem;
        }
        .form-control {
            border-color: #e2e8f0;
            border-radius: 9px;
            font-size: 0.9rem;
            padding: 0.6rem 0.875rem;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        }
        .form-control.is-invalid { border-color: #ef4444; }
        .form-control.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,0.15); }
        .input-group .form-control { border-right: none; }
        .input-group .btn-show-pass {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-left: none;
            border-radius: 0 9px 9px 0;
            color: #94a3b8;
            padding: 0 0.75rem;
            transition: color 0.15s;
        }
        .input-group .btn-show-pass:hover { color: #334155; }
        .input-group .btn-show-pass:focus { outline: none; box-shadow: none; }
        .btn-auth {
            width: 100%;
            padding: 0.65rem;
            border-radius: 9px;
            font-size: 0.9rem;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            border: none;
            color: #fff;
            transition: opacity 0.15s, transform 0.15s;
            letter-spacing: 0.01em;
        }
        .btn-auth:hover { opacity: 0.92; transform: translateY(-1px); color: #fff; }
        .btn-auth:active { transform: translateY(0); }
        .auth-footer {
            text-align: center;
            padding: 1rem 2rem 1.5rem;
            border-top: 1px solid #f1f5f9;
            font-size: 0.83rem;
            color: #64748b;
        }
        .auth-footer a {
            color: #2563eb;
            font-weight: 700;
            text-decoration: none;
        }
        .auth-footer a:hover { text-decoration: underline; }
        .alert {
            border-radius: 9px;
            font-size: 0.85rem;
            padding: 0.7rem 0.875rem;
            margin-bottom: 1rem;
            border: none;
        }
        .alert-danger { background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }
        .invalid-feedback { font-size: 0.78rem; }

        /* Password strength indicator */
        .password-hint {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-logo">
                <i class="bi bi-shop"></i>
            </div>
            <h1 class="auth-title">Tạo tài khoản</h1>
            <p class="auth-subtitle">Đăng ký để bắt đầu mua sắm ngay!</p>
        </div>

        <div class="auth-card-body">
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
                    <label for="email" class="form-label">Email</label>
                    <input type="email"
                           name="email"
                           id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           required
                           placeholder="you@example.com">
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

                <button type="submit" class="btn btn-auth">
                    <i class="bi bi-person-plus me-1"></i>Đăng ký ngay
                </button>
            </form>
        </div>

        <div class="auth-footer">
            Đã có tài khoản?
            <a href="{{ route('login') }}">Đăng nhập</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePass(btnId, inputId, iconId) {
            const btn   = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            btn?.addEventListener('click', function () {
                const isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }
        togglePass('togglePassword', 'password', 'toggleIcon');
        togglePass('toggleConfirm', 'password_confirmation', 'toggleIconConfirm');
    </script>
</body>
</html>
