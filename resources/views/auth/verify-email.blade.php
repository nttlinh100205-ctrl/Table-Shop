<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: transparent;
            border-bottom: none;
            padding-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="fw-bold mb-1">Xác thực Email</h3>
                        <p class="text-muted small mb-0">Vui lòng xác thực email của bạn</p>
                    </div>
                    <div class="card-body px-4 pb-4 text-center">
                        @if (session('success'))
                            <div class="alert alert-success py-2">{{ session('success') }}</div>
                        @endif
                        @if (session('message'))
                            <div class="alert alert-success py-2">{{ session('message') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger py-2">{{ session('error') }}</div>
                        @endif

                        <p class="mb-4">
                            Một email chứa link xác thực đã được gửi đến địa chỉ email của bạn.
                            Vui lòng kiểm tra hộp thư (kể cả thư mục Spam) và nhấn vào link để hoàn tất đăng ký.
                        </p>

                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
                                Gửi lại email xác thực
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100 py-2">
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
