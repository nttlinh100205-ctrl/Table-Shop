<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
        'total_price'         => 'decimal:2',
        'ghn_total_fee'       => 'integer',
        'to_district_id'      => 'integer',
        'cancel_requested_at' => 'datetime',
        'cancel_processed_at' => 'datetime',
        'return_requested_at' => 'datetime',
        'return_processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
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
