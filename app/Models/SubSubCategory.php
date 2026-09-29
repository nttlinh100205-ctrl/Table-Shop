<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubSubCategory extends Model
{
    use HasFactory;

    protected $fillable = ['sub_category_id', 'name', 'description'];

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}