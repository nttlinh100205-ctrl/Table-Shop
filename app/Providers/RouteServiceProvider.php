<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/user/home';


    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('ai-product-analysis', function (Request $request) {
            return Limit::perMinute(2)->by('ai-product-analysis:'.$request->user()->id)
                ->response(function (Request $request, array $headers) {
                    $seconds = max(1, (int)($headers['Retry-After'] ?? 60));
                    $message = 'Bạn vừa yêu cầu phân tích AI nhiều lần. Vui lòng chờ '.$seconds.' giây rồi thử lại. Bản phân tích đã lưu vẫn được giữ.';
                    if ($request->expectsJson()) return response()->json(['message'=>$message,'retry_after'=>$seconds],429,$headers);
                    $days = in_array((string)$request->input('days'),['7','30','90'],true) ? (int)$request->input('days') : 30;
                    return redirect()->route('admin.ai-demands.index',['days'=>$days])->with('error',$message);
                });
        });
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
