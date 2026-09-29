<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /** Danh mục cấp 2 thuộc danh mục này */
    public function subCategories()
    {
        return $this->hasMany(SubCategory::class)->orderBy('name');
    }

    /** Sản phẩm gắn trực tiếp với danh mục cấp 1 */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Lấy tất cả ID sản phẩm liên quan (cấp 1 + cấp 2 + cấp 3).
     * Dùng để lọc sản phẩm khi click danh mục gốc.
     */
    public function allRelatedProductQuery()
    {
        $subIds = $this->subCategories()->pluck('id')->all();
        $subSubIds = SubSubCategory::whereIn('sub_category_id', $subIds)->pluck('id')->all();

        return Product::query()
            ->where(function ($q) use ($subIds, $subSubIds) {
                $q->where('category_id', $this->id);
                if (!empty($subIds)) {
                    $q->orWhereIn('sub_category_id', $subIds);
                }
                if (!empty($subSubIds)) {
                    $q->orWhereIn('sub_sub_category_id', $subSubIds);
                }
            });
    }
}
