<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Bảng màu dùng chung (chỉ xem + sửa).
     */
    public function index(Request $request)
    {
        $query = Color::query()->orderBy('group')->orderBy('sort_order')->orderBy('name');

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $colors = $query->get()->groupBy('group');
        $groups = Color::GROUPS;

        return view('admin.colors.index', compact('colors', 'groups'));
    }

    public function edit(Color $color)
    {
        $groups = Color::GROUPS;
        return view('admin.colors.edit', compact('color', 'groups'));
    }

    public function update(Request $request, Color $color)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'code'       => 'nullable|string|max:20|unique:colors,code,' . $color->id,
            'hex'        => ['nullable', 'string', 'max:7', 'regex:/^#?[0-9A-Fa-f]{3,6}$/'],
            'group'      => 'required|string|in:' . implode(',', array_keys(Color::GROUPS)),
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ], [
            'name.required' => 'Vui lòng nhập tên màu.',
            'code.unique'   => 'Mã màu đã tồn tại.',
            'hex.regex'     => 'Mã hex không hợp lệ (vd: #C4A574).',
        ]);

        if (!empty($data['hex']) && $data['hex'][0] !== '#') {
            $data['hex'] = '#' . $data['hex'];
        }
        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $color->update($data);

        return redirect()->route('admin.colors.index')
            ->with('success', 'Đã cập nhật màu.');
    }
}
