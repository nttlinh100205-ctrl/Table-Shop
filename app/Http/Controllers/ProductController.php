<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\CloudinaryService;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants', 'images'])->latest()->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku'         => 'nullable|string|max:50',
            'name'        => 'required|string|max:255',
            'price'       => 'nullable|numeric|min:0',
            'price_old'   => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'advantages'  => 'nullable|string',
            'usage_guide' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'gallery.*'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'material'    => 'nullable|string|max:255',
            'style'       => 'nullable|string|max:255',
            'color'       => 'nullable|string|max:100',
            'warranty'    => 'nullable|string|max:100',
            'variants'                      => 'nullable|array',
            'variants.*.size_label'         => 'nullable|string|max:100',
            'variants.*.width'              => 'nullable|numeric|min:0',
            'variants.*.depth'              => 'nullable|numeric|min:0',
            'variants.*.height'             => 'nullable|numeric|min:0',
            'variants.*.desktop_width'      => 'nullable|numeric|min:0',
            'variants.*.color'              => 'nullable|string|max:100',
            'variants.*.price'              => 'required_with:variants|numeric|min:0',
            'variants.*.price_old'          => 'nullable|numeric|min:0',
            'variants.*.stock'              => 'nullable|integer|min:0',
            'variants.*.sku'                => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'category_id', 'sku', 'name', 'price', 'price_old', 'description', 'advantages', 'usage_guide',
                'material', 'style', 'color', 'warranty',
            ]);

            if ($request->filled('variants')) {
                $prices = collect($request->variants)->pluck('price')->filter()->map(fn ($p) => (float) $p);
                if ($prices->isNotEmpty()) {
                    $data['price'] = $prices->min();
                }
            }
            $data['price'] = $data['price'] ?? 0;

            if ($request->hasFile('image')) {
                $data['image'] = CloudinaryService::uploadOrStore($request->file('image'), 'products');
            }

            $product = Product::create($data);

            // Gallery nhiều ảnh
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $i => $file) {
                    $path = CloudinaryService::uploadOrStore($file, 'products');
                    $product->images()->create(['path' => $path, 'sort_order' => $i]);
                }
            }

            if ($request->filled('variants')) {
                foreach ($request->variants as $v) {
                    if (empty($v['price']) && empty($v['size_label']) && empty($v['color'])) {
                        continue;
                    }
                    $product->variants()->create([
                        'sku'           => $v['sku'] ?? null,
                        'size_label'    => $v['size_label'] ?? null,
                        'width'         => $v['width'] ?? null,
                        'depth'         => $v['depth'] ?? null,
                        'height'        => $v['height'] ?? null,
                        'desktop_width' => $v['desktop_width'] ?? null,
                        'color'         => $v['color'] ?? null,
                        'price'         => $v['price'] ?? 0,
                        'price_old'     => $v['price_old'] ?? null,
                        'stock'         => $v['stock'] ?? 0,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('products.index')
                             ->with('success', 'Sản phẩm đã được tạo thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $product = Product::with(['category', 'variants', 'images'])->findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with(['variants', 'images'])->findOrFail($id);
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku'         => 'nullable|string|max:50',
            'name'        => 'required|string|max:255',
            'price'       => 'nullable|numeric|min:0',
            'price_old'   => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'advantages'  => 'nullable|string',
            'usage_guide' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'gallery.*'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'material'    => 'nullable|string|max:255',
            'style'       => 'nullable|string|max:255',
            'color'       => 'nullable|string|max:100',
            'warranty'    => 'nullable|string|max:100',
            'variants'                      => 'nullable|array',
            'variants.*.size_label'         => 'nullable|string|max:100',
            'variants.*.width'              => 'nullable|numeric|min:0',
            'variants.*.depth'              => 'nullable|numeric|min:0',
            'variants.*.height'             => 'nullable|numeric|min:0',
            'variants.*.desktop_width'      => 'nullable|numeric|min:0',
            'variants.*.color'              => 'nullable|string|max:100',
            'variants.*.price'              => 'required_with:variants|numeric|min:0',
            'variants.*.price_old'          => 'nullable|numeric|min:0',
            'variants.*.stock'              => 'nullable|integer|min:0',
            'variants.*.sku'                => 'nullable|string|max:50',
        ]);

        $product = Product::findOrFail($id);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'category_id', 'sku', 'name', 'price', 'price_old', 'description', 'advantages', 'usage_guide',
                'material', 'style', 'color', 'warranty',
            ]);

            if ($request->filled('variants')) {
                $prices = collect($request->variants)->pluck('price')->filter()->map(fn ($p) => (float) $p);
                if ($prices->isNotEmpty()) {
                    $data['price'] = $prices->min();
                }
            }
            $data['price'] = $data['price'] ?? $product->price;

            if ($request->hasFile('image')) {
                $this->deleteLocalImage($product->image);
                $data['image'] = CloudinaryService::uploadOrStore($request->file('image'), 'products');
            }

            $product->update($data);

            // Thêm ảnh gallery mới (không xóa ảnh cũ trừ khi user tick xóa)
            if ($request->hasFile('gallery')) {
                $maxOrder = $product->images()->max('sort_order') ?? 0;
                foreach ($request->file('gallery') as $i => $file) {
                    $path = CloudinaryService::uploadOrStore($file, 'products');
                    $product->images()->create(['path' => $path, 'sort_order' => $maxOrder + $i + 1]);
                }
            }

            // Xóa ảnh gallery được chọn
            if ($request->filled('delete_images')) {
                foreach ($request->delete_images as $imgId) {
                    $img = $product->images()->find($imgId);
                    if ($img) {
                        $this->deleteLocalImage($img->path);
                        $img->delete();
                    }
                }
            }

            $product->variants()->delete();
            if ($request->filled('variants')) {
                foreach ($request->variants as $v) {
                    if (empty($v['price']) && empty($v['size_label']) && empty($v['color'])) {
                        continue;
                    }
                    $product->variants()->create([
                        'sku'           => $v['sku'] ?? null,
                        'size_label'    => $v['size_label'] ?? null,
                        'width'         => $v['width'] ?? null,
                        'depth'         => $v['depth'] ?? null,
                        'height'        => $v['height'] ?? null,
                        'desktop_width' => $v['desktop_width'] ?? null,
                        'color'         => $v['color'] ?? null,
                        'price'         => $v['price'] ?? 0,
                        'price_old'     => $v['price_old'] ?? null,
                        'stock'         => $v['stock'] ?? 0,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('products.index')
                             ->with('success', 'Sản phẩm đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $product = Product::with('images')->findOrFail($id);

        $this->deleteLocalImage($product->image);
        foreach ($product->images as $img) {
            $this->deleteLocalImage($img->path);
        }

        $product->delete();

        return redirect()->route('products.index')
                         ->with('success', 'Sản phẩm đã được xóa thành công.');
    }

    /**
     * Xóa an toàn file ảnh lưu trữ local disk (nếu có).
     */
    protected function deleteLocalImage(?string $path): void
    {
        if (empty($path)) {
            return;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:') || strlen($path) > 255) {
            return;
        }
        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Throwable $e) {
            // Không để lỗi filesystem làm hỏng transaction cập nhật/xóa
        }
    }
}
