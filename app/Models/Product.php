<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'sub_sub_category_id',
        'sku',
        'name',
        'price',
        'price_old',
        'description',
        'advantages',
        'usage_guide',
        'image',
        'material',
        'style',
        'color',
        'warranty',
        'width',
        'depth',
        'height',
    ];

    /** Danh mục cấp 1 */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** Danh mục cấp 2 */
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    /** Danh mục cấp 3 */
    public function subSubCategory()
    {
        return $this->belongsTo(SubSubCategory::class);
    }


    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Encode từng segment path để URL không vỡ vì ký tự #, space, ...
     */
    public static function storageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Đã là URL đầy đủ
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        // Bỏ prefix storage/ nếu đã có
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        $encoded = collect(explode('/', $path))
            ->map(fn ($seg) => rawurlencode($seg))
            ->implode('/');

        return asset('storage/' . $encoded);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return self::storageUrl($this->image);
        }

        $first = $this->images->first();
        return $first ? $first->url : null;
    }

    public function getMinPriceAttribute()
    {
        $min = $this->variants()->min('price');
        return $min !== null ? $min : $this->price;
    }

    public function getAllImagesAttribute()
    {
        $list = collect();
        if ($this->image) {
            $list->push(self::storageUrl($this->image));
        }
        foreach ($this->images as $img) {
            $list->push($img->url);
        }
        return $list->filter()->unique()->values();
    }
}
