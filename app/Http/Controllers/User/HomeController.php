<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Trang chủ dành cho người dùng thường.
     * Hỗ trợ lọc danh mục (?category= / ?sub_category= / ?sub_sub_category=) và tìm kiếm (?q=).
     * Khi chọn danh mục cha, hiện cả sản phẩm của danh mục con.
     */
    public function index(Request $request)
    {
        $categories = Category::with(['subCategories.subSubCategories'])->orderBy('name')->get();

        $query = Product::with(['category', 'images', 'variants'])->latest();

        // Lọc theo danh mục cấp 1 (kèm con cháu)
        if ($request->filled('category')) {
            $cat = Category::find($request->category);
            if ($cat) {
                $query = $cat->allRelatedProductQuery()->with(['category', 'images', 'variants'])->latest();
            }
        }

        // Lọc theo danh mục cấp 2
        if ($request->filled('sub_category')) {
            $sub = SubCategory::find($request->sub_category);
            if ($sub) {
                $subSubIds = $sub->subSubCategories()->pluck('id')->all();
                $query->where(function ($q) use ($sub, $subSubIds) {
                    $q->where('sub_category_id', $sub->id);
                    if (!empty($subSubIds)) {
                        $q->orWhereIn('sub_sub_category_id', $subSubIds);
                    }
                });
            }
        }

        // Lọc theo danh mục cấp 3
        if ($request->filled('sub_sub_category')) {
            $query->where('sub_sub_category_id', $request->sub_sub_category);
        }

        // Tìm kiếm theo tên / sku
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('sku', 'like', '%' . $keyword . '%');
            });
        }

        $products = $query->take(24)->get();

        $currentCategory = null;
        if ($request->filled('category')) {
            $currentCategory = Category::find($request->category);
        } elseif ($request->filled('sub_category')) {
            $currentCategory = SubCategory::find($request->sub_category);
        } elseif ($request->filled('sub_sub_category')) {
            $currentCategory = SubSubCategory::find($request->sub_sub_category);
        }

        if ($currentCategory) {
            session()->put('shopping_behavior', ['last_category' => $currentCategory->name]);
        }
        return view('user.home', compact('products', 'categories', 'currentCategory'));
    }

    /**
     * Trang xem chi tiết sản phẩm (user).
     */
    public function show($id)
    {
        $product = Product::with(['category', 'variants', 'images', 'reviews.user'])
            ->findOrFail($id);

        session()->put('shopping_behavior', [
            'last_category' => $product->category?->name,
            'category_id' => $product->category_id,
            'last_product_name' => $product->name,
            'last_product_price' => (float) $product->price,
            'price_range' => ['min' => (float) $product->price * 0.7, 'max' => (float) $product->price * 1.3],
        ]);
        return view('user.products.show', compact('product'));
    }
}
