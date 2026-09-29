<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Hiển thị form đăng ký.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Xử lý đăng ký tài khoản mới (mặc định role = user).
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => ['required', 'confirmed', Password::min(6)],
        ], [
            'name.required'         => 'Vui lòng nhập họ tên.',
            'email.required'        => 'Vui lòng nhập email.',
            'email.email'           => 'Email không hợp lệ.',
            'email.unique'          => 'Email này đã được sử dụng.',
            'password.required'     => 'Vui lòng nhập mật khẩu.',
            'password.confirmed'    => 'Xác nhận mật khẩu không khớp.',
            'password.min'          => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'user', // mặc định là user thường
        ]);

        Auth::login($user);

        return redirect()->route('user.home')
            ->with('success', 'Đăng ký thành công! Chào mừng bạn.');
    }
}
