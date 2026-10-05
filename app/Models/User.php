<?php

namespace App\Models;

use App\Notifications\QueuedVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'referral_code',
        'password',
        'role',
        'points_balance',
        'lifetime_points',
        'spin_tickets',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'points_balance'    => 'integer',
        'lifetime_points'   => 'integer',
        'spin_tickets'      => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                do {
                    $code = 'REF-' . strtoupper(\Illuminate\Support\Str::random(6));
                } while (static::where('referral_code', $code)->exists());
                $user->referral_code = $code;
            }
            if (!isset($user->spin_tickets)) {
                $user->spin_tickets = 3;
            }
        });
    }

    public function spinHistories()
    {
        return $this->hasMany(SpinHistory::class)->orderByDesc('id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class)->orderByDesc('id');
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Lấy thông tin Hạng thành viên hiện tại dựa trên tổng điểm tích lũy trọn đời (lifetime_points)
     * Hạng KHÔNG bị mất khi điểm sử dụng hoặc hết hạn.
     */
    public function getTierAttribute(): array
    {
        $tiers = config('membership.tiers', []);
        $lifetime = (int) $this->lifetime_points;

        $currentTierKey = 'bronze';
        $currentTier = $tiers['bronze'] ?? [
            'name'        => 'Thành viên Đồng',
            'min_points'  => 0,
            'color'       => '#a87957',
            'badge_class' => 'bg-secondary',
            'description' => 'Hạng mặc định',
        ];

        // Sắp xếp các hạng theo min_points tăng dần
        uasort($tiers, fn ($a, $b) => $a['min_points'] <=> $b['min_points']);

        $nextTier = null;
        $tierKeys = array_keys($tiers);

        foreach ($tierKeys as $idx => $key) {
            $info = $tiers[$key];
            if ($lifetime >= (int) $info['min_points']) {
                $currentTierKey = $key;
                $currentTier = $info;
                $nextTier = isset($tierKeys[$idx + 1]) ? $tiers[$tierKeys[$idx + 1]] : null;
            }
        }

        $progressPercent = 100;
        $pointsNeeded = 0;

        if ($nextTier) {
            $range = (int) $nextTier['min_points'] - (int) $currentTier['min_points'];
            $currentOverBase = max(0, $lifetime - (int) $currentTier['min_points']);
            $progressPercent = $range > 0 ? min(100, (int) round(($currentOverBase / $range) * 100)) : 100;
            $pointsNeeded = max(0, (int) $nextTier['min_points'] - $lifetime);
        }

        return [
            'key'              => $currentTierKey,
            'name'             => $currentTier['name'] ?? 'Thành viên',
            'min_points'       => (int) ($currentTier['min_points'] ?? 0),
            'color'            => $currentTier['color'] ?? '#a87957',
            'badge_class'      => $currentTier['badge_class'] ?? 'bg-secondary',
            'description'      => $currentTier['description'] ?? '',
            'next_tier'        => $nextTier,
            'points_needed'    => $pointsNeeded,
            'progress_percent' => $progressPercent,
        ];
    }

    /**
     * Điểm sắp hết hạn trong N ngày tới (mặc định 30 ngày)
     */
    public function getExpiringPointsWithin(int $days = 30): int
    {
        return (int) $this->pointTransactions()
            ->where('type', 'earn')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays($days))
            ->whereRaw('(points - points_used) > 0')
            ->selectRaw('SUM(points - points_used) as expiring_total')
            ->value('expiring_total') ?? 0;
    }

    /**
     * URL Avatar người dùng (nếu chưa có thì trả về null)
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return !empty($this->avatar) ? $this->avatar : null;
    }

    /**
     * Kiểm tra user có phải admin không.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kiểm tra user thường.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Gửi email xác thực qua queue để tránh 504 khi SMTP chậm.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail());
    }
}
