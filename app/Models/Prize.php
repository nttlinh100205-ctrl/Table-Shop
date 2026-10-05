<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'value',
        'probability',
        'quantity',
        'color',
        'text_color',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'value'       => 'decimal:2',
        'probability' => 'integer',
        'quantity'    => 'integer',
        'is_active'   => 'boolean',
    ];

    public function spinHistories()
    {
        return $this->hasMany(SpinHistory::class);
    }

    /**
     * Kiểm tra giải này còn có thể trao không (còn số lượng hoặc vô hạn)
     */
    public function isAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return $this->quantity === -1 || $this->quantity > 0;
    }
}
