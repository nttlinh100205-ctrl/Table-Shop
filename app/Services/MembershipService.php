<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MembershipService
{
    /**
     * Thứ tự các hạng thành viên từ thấp đến cao để so sánh điều kiện đổi gói
     */
    protected static array $tierLevels = [
        'bronze'  => 0,
        'silver'  => 1,
        'gold'    => 2,
        'diamond' => 3,
    ];

    /**
     * Cộng điểm khi đơn hàng hoàn thành (Idempotent - chỉ cộng 1 lần duy nhất cho mỗi đơn)
     * Tỷ lệ: 1 điểm / 10đ (ví dụ đơn 3.000.000đ = 300.000 điểm).
     * Hạn dùng điểm: 1 năm.
     */
    public static function awardOrderPoints(Order $order): ?PointTransaction
    {
        if (empty($order->user_id)) {
            return null;
        }

        // Kiểm tra idempotency: đơn này đã được cộng điểm trước đó chưa
        $alreadyAwarded = PointTransaction::where('order_id', $order->id)
            ->where('type', 'earn')
            ->exists();

        if ($alreadyAwarded) {
            return null;
        }

        $earnRate = (int) config('membership.earn_rate', 10);
        if ($earnRate <= 0) {
            $earnRate = 10;
        }

        $points = (int) floor((float) $order->total_price / $earnRate);
        if ($points <= 0) {
            return null;
        }

        $lifetimeDays = (int) config('membership.point_lifetime_days', 365);
        $expiresAt = now()->addDays($lifetimeDays);

        return DB::transaction(function () use ($order, $points, $expiresAt) {
            /** @var User $user */
            $user = User::lockForUpdate()->find($order->user_id);
            if (!$user) {
                return null;
            }

            // Ghi nhận transaction cộng điểm
            $tx = PointTransaction::create([
                'user_id'      => $user->id,
                'order_id'     => $order->id,
                'type'         => 'earn',
                'points'       => $points,
                'points_used'  => 0,
                'expires_at'   => $expiresAt,
                'description'  => "Tích điểm từ đơn hàng hoàn thành #{$order->id} (" . number_format($order->total_price, 0, ',', '.') . 'đ)',
            ]);

            // Cập nhật số dư điểm khả dụng và tổng điểm tích lũy trọn đời
            $user->increment('points_balance', $points);
            $user->increment('lifetime_points', $points);

            Log::info("Awarded {$points} points to User #{$user->id} for Order #{$order->id}. New balance: {$user->points_balance}");

            return $tx;
        });
    }

    /**
     * Thu hồi điểm khi đơn hàng bị huỷ hoặc trả hàng hoàn tiền
     */
    public static function revokeOrderPoints(Order $order): ?PointTransaction
    {
        if (empty($order->user_id)) {
            return null;
        }

        // Tìm giao dịch earn của đơn này
        $earnTx = PointTransaction::where('order_id', $order->id)
            ->where('type', 'earn')
            ->first();

        if (!$earnTx) {
            return null;
        }

        // Kiểm tra xem đơn này đã từng bị thu hồi điểm chưa (tránh trừ 2 lần)
        $alreadyRevoked = PointTransaction::where('order_id', $order->id)
            ->where('type', 'refund')
            ->exists();

        if ($alreadyRevoked) {
            return null;
        }

        return DB::transaction(function () use ($order, $earnTx) {
            /** @var User $user */
            $user = User::lockForUpdate()->find($order->user_id);
            if (!$user) {
                return null;
            }

            $pointsToRevoke = (int) $earnTx->points;

            $tx = PointTransaction::create([
                'user_id'      => $user->id,
                'order_id'     => $order->id,
                'type'         => 'refund',
                'points'       => -$pointsToRevoke,
                'points_used'  => 0,
                'expires_at'   => null,
                'description'  => "Thu hồi điểm do huỷ/hoàn đơn hàng #{$order->id}",
            ]);

            // Giảm số dư điểm khả dụng (không để âm)
            $newBalance = max(0, (int) $user->points_balance - $pointsToRevoke);
            $newLifetime = max(0, (int) $user->lifetime_points - $pointsToRevoke);

            $user->update([
                'points_balance'  => $newBalance,
                'lifetime_points' => $newLifetime,
            ]);

            Log::info("Revoked {$pointsToRevoke} points from User #{$user->id} for Order #{$order->id}. New balance: {$newBalance}");

            return $tx;
        });
    }

    /**
     * Đổi điểm lấy voucher khuyến mãi
     * Áp dụng trừ điểm theo FIFO (lô điểm cũ nhận trước sẽ được trừ trước).
     * Điểm bị trừ trong Transaction; sinh mã voucher riêng cho user.
     */
    public static function redeemVoucher(User $user, string $packageId): array
    {
        $packages = config('membership.voucher_packages', []);
        if (!isset($packages[$packageId])) {
            throw new \InvalidArgumentException('Gói đổi voucher không hợp lệ hoặc đã ngừng áp dụng.');
        }

        $package = $packages[$packageId];
        $requiredPoints = (int) $package['points_required'];
        $minTier = $package['min_tier'] ?? 'bronze';

        $userTier = $user->tier['key'];
        $userLevel = self::$tierLevels[$userTier] ?? 0;
        $reqLevel = self::$tierLevels[$minTier] ?? 0;

        if ($userLevel < $reqLevel) {
            $minTierName = config("membership.tiers.{$minTier}.name", $minTier);
            throw new \RuntimeException("Gói ưu đãi này chỉ dành cho thành viên đạt hạng từ {$minTierName} trở lên.");
        }

        return DB::transaction(function () use ($user, $package, $requiredPoints) {
            /** @var User $lockedUser */
            $lockedUser = User::lockForUpdate()->find($user->id);

            if ((int) $lockedUser->points_balance < $requiredPoints) {
                throw new \RuntimeException('Số dư điểm thưởng hiện tại không đủ để đổi gói ưu đãi này.');
            }

            // 1. Trừ điểm theo FIFO từ các lô earn còn hạn
            $remainingToDeduct = $requiredPoints;

            $earnLots = PointTransaction::where('user_id', $lockedUser->id)
                ->where('type', 'earn')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->whereRaw('(points - points_used) > 0')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($earnLots as $lot) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

                $availableInLot = (int) ($lot->points - $lot->points_used);
                $deduct = min($remainingToDeduct, $availableInLot);

                $lot->increment('points_used', $deduct);
                $remainingToDeduct -= $deduct;
            }

            // Cập nhật số dư khả dụng của user
            $lockedUser->decrement('points_balance', $requiredPoints);

            // 2. Ghi nhận giao dịch đổi điểm
            $tx = PointTransaction::create([
                'user_id'      => $lockedUser->id,
                'order_id'     => null,
                'type'         => 'redeem',
                'points'       => -$requiredPoints,
                'points_used'  => 0,
                'expires_at'   => null,
                'description'  => "Đổi {$package['name']} (-" . number_format($requiredPoints, 0, ',', '.') . ' điểm)',
            ]);

            // 3. Sinh Voucher riêng cho User (áp dụng 1 lần ở checkout)
            $voucherCode = self::generateUniqueVoucherCode();
            $validDays = (int) ($package['valid_days'] ?? 30);

            $promotion = Promotion::create([
                'user_id'             => $lockedUser->id,
                'code'                => $voucherCode,
                'name'                => $package['name'],
                'description'         => "Voucher đổi từ điểm thưởng thành viên dành riêng cho {$lockedUser->name} ({$lockedUser->tier['name']})",
                'discount_type'       => 'fixed',
                'discount_value'      => $package['discount_value'],
                'min_order_amount'    => $package['min_order'],
                'max_discount_amount' => null,
                'usage_limit'         => 1,
                'used_count'          => 0,
                'start_date'          => now(),
                'end_date'            => now()->addDays($validDays),
                'is_active'           => true,
            ]);

            Log::info("User #{$lockedUser->id} redeemed package {$package['name']} with code {$voucherCode}");

            return [
                'success'     => true,
                'message'     => "Đổi thành công gói {$package['name']}! Mã voucher của bạn là: {$voucherCode}",
                'voucher'     => $promotion,
                'package'     => $package,
                'transaction' => $tx,
                'new_balance' => $lockedUser->points_balance,
            ];
        });
    }

    /**
     * Xử lý hết hạn điểm thưởng theo lô hằng ngày
     * Quét các transaction 'earn' có expires_at <= now() và (points - points_used) > 0
     */
    public static function expirePoints(): int
    {
        $expiredLots = PointTransaction::where('type', 'earn')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->whereRaw('(points - points_used) > 0')
            ->get();

        if ($expiredLots->isEmpty()) {
            return 0;
        }

        $totalExpiredPoints = 0;

        // Gom nhóm theo user_id để xử lý trong từng transaction riêng biệt
        $lotsByUser = $expiredLots->groupBy('user_id');

        foreach ($lotsByUser as $userId => $lots) {
            DB::transaction(function () use ($userId, $lots, &$totalExpiredPoints) {
                /** @var User $user */
                $user = User::lockForUpdate()->find($userId);
                if (!$user) {
                    return;
                }

                $userExpiredPoints = 0;

                foreach ($lots as $lot) {
                    $unclaimed = (int) ($lot->points - $lot->points_used);
                    if ($unclaimed > 0) {
                        $lot->update(['points_used' => $lot->points]);
                        $userExpiredPoints += $unclaimed;
                    }
                }

                if ($userExpiredPoints > 0) {
                    $newBalance = max(0, (int) $user->points_balance - $userExpiredPoints);
                    $user->update(['points_balance' => $newBalance]);

                    // Ghi nhận transaction hết hạn điểm
                    PointTransaction::create([
                        'user_id'      => $user->id,
                        'order_id'     => null,
                        'type'         => 'expire',
                        'points'       => -$userExpiredPoints,
                        'points_used'  => 0,
                        'expires_at'   => null,
                        'description'  => "Điểm thưởng hết hạn sử dụng sau 1 năm (-" . number_format($userExpiredPoints, 0, ',', '.') . ' điểm)',
                    ]);

                    $totalExpiredPoints += $userExpiredPoints;
                    Log::info("Expired {$userExpiredPoints} points for User #{$user->id}. New balance: {$newBalance}");
                }
            });
        }

        return $totalExpiredPoints;
    }

    /**
     * Sinh mã voucher ngẫu nhiên không trùng lặp
     */
    protected static function generateUniqueVoucherCode(): string
    {
        do {
            $code = 'VIP-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (Promotion::where('code', $code)->exists());

        return $code;
    }
}
