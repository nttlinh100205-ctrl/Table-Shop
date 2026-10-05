<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'coins_used',
        'coin_discount_amount',
        'user_id',
        'promotion_id',
        'coupon_code',
        'referral_code_applied',
        'discount_amount',
        'name',
        'phone',
        'address',
        'total_price',
        'status',
        'shipping_status',
        'cancel_reason',
        'cancel_previous_status',
        'cancel_admin_note',
        'cancel_requested_at',
        'cancel_processed_at',
        'ghn_order_code',
        'ghn_total_fee',
        'to_district_id',
        'to_ward_code',
        'return_status',
        'return_reason',
        'return_admin_note',
        'return_requested_at',
        'return_processed_at',
    ];

    protected $casts = [
        'coins_used' => 'integer',
        'coin_discount_amount' => 'integer',
        'coins_refunded_at' => 'datetime',
        'total_price'         => 'decimal:2',
        'discount_amount'     => 'decimal:2',
        'ghn_total_fee'       => 'integer',
        'to_district_id'      => 'integer',
        'cancel_requested_at' => 'datetime',
        'cancel_processed_at' => 'datetime',
        'return_requested_at' => 'datetime',
        'return_processed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::updating(function ($order) {
            if ($order->isDirty('status') && $order->status !== 'cancelled'
                && static::whereKey($order->id)->whereNotNull('coins_refunded_at')->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['status' => 'Đơn đã hoàn xu không thể mở lại. Vui lòng tạo đơn mới.']);
            }
        });
        static::saved(function ($order) {
            if ($order->status === 'cancelled' && $order->coins_used) {
                app(\App\Services\CoinService::class)->refund($order);
            }
            if ($order->status === 'completed') {
                event(new \App\Events\OrderCompleted($order->id));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referralUser()
    {
        return $this->belongsTo(User::class, 'referral_code_applied', 'referral_code');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function hasReviewedProduct(int $productId): bool
    {
        return $this->reviews()->where('product_id', $productId)->exists();
    }

    public function getReviewForProduct(int $productId): ?Review
    {
        return $this->reviews()->where('product_id', $productId)->first();
    }

    public function canPayMomo(): bool
    {
        return !in_array($this->status, ['paid', 'cod_ordered', 'cancelled', 'completed'], true)
            && empty($this->ghn_order_code);
    }

    /**
     * Được yêu cầu trả hàng khi đang giao / đã giao và chưa có yêu cầu mở.
     * (Xem hàng tại chỗ → không ưng → trả)
     */
    public function canRequestReturn(): bool
    {
        if ($this->status === 'cancelled') {
            return false;
        }
        if (in_array($this->return_status, ['requested', 'approved', 'completed'], true)) {
            return false;
        }

        return in_array($this->shipping_status, [
            'delivering', 'delivered', 'picked', 'storing', 'transporting', 'sorting',
        ], true);
    }

    public function hasOpenReturn(): bool
    {
        return in_array($this->return_status, ['requested', 'approved'], true);
    }
}
