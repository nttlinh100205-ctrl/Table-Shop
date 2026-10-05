<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'size_label',
        'width',
        'depth',
        'height',
        'desktop_width',
        'color',
        'price',
        'price_old',
        'stock',
        'image',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Format số: bỏ .00 nếu là số nguyên
     */
    protected function formatNum($val)
    {
        if ($val === null || $val === '') {
            return '-';
        }
        return fmod((float) $val, 1) == 0 ? (int) $val : $val;
    }

    /**
     * Kích thước đầy đủ: 160 x 120 x 75cm (mặt bàn 60cm)
     */
    public function getDimensionsAttribute()
    {
        if ($this->width || $this->depth || $this->height) {
            $str = $this->formatNum($this->width) . ' x '
                 . $this->formatNum($this->depth) . ' x '
                 . $this->formatNum($this->height) . 'cm';

            if ($this->desktop_width) {
                $str .= ' (mặt bàn ' . $this->formatNum($this->desktop_width) . 'cm)';
            }
            return $str;
        }

        return $this->size_label ?? '—';
    }

    /**
     * Nhãn nút chọn size — ƯU TIÊN kích thước thật
     * VD: 160 x 120 x 75cm
     */
    public function getSizeButtonLabelAttribute()
    {
        if ($this->width || $this->depth || $this->height) {
            return $this->formatNum($this->width) . ' x '
                 . $this->formatNum($this->depth) . ' x '
                 . $this->formatNum($this->height) . 'cm';
        }

        if ($this->size_label) {
            return $this->size_label;
        }

        return 'Mặc định';
    }

    public function getImageUrlAttribute()
    {
        return Product::storageUrl($this->image);
    }
}
