<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q'=>'nullable|string|max:100']);
        $users = User::when($request->filled('q'), fn($q)=>$q->where(fn($q)=>$q->where('name','like','%'.$request->q.'%')->orWhere('email','like','%'.$request->q.'%')))->orderByDesc('id')->paginate(30)->withQueryString();

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
        return view('admin.users.show', [
            'user'=>$user,
            'payments'=>\App\Models\PaymentTransaction::whereHas('order',fn($q)=>$q->where('user_id',$user->id))->latest('id')->paginate(10,['*'],'payments_page'),
            'points'=>$user->pointTransactions()->latest('id')->paginate(10,['*'],'points_page'),
            'coins'=>\Illuminate\Support\Facades\DB::table('coin_transactions')->where('user_id',$user->id)->latest('id')->paginate(10,['*'],'coins_page'),
            'voucherOrders'=>\App\Models\Order::where('user_id',$user->id)->whereNotNull('coupon_code')->latest('id')->paginate(10,['*'],'vouchers_page'),
            'vouchers'=>\App\Models\Promotion::where('user_id',$user->id)->latest('id')->paginate(10,['*'],'owned_page'),
        ]);
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
