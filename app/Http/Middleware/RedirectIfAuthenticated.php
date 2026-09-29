<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Đã đăng nhập thì không vào login/register nữa.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if ($user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                }

                return redirect()->route('user.home');
            }
        }

        return $next($request);
    }
}
