<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'description'];

    /** Danh mục cấp 1 cha */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** Danh mục cấp 3 thuộc danh mục này */
    public function subSubCategories()
    {
        return $this->hasMany(SubSubCategory::class)->orderBy('name');
    }

    /** Sản phẩm gắn với danh mục cấp 2 */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}