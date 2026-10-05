<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'points',
        'points_used',
        'expires_at',
        'description',
    ];

    protected $casts = [
        'points'      => 'integer',
        'points_used' => 'integer',
        'expires_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Số điểm còn lại chưa sử dụng của lô cộng điểm này
     */
    public function getAvailablePointsAttribute(): int
    {
        if ($this->type !== 'earn') {
            return 0;
        }

        return max(0, $this->points - $this->points_used);
    }

    /**
     * Kiểm tra lô điểm này đã hết hạn hay chưa
     */
    public function getIsExpiredAttribute(): bool
    {
        return !empty($this->expires_at) && now()->gt($this->expires_at);
    }
}
