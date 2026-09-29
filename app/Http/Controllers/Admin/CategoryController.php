<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $tree = Category::with(['subCategories.subSubCategories'])->orderBy('name')->get();

        return view('admin.categories.index', compact('tree'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                           => 'required|string|max:255',
            'description'                    => 'nullable|string',
            'children'                       => 'nullable|array',
            'children.*.name'                => 'nullable|string|max:255',
            'children.*.description'         => 'nullable|string',
            'children.*.children'            => 'nullable|array',
            'children.*.children.*.name'     => 'nullable|string|max:255',
            'children.*.children.*.description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Cấp 1
            $category = Category::create([
                'name'        => $request->name,
                'description' => $request->description,
            ]);

            // Cấp 2 + Cấp 3
            if ($request->filled('children')) {
                foreach ($request->children as $childData) {
                    $childName = trim($childData['name'] ?? '');
                    if ($childName === '') {
                        continue;
                    }

                    $sub = SubCategory::create([
                        'category_id' => $category->id,
                        'name'        => $childName,
                        'description' => $childData['description'] ?? null,
                    ]);

                    if (!empty($childData['children']) && is_array($childData['children'])) {
                        foreach ($childData['children'] as $grandData) {
                            $grandName = trim($grandData['name'] ?? '');
                            if ($grandName === '') {
                                continue;
                            }
                            SubSubCategory::create([
                                'sub_category_id' => $sub->id,
                                'name'            => $grandName,
                                'description'     => $grandData['description'] ?? null,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.categories.index')
                             ->with('success', 'Danh mục (và danh mục con) đã được tạo thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $category = Category::with([
            'subCategories.subSubCategories',
            'products',
        ])->findOrFail($id);

        return view('admin.categories.show', compact('category'));
    }

    public function edit($id)
    {
        $category = Category::with(['subCategories.subSubCategories'])->findOrFail($id);

        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Cập nhật danh mục cấp 1 + thêm/sửa/xóa danh mục cấp 2, cấp 3.
     * Mục bị bỏ khỏi form (nút Xóa) sẽ bị xóa khỏi DB.
     * Sản phẩm gắn với mục bị xóa → sub_category_id / sub_sub_category_id = null (nullOnDelete).
     */
    public function update(Request $request, $id)
    {
        $category = Category::with(['subCategories.subSubCategories'])->findOrFail($id);

        $request->validate([
            'name'                              => 'required|string|max:255',
            'description'                       => 'nullable|string',
            'children'                          => 'nullable|array',
            'children.*.id'                     => 'nullable|integer|exists:sub_categories,id',
            'children.*.name'                   => 'nullable|string|max:255',
            'children.*.description'            => 'nullable|string',
            'children.*.children'               => 'nullable|array',
            'children.*.children.*.id'          => 'nullable|integer|exists:sub_sub_categories,id',
            'children.*.children.*.name'        => 'nullable|string|max:255',
            'children.*.children.*.description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $category->update([
                'name'        => $request->name,
                'description' => $request->description,
            ]);

            $submittedSubIds = [];
            $children = $request->input('children', []) ?: [];

            foreach ($children as $childData) {
                $childName = trim($childData['name'] ?? '');
                if ($childName === '') {
                    continue;
                }

                // Cấp 2: cập nhật hoặc tạo mới
                if (!empty($childData['id'])) {
                    $sub = SubCategory::where('id', $childData['id'])
                        ->where('category_id', $category->id)
                        ->first();
                    if (!$sub) {
                        continue;
                    }
                    $sub->update([
                        'name'        => $childName,
                        'description' => $childData['description'] ?? $sub->description,
                    ]);
                } else {
                    $sub = SubCategory::create([
                        'category_id' => $category->id,
                        'name'        => $childName,
                        'description' => $childData['description'] ?? null,
                    ]);
                }

                $submittedSubIds[] = $sub->id;

                // Cấp 3: cập nhật / tạo mới + xóa mục không còn trên form
                $submittedGrandIds = [];
                $grands = $childData['children'] ?? [];
                if (is_array($grands)) {
                    foreach ($grands as $grandData) {
                        $grandName = trim($grandData['name'] ?? '');
                        if ($grandName === '') {
                            continue;
                        }

                        if (!empty($grandData['id'])) {
                            $grand = SubSubCategory::where('id', $grandData['id'])
                                ->where('sub_category_id', $sub->id)
                                ->first();
                            if ($grand) {
                                $grand->update([
                                    'name'        => $grandName,
                                    'description' => $grandData['description'] ?? $grand->description,
                                ]);
                                $submittedGrandIds[] = $grand->id;
                            }
                        } else {
                            $grand = SubSubCategory::create([
                                'sub_category_id' => $sub->id,
                                'name'            => $grandName,
                                'description'     => $grandData['description'] ?? null,
                            ]);
                            $submittedGrandIds[] = $grand->id;
                        }
                    }
                }

                // Xóa cấp 3 không còn trong form (sản phẩm → nullOnDelete)
                SubSubCategory::where('sub_category_id', $sub->id)
                    ->when(!empty($submittedGrandIds), fn ($q) => $q->whereNotIn('id', $submittedGrandIds))
                    ->when(empty($submittedGrandIds), fn ($q) => $q)
                    ->delete();
            }

            // Xóa cấp 2 không còn trong form (cascade xóa cấp 3; sản phẩm → nullOnDelete)
            $subsToDelete = SubCategory::where('category_id', $category->id)
                ->when(!empty($submittedSubIds), fn ($q) => $q->whereNotIn('id', $submittedSubIds))
                ->get();

            foreach ($subsToDelete as $subDel) {
                // Xóa cấp 3 trước (rõ ràng), rồi xóa cấp 2
                SubSubCategory::where('sub_category_id', $subDel->id)->delete();
                $subDel->delete();
            }

            DB::commit();

            return redirect()->route('admin.categories.index')
                             ->with('success', 'Danh mục đã được cập nhật.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $category = Category::with(['subCategories.subSubCategories', 'products'])->findOrFail($id);

        // Đếm sản phẩm thuộc cấp 1 + cấp 2 + cấp 3
        $subIds = $category->subCategories->pluck('id')->all();
        $subSubIds = SubSubCategory::whereIn('sub_category_id', $subIds)->pluck('id')->all();

        $productCount = \App\Models\Product::query()
            ->where('category_id', $category->id)
            ->when(!empty($subIds), fn ($q) => $q->orWhereIn('sub_category_id', $subIds))
            ->when(!empty($subSubIds), fn ($q) => $q->orWhereIn('sub_sub_category_id', $subSubIds))
            ->count();

        if ($productCount > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', "Không thể xóa: còn {$productCount} sản phẩm thuộc danh mục này (hoặc danh mục con). Hãy chuyển/xóa sản phẩm trước.");
        }

       
        $category->delete();

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Danh mục và các danh mục con đã được xóa.');
    }
}
