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
    }
}
