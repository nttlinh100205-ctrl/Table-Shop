<?php

namespace App\Services;

use App\Models\{User, Prize, Promotion, SpinHistory};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SpinService
{
    public function spin(int $userId, string $requestId): SpinHistory
    {
        return DB::transaction(function () use ($userId, $requestId) {
            $user = User::lockForUpdate()->findOrFail($userId);
            $existing = SpinHistory::where('user_id', $userId)->where('request_id', $requestId)->first();
            if ($existing) return $existing;
            if ($user->spin_tickets < 1) {
                throw ValidationException::withMessages(['spin' => 'Bạn đã hết lượt quay.']);
            }
            $prizes = Prize::where('is_active', true)->where('probability', '>', 0)
                ->where(fn ($q) => $q->where('quantity', -1)->orWhere('quantity', '>', 0))
                ->orderBy('id')->lockForUpdate()->get();
            if ($prizes->isEmpty()) {
                throw ValidationException::withMessages(['spin' => 'Hiện chưa có giải thưởng khả dụng.']);
            }
            $draw = random_int(1, (int) $prizes->sum('probability'));
            foreach ($prizes as $prize) {
                $draw -= $prize->probability;
                if ($draw <= 0) break;
            }
            $user->decrement('spin_tickets');
            if ($prize->quantity > 0) $prize->decrement('quantity');
            $detail = $prize->name;
            switch ($prize->type) {
                case 'points':
                    RewardService::points($user, (int) $prize->value, 'Vòng quay: '.$prize->name);
                    break;
                case 'ticket':
                    $user->increment('spin_tickets', max(0, (int) $prize->value));
                    break;
                case 'voucher':
                    $code = 'SPIN-'.strtoupper(Str::random(16));
                    Promotion::create([
                        'user_id' => $user->id, 'code' => $code, 'name' => $prize->name,
                        'discount_type' => 'fixed', 'discount_value' => $prize->value,
                        'min_order_amount' => 0, 'usage_limit' => 1, 'used_count' => 0,
                        'start_date' => now(), 'end_date' => now()->addDays(30), 'is_active' => true,
                    ]);
                    $detail = 'Mã '.$code.' — có hiệu lực 30 ngày';
                    break;
            }
            return SpinHistory::create([
                'user_id' => $user->id, 'prize_id' => $prize->id, 'prize_name' => $prize->name,
                'reward_detail' => $detail, 'request_id' => $requestId, 'created_at' => now(),
            ]);
        }, 3);
    }
}
