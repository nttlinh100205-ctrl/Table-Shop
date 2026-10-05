<?php
namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Models\{Order, User};
use App\Services\RewardService;
use App\Notifications\ReferralRewarded;
use Illuminate\Support\Facades\DB;

class AwardReferralPoints
{
    public function handle(OrderCompleted $event): void
    {
        DB::transaction(function () use ($event) {
            $order = Order::lockForUpdate()->find($event->orderId);
            if (!$order || $order->status !== 'completed' || !$order->referral_code_applied || $order->referral_rewarded_at) return;
            $owner = User::where('referral_code', $order->referral_code_applied)->lockForUpdate()->first();
            if (!$owner || $owner->id === $order->user_id) return;
            $points = max(0, (int) config('membership.referral_points', 10000));
            RewardService::points($owner, $points, 'Giới thiệu đơn hàng #'.$order->id);
            $order->forceFill(['referral_rewarded_at' => now()])->saveQuietly();
            $owner->notify(new ReferralRewarded($order->id, $points));
        }, 3);
    }
}
