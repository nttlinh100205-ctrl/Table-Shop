<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Dự án dùng Bootstrap 5 → phân trang Bootstrap (không dùng Tailwind mặc định)
        Paginator::useBootstrapFive();

        // Tự động ép HTTPS khi chạy trên môi trường production hoặc URL dạng HTTPS (Render)
        if (config('app.env') === 'production' || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Đăng ký custom mail driver cho Resend và Brevo (gửi qua HTTPS API không bị chặn SMTP trên Render)
        \Illuminate\Support\Facades\Mail::extend('api', function (array $config = []) {
            return new \App\Mail\Transport\ApiTransport($config);
        });
        \Illuminate\Support\Facades\Mail::extend('resend', function (array $config = []) {
            $config['provider'] = 'resend';
            return new \App\Mail\Transport\ApiTransport($config);
        });
        \Illuminate\Support\Facades\Mail::extend('brevo', function (array $config = []) {
            $config['provider'] = 'brevo';
            return new \App\Mail\Transport\ApiTransport($config);
        });
    }
}
