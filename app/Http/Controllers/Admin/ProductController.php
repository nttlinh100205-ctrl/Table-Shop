<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Color;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');

        $query = Product::with(['category', 'variants', 'images'])
            ->withCount('variants');

        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $totalProducts = Product::count();

        return view('admin.products.index', compact('products', 'categories', 'q', 'categoryId', 'totalProducts'));
    }

    public function create()
    {
        $categoryTree = Category::with(['subCategories.subSubCategories'])->orderBy('name')->get();
        $colors = Color::active()->orderBy('sort_order')->orderBy('name')->get()->groupBy('group');
        return view('admin.products.create', compact('categoryTree', 'colors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'         => 'required|exists:categories,id',
            'sub_category_id'     => 'nullable|exists:sub_categories,id',
            'sub_sub_category_id' => 'nullable|exists:sub_sub_categories,id',
            'sku'                 => 'nullable|string|max:50',
            'name'                => 'required|string|max:255',
            'price'               => 'nullable|numeric|min:0',
            'price_old'           => 'nullable|numeric|min:0',
            'description'         => 'nullable|string',
            'advantages'          => 'nullable|string',
            'usage_guide'         => 'nullable|string',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'image_url'           => 'nullable|string|max:1000',
            'gallery.*'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'material'            => 'nullable|string|max:255',
            'style'               => 'nullable|string|max:255',
            'color'               => 'nullable|string|max:100',
            'warranty'            => 'nullable|string|max:100',
            'variants'                      => 'nullable|array',
            'variants.*.size_label'         => 'nullable|string|max:100',
            'variants.*.width'              => 'nullable|numeric|min:0',
            'variants.*.depth'              => 'nullable|numeric|min:0',
            'variants.*.height'             => 'nullable|numeric|min:0',
            'variants.*.desktop_width'      => 'nullable|numeric|min:0',
            'variants.*.color'              => 'nullable|string|max:100',
            'variants.*.price'              => 'nullable|numeric|min:0',
            'variants.*.price_old'          => 'nullable|numeric|min:0',
            'variants.*.stock'              => 'nullable|integer|min:0',
            'variants.*.sku'                => 'nullable|string|max:50',
            'sizes'                         => 'nullable|array',
            'sizes.*.size_label'            => 'nullable|string|max:100',
            'sizes.*.width'                 => 'nullable|numeric|min:0',
            'sizes.*.depth'                 => 'nullable|numeric|min:0',
            'sizes.*.height'                => 'nullable|numeric|min:0',
            'sizes.*.desktop_width'         => 'nullable|numeric|min:0',
            'sizes.*.price'                 => 'nullable|numeric|min:0',
            'sizes.*.price_old'             => 'nullable|numeric|min:0',
            'sizes.*.colors'                => 'nullable|array',
            'sizes.*.colors.*'              => 'nullable|string|max:100',
            'sizes.*.color_stocks'          => 'nullable|array',
            'sizes.*.color_stocks.*'        => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'category_id', 'sub_category_id', 'sub_sub_category_id',
                'sku', 'name', 'price', 'price_old', 'description', 'advantages', 'usage_guide',
                'material', 'style', 'color', 'warranty',
            ]);
            // Chuẩn hóa: nếu không chọn cấp 2/3 thì để null
            $data['sub_category_id']     = $request->filled('sub_category_id') ? $request->sub_category_id : null;
            $data['sub_sub_category_id'] = $request->filled('sub_sub_category_id') ? $request->sub_sub_category_id : null;

            if ($request->filled('sizes')) {
                $prices = collect($request->sizes)->pluck('price')->filter()->map(fn ($p) => (float) $p);
                if ($prices->isNotEmpty()) {
                    $data['price'] = $prices->min();
                }
            } elseif ($request->filled('variants')) {
                $prices = collect($request->variants)->pluck('price')->filter()->map(fn ($p) => (float) $p);
                if ($prices->isNotEmpty()) {
                    $data['price'] = $prices->min();
                }
            }
            $data['price'] = $data['price'] ?? 0;

            if ($request->hasFile('image')) {
                $data['image'] = CloudinaryService::uploadOrStore($request->file('image'), 'products');
            } elseif ($request->filled('image_url')) {
                $data['image'] = trim($request->input('image_url'));
            }

            $product = Product::create($data);

            // Gallery nhiều ảnh
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $i => $file) {
                    $path = CloudinaryService::uploadOrStore($file, 'products');
                    $product->images()->create(['path' => $path, 'sort_order' => $i]);
                }
            }

            $this->syncVariants($product, $request);

            DB::commit();
            return redirect()->route('admin.products.index')
                             ->with('success', 'Sản phẩm đã được tạo thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $product = Product::with(['category', 'variants', 'images'])->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with(['variants', 'images', 'category', 'subCategory', 'subSubCategory'])->findOrFail($id);
        $categoryTree = Category::with(['subCategories.subSubCategories'])->orderBy('name')->get();
        $colors = Color::active()->orderBy('sort_order')->orderBy('name')->get()->groupBy('group');
        return view('admin.products.edit', compact('product', 'categoryTree', 'colors'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id'         => 'required|exists:categories,id',
            'sub_category_id'     => 'nullable|exists:sub_categories,id',
            'sub_sub_category_id' => 'nullable|exists:sub_sub_categories,id',
            'sku'                 => 'nullable|string|max:50',
            'name'                => 'required|string|max:255',
            'price'               => 'nullable|numeric|min:0',
            'price_old'           => 'nullable|numeric|min:0',
            'description'         => 'nullable|string',
            'advantages'          => 'nullable|string',
            'usage_guide'         => 'nullable|string',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'image_url'           => 'nullable|string|max:1000',
            'gallery.*'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'material'            => 'nullable|string|max:255',
            'style'               => 'nullable|string|max:255',
            'color'               => 'nullable|string|max:100',
            'warranty'            => 'nullable|string|max:100',
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
            'sizes'                         => 'nullable|array',
            'sizes.*.size_label'            => 'nullable|string|max:100',
            'sizes.*.width'                 => 'nullable|numeric|min:0',
            'sizes.*.depth'                 => 'nullable|numeric|min:0',
            'sizes.*.height'                => 'nullable|numeric|min:0',
            'sizes.*.desktop_width'         => 'nullable|numeric|min:0',
            'sizes.*.price'                 => 'nullable|numeric|min:0',
            'sizes.*.price_old'             => 'nullable|numeric|min:0',
            'sizes.*.colors'                => 'nullable|array',
            'sizes.*.colors.*'              => 'nullable|string|max:100',
            'sizes.*.color_stocks'          => 'nullable|array',
            'sizes.*.color_stocks.*'        => 'nullable|integer|min:0',
        ]);

        $product = Product::findOrFail($id);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'category_id', 'sub_category_id', 'sub_sub_category_id',
                'sku', 'name', 'price', 'price_old', 'description', 'advantages', 'usage_guide',
                'material', 'style', 'color', 'warranty',
            ]);
            $data['sub_category_id']     = $request->filled('sub_category_id') ? $request->sub_category_id : null;
            $data['sub_sub_category_id'] = $request->filled('sub_sub_category_id') ? $request->sub_sub_category_id : null;

            if ($request->filled('sizes')) {
                $prices = collect($request->sizes)->pluck('price')->filter()->map(fn ($p) => (float) $p);
                if ($prices->isNotEmpty()) {
                    $data['price'] = $prices->min();
                }
            } elseif ($request->filled('variants')) {
                $prices = collect($request->variants)->pluck('price')->filter()->map(fn ($p) => (float) $p);
                if ($prices->isNotEmpty()) {
                    $data['price'] = $prices->min();
                }
            }
            $data['price'] = $data['price'] ?? $product->price;

            if ($request->hasFile('image')) {
                if ($product->image && !str_starts_with($product->image, 'http') && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = CloudinaryService::uploadOrStore($request->file('image'), 'products');
            } elseif ($request->filled('image_url')) {
                if ($product->image && !str_starts_with($product->image, 'http') && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = trim($request->input('image_url'));
            } elseif ($request->boolean('delete_main_image') || $request->input('delete_main_image') == '1') {
                if ($product->image && !str_starts_with($product->image, 'http') && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = null;
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
                        if (Storage::disk('public')->exists($img->path)) {
                            Storage::disk('public')->delete($img->path);
                        }
                        $img->delete();
                    }
                }
            }

            $this->syncVariants($product, $request);

            DB::commit();
            $product->refresh();
            $product->load(['category', 'variants', 'images']);

            return redirect()
                ->route('admin.products.show', $product->id)
                ->with('success', 'Sản phẩm đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $product = Product::with('images')->findOrFail($id);

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        foreach ($product->images as $img) {
            if (Storage::disk('public')->exists($img->path)) {
                Storage::disk('public')->delete($img->path);
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')
                         ->with('success', 'Sản phẩm đã được xóa thành công.');
    }

    /**
     * Lưu biến thể từ form sizes[] (mỗi size nhiều màu) hoặc variants[] cũ.
     */
    protected function syncVariants(Product $product, Request $request): void
    {
        $sizes = $request->input('sizes', []);
        $variants = $request->input('variants', []);
        $hasSizes = is_array($sizes) && count($sizes) > 0;
        $hasVariants = is_array($variants) && count($variants) > 0;

        // Không có dữ liệu size/biến thể trên form → giữ nguyên variants cũ
        if (!$hasSizes && !$hasVariants) {
            return;
        }

        $product->variants()->delete();

        $minPrice = null;
        $allColorNames = [];

        // Form mới: sizes[i][colors][] + color_stocks[name]
        if ($hasSizes) {
            foreach ($sizes as $size) {
                if (!is_array($size)) {
                    continue;
                }

                $price = isset($size['price']) && $size['price'] !== '' ? (float) $size['price'] : null;
                $priceOld = isset($size['price_old']) && $size['price_old'] !== '' ? (float) $size['price_old'] : null;

                $stocks = is_array($size['color_stocks'] ?? null) ? $size['color_stocks'] : [];
                $colors = is_array($size['colors'] ?? null) ? $size['colors'] : [];

                // Gộp màu từ colors[] và từ key của color_stocks (tránh mất màu mới chọn)
                $colorMap = []; // name => stock
                foreach ($colors as $colorName) {
                    $colorName = trim(html_entity_decode((string) $colorName, ENT_QUOTES, 'UTF-8'));
                    if ($colorName === '') {
                        continue;
                    }
                    $stock = 0;
                    if (array_key_exists($colorName, $stocks)) {
                        $stock = (int) $stocks[$colorName];
                    } else {
                        // thử khớp không phân biệt hoa thường / khoảng trắng thừa
                        foreach ($stocks as $k => $v) {
                            if (mb_strtolower(trim((string) $k)) === mb_strtolower($colorName)) {
                                $stock = (int) $v;
                                break;
                            }
                        }
                    }
                    $colorMap[$colorName] = $stock;
                }
                foreach ($stocks as $k => $v) {
                    $colorName = trim(html_entity_decode((string) $k, ENT_QUOTES, 'UTF-8'));
                    if ($colorName === '' || isset($colorMap[$colorName])) {
                        continue;
                    }
                    $colorMap[$colorName] = (int) $v;
                }

                $base = [
                    'size_label'    => $size['size_label'] ?? null,
                    'width'         => $size['width'] !== '' && isset($size['width']) ? $size['width'] : null,
                    'depth'         => $size['depth'] !== '' && isset($size['depth']) ? $size['depth'] : null,
                    'height'        => $size['height'] !== '' && isset($size['height']) ? $size['height'] : null,
                    'desktop_width' => $size['desktop_width'] !== '' && isset($size['desktop_width']) ? $size['desktop_width'] : null,
                    'price'         => $price ?? 0,
                    'price_old'     => $priceOld,
                ];

                if (empty($colorMap)) {
                    // Size không chọn màu → vẫn lưu 1 dòng
                    if ($price === null && empty($size['size_label']) && empty($size['width'])) {
                        continue;
                    }
                    $product->variants()->create(array_merge($base, [
                        'color' => null,
                        'stock' => 0,
                    ]));
                } else {
                    foreach ($colorMap as $colorName => $stock) {
                        $product->variants()->create(array_merge($base, [
                            'color' => $colorName,
                            'stock' => $stock,
                        ]));
                        $allColorNames[] = $colorName;
                    }
                }

                if ($price !== null) {
                    $minPrice = $minPrice === null ? $price : min($minPrice, $price);
                }
            }

            $update = [];
            if ($minPrice !== null) {
                $update['price'] = $minPrice;
            }
            $update['color'] = $allColorNames
                ? implode(', ', array_values(array_unique($allColorNames)))
                : null;
            if ($update) {
                $product->update($update);
            }
            return;
        }

        // Form cũ: variants[]
        foreach ($variants as $v) {
            if (!is_array($v)) {
                continue;
            }
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
            if (!empty($v['color'])) {
                $allColorNames[] = trim((string) $v['color']);
            }
        }

        $product->update([
            'color' => $allColorNames
                ? implode(', ', array_values(array_unique($allColorNames)))
                : null,
        ]);
    }
}
