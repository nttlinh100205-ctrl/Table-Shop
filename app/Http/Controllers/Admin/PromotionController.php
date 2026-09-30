<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * Danh sách mã khuyến mãi.
     */
    public function index(Request $request)
    {
        $query = Promotion::withCount('orders')->latest();

        // Tìm kiếm theo mã hoặc tên
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                  ->orWhere('name', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo loại giảm giá
        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->discount_type);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'active') {
                $query->where('is_active', true)
                      ->where(function ($q) {
                          $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                      })
                      ->where(function ($q) {
                          $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
                      });
            } elseif ($status === 'expired') {
                $query->whereNotNull('end_date')->where('end_date', '<', now());
            } elseif ($status === 'exhausted') {
                $query->whereNotNull('usage_limit')->whereRaw('used_count >= usage_limit');
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $promotions = $query->paginate(12)->withQueryString();

        // Thống kê tổng quan
        $stats = [
            'total'     => Promotion::count(),
            'active'    => Promotion::where('is_active', true)
                            ->where(function ($q) {
                                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                            })
                            ->where(function ($q) {
                                $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
                            })->count(),
            'expired'   => Promotion::whereNotNull('end_date')->where('end_date', '<', now())->count(),
            'used_sum'  => Promotion::sum('used_count'),
        ];

        return view('admin.promotions.index', compact('promotions', 'stats'));
    }

    /**
     * Giao diện tạo mới mã khuyến mãi.
     */
    public function create()
    {
        return view('admin.promotions.create');
    }

    /**
     * Lưu mã khuyến mãi mới vào database.
     */
    public function store(PromotionRequest $request)
    {
        $data = $request->validated();
        Promotion::create($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Đã thêm mã khuyến mãi "' . $data['code'] . '" thành công!');
    }

    /**
     * Giao diện chỉnh sửa mã khuyến mãi.
     */
    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.edit', compact('promotion'));
    }

    /**
     * Cập nhật mã khuyến mãi.
     */
    public function update(PromotionRequest $request, Promotion $promotion)
    {
        $data = $request->validated();
        $promotion->update($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Đã cập nhật mã khuyến mãi "' . $promotion->code . '" thành công!');
    }

    /**
     * Xóa mã khuyến mãi.
     */
    public function destroy(Promotion $promotion)
    {
        $code = $promotion->code;
        $orderCount = $promotion->orders()->count();

        // Nếu đã có đơn hàng sử dụng mã này, tắt kích hoạt thay vì xóa cứng để bảo toàn dữ liệu báo cáo
        if ($orderCount > 0) {
            $promotion->update(['is_active' => false]);
            return redirect()->route('admin.promotions.index')
                ->with('success', "Mã \"{$code}\" đã có {$orderCount} đơn hàng sử dụng nên hệ thống đã chuyển sang trạng thái Tắt thay vì xóa dữ liệu.");
        }

        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Đã xóa mã khuyến mãi "' . $code . '" thành công!');
    }

    /**
     * Bật / tắt nhanh trạng thái kích hoạt của mã.
     */
    public function toggleStatus(Promotion $promotion)
    {
        $promotion->update([
            'is_active' => !$promotion->is_active,
        ]);

        $statusText = $promotion->is_active ? 'Kích hoạt' : 'Tạm tắt';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $promotion->is_active,
                'message'   => "Đã {$statusText} mã {$promotion->code}.",
            ]);
        }

        return back()->with('success', "Đã {$statusText} mã khuyến mãi {$promotion->code}!");
    }
}
