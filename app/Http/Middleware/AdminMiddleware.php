<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Chỉ cho phép admin truy cập khu vực quản trị.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        if (!Auth::user()->isAdmin()) {
            return redirect()->route('user.home')
                ->with('error', 'Bạn không có quyền truy cập khu vực quản trị.');
        }

        return $next($request);
    }
}
