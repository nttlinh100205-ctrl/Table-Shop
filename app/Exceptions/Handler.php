<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof \Illuminate\Session\TokenMismatchException
            && !$request->expectsJson()
            && $request->is('register', 'email/verification-notification')) {
            $destination = $request->user() ? 'verification.notice' : ($request->is('register') ? 'register' : 'login');
            return redirect()->route($destination)
                ->withInput($request->only('name', 'email'))
                ->with('error', 'Phiên làm việc đã hết hạn. Trang đã được làm mới; vui lòng nhập lại mật khẩu hoặc gửi lại email xác thực.');
        }
        return parent::render($request, $e);
    }
}
