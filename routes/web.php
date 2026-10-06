<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ColorController as AdminColorController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\GHNController;
use App\Http\Controllers\User\MomoController;
use App\Http\Controllers\User\ChatController as UserChatController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\User\PointController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Health check cho Docker & Render Web Service
Route::get('/up', function () {
    return response('OK', 200)->header('Content-Type', 'text/plain');
});

// Trang chủ công khai cho mọi người
Route::get('/', [HomeController::class, 'index'])->name('user.home');
Route::get('/products/{id}', [HomeController::class, 'show'])->name('products.show');


/*
|--------------------------------------------------------------------------
| Auth routes (guest only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Email Verification routes
|--------------------------------------------------------------------------
*/
// Hiển thị thông báo xác thực email
Route::get('/email/verify', function () {
    if (auth()->user()->hasVerifiedEmail()) return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'user.home');
    return response()->view('auth.verify-email')->header('Cache-Control', 'no-store, private');
})->middleware('auth')->name('verification.notice');

// Xử lý link xác nhận (từ email)
Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:20,1'])->name('verification.verify');
Route::get('/email/verification-status', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'status'])
    ->middleware(['auth', 'throttle:60,1'])->name('verification.status');

// Gửi lại email xác nhận
Route::post('/email/verification-notification', function (Request $request) {
    $user = $request->user();
    // Chỉ gửi khi chưa xác thực — không gửi lại nếu đã verify
    if ($user->hasVerifiedEmail()) {
        return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'user.home')
            ->with('success', 'Email đã được xác thực.');
    }
    try {
        $user->sendEmailVerificationNotification();
        return back()->with('message', 'Email xác thực đã được xếp hàng gửi. Vui lòng kiểm tra hộp thư và mục Spam.');
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Verification enqueue failed', ['exception' => get_class($e)]);
        return back()->with('error', 'Chưa gửi được email xác thực. Vui lòng thử lại sau ít phút.');
    }
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| Admin routes — chỉ admin (middleware: auth + admin)
| Thư mục xử lý: App\Http\Controllers\Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'admin'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('categories', AdminCategoryController::class);
        Route::resource('products', AdminProductController::class);
        Route::resource('colors', AdminColorController::class)->only(['index', 'edit', 'update']);

        // Đơn hàng
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::post('/orders/bulk-status', [AdminOrderController::class, 'bulkUpdateStatus'])->name('orders.bulkUpdateStatus');
        Route::post('/orders/{order}/ghn-retry', [AdminOrderController::class, 'retryGhnOrder'])->name('orders.ghnRetry');
        Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/process-cancel', [AdminOrderController::class, 'processCancel'])->name('orders.processCancel');
        Route::post('/orders/{order}/return', [AdminOrderController::class, 'processReturn'])->name('orders.return');
        Route::post('/orders/{order}/refund', [AdminOrderController::class, 'refund'])->name('orders.refund');

        // Khuyến mãi & Voucher
        Route::resource('promotions', AdminPromotionController::class);
        Route::post('/promotions/{promotion}/toggle', [AdminPromotionController::class, 'toggleStatus'])->name('promotions.toggle');

        // Người dùng
        Route::resource('users', AdminUserController::class);
        Route::resource('prizes', \App\Http\Controllers\Admin\PrizeController::class)->except(['show','destroy']);
        Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/ai-demands', [\App\Http\Controllers\Admin\AiDemandController::class, 'index'])->name('ai-demands.index');
        Route::post('/ai-demands/import', [\App\Http\Controllers\Admin\AiDemandController::class, 'import'])->middleware('throttle:2,1')->name('ai-demands.import');
        Route::post('/ai-demands/analyze', [\App\Http\Controllers\Admin\AiDemandController::class, 'analyze'])->middleware('throttle:2,1')->name('ai-demands.analyze');
        Route::put('/ai-demands/{key}', [\App\Http\Controllers\Admin\AiDemandController::class, 'update'])->where('key', '[a-f0-9]{64}')->name('ai-demands.update');
        Route::put('/reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'update'])->name('reviews.update');

        // Báo cáo
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/charts', [AdminReportController::class, 'charts'])->name('reports.charts');

        // Tài chính & Giao dịch thanh toán
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('/finance/transactions', [FinanceController::class, 'transactions'])->name('finance.transactions');
        Route::patch('/finance/{order}/status', [FinanceController::class, 'updateStatus'])->name('finance.update-status');

        // Livechat admin
        Route::get('/chat/users', [AdminChatController::class, 'getUsers'])->name('chat.users');
        Route::get('/chat/messages/{userId}', [AdminChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('/chat/send', [AdminChatController::class, 'send'])->name('chat.send');
    });

/*
|--------------------------------------------------------------------------
| User routes — Công khai (Chi tiết sản phẩm, Giỏ hàng)
| Cho phép cả khách vãng lai và người dùng đã đăng nhập
|--------------------------------------------------------------------------
*/
Route::prefix('user')->name('user.')->group(function () {
    Route::get('/home', function () {
        return redirect()->route('user.home');
    });
    Route::get('/products/{id}', [HomeController::class, 'show'])->name('products.show');

    // Giỏ hàng (dùng session)
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    // Áp dụng / Hủy voucher giảm giá & lấy danh sách khuyến mãi
    Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::post('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    Route::get('/promotions', [CartController::class, 'promotions'])->name('promotions.index');
});

/*
|--------------------------------------------------------------------------
| User routes — Bắt buộc đăng nhập (Thanh toán, Đơn hàng, Livechat)
|--------------------------------------------------------------------------
*/
Route::prefix('user')
    ->name('user.')
    ->middleware(['auth', 'verified', 'user'])
    ->group(function () {
        // Thanh toán & đơn hàng
        Route::get('/payment', [OrderController::class, 'index'])->name('payment.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders', [OrderController::class, 'orderHistory'])->name('orders.index');
        // MoMo: khai báo trước /orders/{order}
        Route::get('/orders/{order}/start-momo', [MomoController::class, 'start'])->name('orders.momo.start');
        Route::get('/orders/{order}/pay/momo', [MomoController::class, 'payAgain'])->name('orders.momo.pay');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/switch-cod', [OrderController::class, 'switchToCod'])->name('orders.switchCod');
        Route::post('/orders/{order}/return', [OrderController::class, 'requestReturn'])->name('orders.return');

        // GHN locations + phí ship
        Route::prefix('locations')->name('locations.')->group(function () {
            Route::get('/provinces', [GHNController::class, 'getProvinces'])->name('provinces');
            Route::get('/districts/{provinceId}', [GHNController::class, 'getDistricts'])->name('districts');
            Route::get('/wards/{districtId}', [GHNController::class, 'getWards'])->name('wards');
            Route::post('/calculate-fee', [GHNController::class, 'getShippingFee'])->name('fee');
        });

        // Đánh giá đơn hàng (Review)
        Route::post('/orders/{order}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

        // Điểm thưởng & Hạng thành viên
        Route::get('/points', [PointController::class, 'index'])->name('points.index');
        Route::post('/points/redeem', [PointController::class, 'redeem'])->name('points.redeem');
        Route::post('/profile/avatar', [PointController::class, 'updateAvatar'])->name('profile.avatar');

        // Livechat user
        Route::post('/chat/send', [UserChatController::class, 'send'])->name('chat.send');
        Route::get('/chat/messages', [UserChatController::class, 'getMessages'])->name('chat.messages');
    });

/*
|--------------------------------------------------------------------------
| Third-party callbacks (MoMo IPN / redirect) — không auth, không CSRF
|--------------------------------------------------------------------------
*/
Route::post('/payment/momo/ipn', [MomoController::class, 'ipn'])->name('payment.momo.ipn');
Route::get('/payment/momo/callback', [MomoController::class, 'callback'])
    ->middleware(['auth', 'verified'])
    ->name('user.payment.momo.callback');

Route::get('/ai-chat/greeting', [\App\Http\Controllers\User\AiChatController::class, 'greeting'])->middleware('throttle:30,1')->name('ai.greeting');
Route::post('/ai-chat', [\App\Http\Controllers\User\AiChatController::class, 'send'])->middleware('throttle:10,1')->name('ai.send');
Route::get('/chat/session', function (\Illuminate\Http\Request $request) {
    return response()->json(['user_id'=>$request->user()->id, 'csrf_token'=>csrf_token()])->header('Cache-Control', 'no-store, private');
})->middleware('auth')->name('chat.session');
Route::middleware(['auth', 'verified', 'user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/spin', [\App\Http\Controllers\User\SpinController::class, 'index'])->name('spin.index');
    Route::post('/spin', [\App\Http\Controllers\User\SpinController::class, 'store'])->middleware('throttle:10,1')->name('spin.store');
    Route::get('/profile', fn () => redirect()->route('user.points.index'))->name('profile');
    Route::get('/check-in', [\App\Http\Controllers\User\CheckInController::class, 'index'])->name('check-in.index');
    Route::post('/check-in', [\App\Http\Controllers\User\CheckInController::class, 'store'])->middleware('throttle:10,1')->name('check-in.store');
});
