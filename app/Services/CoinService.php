<?php
namespace App\Services;

use App\Models\{User, Order};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CoinService
{
    public function today(): string { return now(config('coins.timezone'))->toDateString(); }

    public function status(User $user): array
    {
        $last = DB::table('daily_check_ins')->where('user_id', $user->id)->latest('id')->first();
        return ['balance' => (int) $user->fresh()->coin_balance,
            'claimed_today' => $last && $last->checked_date === $this->today(),
            'cycle_day' => $last ? (int) $last->cycle_day : 0,
            'next_day' => $last ? ((int) $last->cycle_day % config('coins.cycle_days')) + 1 : 1];
    }

    public function checkIn(int $userId): array
    {
        return DB::transaction(function () use ($userId) {
            $user = User::lockForUpdate()->findOrFail($userId);
            $today = $this->today();
            $existing = DB::table('daily_check_ins')->where('user_id', $userId)->where('checked_date', $today)->first();
            if ($existing) return ['awarded' => false, 'coins' => $existing->coins] + $this->status($user);
            $last = DB::table('daily_check_ins')->where('user_id', $userId)->latest('id')->first();
            $day = $last ? ((int) $last->cycle_day % config('coins.cycle_days')) + 1 : 1;
            $coins = (int) config($day === config('coins.cycle_days') ? 'coins.last_day_reward' : 'coins.daily_reward');
            $id = DB::table('daily_check_ins')->insertGetId(['user_id' => $userId, 'checked_date' => $today,
                'cycle_day' => $day, 'coins' => $coins, 'created_at' => now(), 'updated_at' => now()]);
            $user->increment('coin_balance', $coins);
            DB::table('coin_transactions')->insert(['user_id' => $userId, 'daily_check_in_id' => $id,
                'type' => 'check_in', 'amount' => $coins, 'description' => "Điểm danh lần {$day}/7",
                'created_at' => now(), 'updated_at' => now()]);
            return ['awarded' => true, 'coins' => $coins] + $this->status($user);
        }, 3);
    }

    // Called inside the checkout transaction, with a user row lock held.
    public function discount(User $user, int $requested, int $goods): int
    {
        $rate = max(1, (int) config('coins.vnd_per_coin'));
        $maximum = min((int) $user->coin_balance, intdiv(max(0, $goods - config('coins.minimum_goods_payment')), $rate));
        if ($requested < 0 || $requested > $maximum) {
            throw ValidationException::withMessages(['coins_to_use' => "Bạn có thể dùng tối đa {$maximum} xu cho đơn này."]);
        }
        return $requested * $rate;
    }

    public function spend(User $user, Order $order, int $coins): void
    {
        if ($coins === 0) return;
        $user->decrement('coin_balance', $coins);
        DB::table('coin_transactions')->insert(['user_id' => $user->id, 'order_id' => $order->id,
            'type' => 'spend', 'amount' => -$coins, 'description' => 'Dùng xu cho đơn #'.$order->id,
            'created_at' => now(), 'updated_at' => now()]);
    }

    public function refund(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::lockForUpdate()->findOrFail($order->id);
            if ($locked->status !== 'cancelled' || !$locked->coins_used || $locked->coins_refunded_at) return;
            $user = User::lockForUpdate()->findOrFail($locked->user_id);
            $user->increment('coin_balance', $locked->coins_used);
            DB::table('coin_transactions')->insert(['user_id' => $user->id, 'order_id' => $locked->id,
                'type' => 'refund', 'amount' => $locked->coins_used, 'description' => 'Hoàn xu đơn hủy #'.$locked->id,
                'created_at' => now(), 'updated_at' => now()]);
            $locked->forceFill(['coins_refunded_at' => now()])->saveQuietly();
        }, 3);
    }
}
