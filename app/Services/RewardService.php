<?php

namespace App\Services;

use App\Models\User;
use App\Models\PointTransaction;

class RewardService
{
    // Caller owns the transaction and holds a lock on the user.
    public static function points(User $user, int $points, string $description): void
    {
        if ($points <= 0) return;
        PointTransaction::create([
            'user_id' => $user->id, 'type' => 'earn', 'points' => $points,
            'points_used' => 0, 'description' => $description,
            'expires_at' => now()->addDays(config('membership.point_lifetime_days', 365)),
        ]);
        $user->increment('points_balance', $points);
        $user->increment('lifetime_points', $points);
    }
}
