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
        return response()->view('auth.register')->header('Cache-Control', 'no-store, private');
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
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'role'              => 'user', // mặc định là user thường
            'email_verified_at' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        try {
            $user->sendEmailVerificationNotification();
            return redirect()->route('verification.notice')
                ->with('success', 'Đăng ký thành công! Email xác thực đang được gửi tới ' . $user->email . '. Bạn có thể kiểm tra hộp thư và mục Spam.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Could not enqueue verification email', ['user_id' => $user->id, 'exception' => get_class($e)]);
            return redirect()->route('verification.notice')->with('error', 'Tài khoản đã được tạo nhưng chưa xếp hàng gửi được email. Vui lòng thử nút gửi lại sau ít phút.');
        }
    }
}
