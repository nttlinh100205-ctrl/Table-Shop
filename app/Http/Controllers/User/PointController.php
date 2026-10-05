<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Services\CloudinaryService;
use App\Services\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PointController extends Controller
{
    /**
     * Trang xem Lịch sử điểm & Đổi voucher ưu đãi
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tier = $user->tier;
        $pointsBalance = (int) $user->points_balance;
        $lifetimePoints = (int) $user->lifetime_points;
        $expiringPoints = $user->getExpiringPointsWithin(30);

        // Danh sách các gói đổi voucher
        $rawPackages = config('membership.voucher_packages', []);
        $tierLevels = [
            'bronze'  => 0,
            'silver'  => 1,
            'gold'    => 2,
            'diamond' => 3,
        ];
        $userLevel = $tierLevels[$tier['key']] ?? 0;

        $packages = [];
        foreach ($rawPackages as $key => $pkg) {
            $reqLevel = $tierLevels[$pkg['min_tier'] ?? 'bronze'] ?? 0;
            $hasTier = $userLevel >= $reqLevel;
            $hasPoints = $pointsBalance >= (int) $pkg['points_required'];

            $pkg['can_redeem'] = $hasTier && $hasPoints;
            if (!$hasTier) {
                $minTierName = config("membership.tiers.{$pkg['min_tier']}.name", $pkg['min_tier']);
                $pkg['lock_reason'] = "Yêu cầu đạt từ {$minTierName} trở lên";
            } elseif (!$hasPoints) {
                $missing = (int) $pkg['points_required'] - $pointsBalance;
                $pkg['lock_reason'] = 'Cần thêm ' . number_format($missing, 0, ',', '.') . ' điểm';
            } else {
                $pkg['lock_reason'] = null;
            }

            $packages[$key] = $pkg;
        }

        // Lịch sử biến động điểm (cộng / đổi / hết hạn / hoàn trả)
        $transactions = $user->pointTransactions()
            ->with('order')
            ->paginate(12)
            ->withQueryString();

        // Danh sách voucher cá nhân đã đổi và còn hạn sử dụng
        $myVouchers = Promotion::where('user_id', $user->id)
            ->active()
            ->notExpired()
            ->latest()
            ->get();

        return view('user.points.index', compact(
            'user',
            'tier',
            'pointsBalance',
            'lifetimePoints',
            'expiringPoints',
            'packages',
            'transactions',
            'myVouchers'
        ));
    }

    /**
     * Xử lý đổi điểm lấy Voucher
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'package_id' => 'required|string',
        ], [
            'package_id.required' => 'Vui lòng chọn gói ưu đãi cần đổi.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $result = MembershipService::redeemVoucher($user, $request->package_id);
            return back()->with('success', $result['message']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cập nhật ảnh đại diện cá nhân (Avatar) qua Cloudinary
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ], [
            'avatar.required' => 'Vui lòng chọn file hình ảnh đại diện.',
            'avatar.image'    => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'avatar.max'      => 'Dung lượng ảnh tối đa 5MB.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $url = CloudinaryService::uploadOrStore($request->file('avatar'), 'avatars');
        if ($url) {
            $user->update(['avatar' => $url]);
            return back()->with('success', 'Cập nhật ảnh đại diện thành công!');
        }

        return back()->with('error', 'Không thể tải lên ảnh đại diện. Vui lòng thử lại sau.');
    }
}
