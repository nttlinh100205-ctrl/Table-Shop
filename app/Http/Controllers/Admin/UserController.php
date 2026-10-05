<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderByDesc('id')->paginate(30);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,user',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $avatarUrl = null;
        if ($request->hasFile('avatar')) {
            $avatarUrl = \App\Services\CloudinaryService::uploadOrStore($request->file('avatar'), 'avatars');
        }

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'avatar'   => $avatarUrl,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Thêm người dùng thành công.');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email,' . $user->id,
            'role'   => 'required|in:admin,user',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = \App\Services\CloudinaryService::uploadOrStore($request->file('avatar'), 'avatars');
        } elseif ($request->boolean('delete_avatar')) {
            $data['avatar'] = null;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật người dùng thành công.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Xóa người dùng thành công.');
    }
}
