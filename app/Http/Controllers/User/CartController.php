<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Hiển thị giỏ hàng (session).
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = 0;

        // Bổ sung size/màu nếu session cũ thiếu (từ variant_id)
        $needSave = false;
        foreach ($cart as $key => &$item) {
            if (empty($item['size_label']) && empty($item['color']) && !empty($item['variant_id'])) {
                $variant = \App\Models\ProductVariant::find($item['variant_id']);
                if ($variant) {
                    $item['size_label'] = $variant->size_button_label
                        ?: ($variant->size_label ?: $variant->dimensions);
                    $item['color'] = $variant->color;
                    $needSave = true;
                }
            }
            // Tên sản phẩm sạch (bỏ đuôi — size/màu nếu đã tách field)
            if (!empty($item['size_label']) || !empty($item['color'])) {
                if (isset($item['name']) && str_contains($item['name'], ' — ')) {
                    $item['name'] = trim(explode(' — ', $item['name'], 2)[0]);
                    $needSave = true;
                }
            }
            $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
        }
        unset($item);

        if ($needSave) {
            session()->put('cart', $cart);
            session()->save();
        }

        // Lấy danh sách khuyến mãi để hiển thị cho khách hàng xem ngay tại giỏ hàng (mã chung + mã riêng)
        $allPromotions = Promotion::active()->notExpired()->forUser(auth()->id())->orderBy('min_order_amount', 'asc')->get();
        $availablePromotions = collect();
        $ineligiblePromotions = collect();

        foreach ($allPromotions as $promo) {
            $info = $promo->getEligibilityInfo($total);
            $promo->is_eligible = $info['is_eligible'];
            $promo->ineligible_reason = $info['reason'];
            $promo->need_more_amount = $info['need_more'];
            $promo->calculated_discount = $info['discount_amount'];

            if ($promo->is_eligible) {
                $availablePromotions->push($promo);
            } else {
                $ineligiblePromotions->push($promo);
            }
        }
        $availablePromotions = $availablePromotions->sortByDesc('calculated_discount')->values();
        $coupon = session('coupon');

        return view('user.cart.index', compact('cart', 'total', 'availablePromotions', 'ineligiblePromotions', 'coupon'));
    }

    /**
     * Thêm sản phẩm vào giỏ (session).
     * Hỗ trợ variant_id (size + màu).
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer',
            'quantity'   => 'nullable|integer|min:1|max:99',
        ]);

        $product = Product::with(['images', 'variants'])->findOrFail($validated['product_id']);
        $quantity = (int) ($validated['quantity'] ?? 1);
        $variantId = isset($validated['variant_id']) ? (int) $validated['variant_id'] : null;

        $variant = null;
        if ($variantId) {
            $variant = $product->variants->firstWhere('id', $variantId);
            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biến thể không hợp lệ.',
                ], 422);
            }
        }

        // Giá: variant > product.price > min variant
        $price = (float) ($product->price ?? 0);
        if ($variant) {
            $price = (float) ($variant->price ?? 0);
        } elseif ($price <= 0 && $product->variants->isNotEmpty()) {
            $price = (float) ($product->variants->min('price') ?? 0);
        }

        $name = $product->name;
        $sizeLabel = null;
        $color = null;
        if ($variant) {
            $sizeLabel = $variant->size_button_label
                ?: ($variant->size_label ?: $variant->dimensions);
            $color = $variant->color;
        }

        $image = $product->image;
        if (!$image && $product->images && $product->images->count() > 0) {
            $image = $product->images->first()->path ?? null;
        }

        // Key riêng theo product + variant để không gộp nhầm size/màu
        $cartKey = $variantId ? ($product->id . '_v' . $variantId) : (string) $product->id;
        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
            // Cập nhật lại thông số (phòng khi session cũ thiếu field)
            $cart[$cartKey]['size_label'] = $sizeLabel;
            $cart[$cartKey]['color'] = $color;
            $cart[$cartKey]['name'] = $name;
            $cart[$cartKey]['price'] = $price;
        } else {
            $cart[$cartKey] = [
                'id'         => (int) $product->id,
                'variant_id' => $variantId,
                'name'       => $name,
                'size_label' => $sizeLabel,
                'color'      => $color,
                'price'      => $price,
                'quantity'   => $quantity,
                'image'      => $image,
            ];
        }

        session()->put('cart', $cart);
        session()->save();

        $cartCount = collect($cart)->sum('quantity');

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Thêm giỏ hàng thành công.',
                'cart_count' => $cartCount,
            ]);
        }

        return back()->with('success', 'Đã thêm "' . $name . '" vào giỏ hàng!');
    }

    /**
     * Cập nhật số lượng.
     */
    public function update(Request $request)
    {
        $request->validate([
            'cart_key'   => 'nullable|string',
            'product_id' => 'nullable',
            'quantity'   => 'required|integer|min:0|max:99',
        ]);

        $cart = session()->get('cart', []);
        // Ưu tiên cart_key (vd: 5_v12), fallback product_id
        $key = $request->input('cart_key') ?: (string) $request->input('product_id');

        if ($request->quantity <= 0) {
            unset($cart[$key]);
        } elseif (isset($cart[$key])) {
            $cart[$key]['quantity'] = (int) $request->quantity;
        }

        session()->put('cart', $cart);
        session()->save();

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            $total = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
            $cartCount = collect($cart)->sum('quantity');

            return response()->json([
                'success'    => true,
                'total'      => $total,
                'cart_count' => $cartCount,
            ]);
        }

        return back()->with('success', 'Đã cập nhật giỏ hàng.');
    }

    /**
     * Xóa 1 sản phẩm khỏi giỏ.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'cart_key'   => 'nullable|string',
            'product_id' => 'nullable',
        ]);

        $cart = session()->get('cart', []);
        $key = $request->input('cart_key') ?: (string) $request->input('product_id');
        unset($cart[$key]);
        session()->put('cart', $cart);
        session()->save();

        $cartCount = collect($cart)->sum('quantity');

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'cart_count' => $cartCount,
            ]);
        }

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    /**
     * Xóa toàn bộ giỏ.
     */
    public function clear()
    {
        session()->forget(['cart', 'coupon']);
        return redirect()->route('user.cart.index')->with('success', 'Đã xóa toàn bộ giỏ hàng.');
    }

    /**
     * Áp dụng mã khuyến mãi vào giỏ hàng / phiên thanh toán.
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ], [
            'code.required' => 'Vui lòng nhập mã khuyến mãi.',
        ]);

        $code = strtoupper(trim($request->code));
        $promotion = Promotion::where('code', $code)->first();

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Mã khuyến mãi "' . $code . '" không tồn tại hoặc đã bị xóa.',
            ], 422);
        }

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng đang trống, không thể áp dụng mã giảm giá.',
            ], 422);
        }

        $subtotal = (float) collect($cart)->sum(fn ($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 0));

        $errorMsg = null;
        if (!$promotion->isValid($subtotal, $errorMsg, auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => $errorMsg,
            ], 422);
        }

        $discount = $promotion->calculateDiscount($subtotal);
        $newTotal = max(0, $subtotal - $discount);

        session()->put('coupon', [
            'id'                  => $promotion->id,
            'code'                => $promotion->code,
            'name'                => $promotion->name,
            'discount_type'       => $promotion->discount_type,
            'discount_value'      => (float) $promotion->discount_value,
            'max_discount_amount' => $promotion->max_discount_amount ? (float) $promotion->max_discount_amount : null,
            'discount_amount'     => $discount,
        ]);
        session()->save();

        return response()->json([
            'success'         => true,
            'message'         => 'Áp dụng mã "' . $promotion->code . '" thành công! Bạn được giảm ' . number_format($discount, 0, ',', '.') . 'đ.',
            'code'            => $promotion->code,
            'discount_amount' => $discount,
            'discount_text'   => number_format($discount, 0, ',', '.') . 'đ',
            'subtotal'        => $subtotal,
            'new_total'       => $newTotal,
            'new_total_text'  => number_format($newTotal, 0, ',', '.') . 'đ',
        ]);
    }

    /**
     * Hủy áp dụng mã khuyến mãi.
     */
    public function removeCoupon(Request $request)
    {
        session()->forget('coupon');
        session()->save();

        $cart = session()->get('cart', []);
        $subtotal = (float) collect($cart)->sum(fn ($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 0));

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success'        => true,
                'message'        => 'Đã hủy áp dụng mã giảm giá.',
                'subtotal'       => $subtotal,
                'new_total'      => $subtotal,
                'new_total_text' => number_format($subtotal, 0, ',', '.') . 'đ',
            ]);
        }

        return back()->with('success', 'Đã hủy mã giảm giá.');
    }

    /**
     * Lấy danh sách khuyến mãi (API cho giỏ hàng / thanh toán).
     */
    public function promotions(Request $request)
    {
        $cart = session()->get('cart', []);
        $subtotal = $request->filled('subtotal')
            ? (float) $request->input('subtotal')
            : (float) collect($cart)->sum(fn ($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 0));

        $allPromotions = Promotion::active()
            ->notExpired()
            ->forUser(auth()->id())
            ->orderBy('min_order_amount', 'asc')
            ->get();

        $available = [];
        $ineligible = [];
        $currentCouponCode = session('coupon.code');

        foreach ($allPromotions as $promo) {
            $info = $promo->getEligibilityInfo($subtotal);
            $item = [
                'id'                  => $promo->id,
                'code'                => $promo->code,
                'name'                => $promo->name,
                'description'         => $promo->description,
                'discount_type'       => $promo->discount_type,
                'discount_value'      => (float) $promo->discount_value,
                'discount_display'    => $promo->discount_display,
                'max_discount_amount' => $promo->max_discount_amount ? (float) $promo->max_discount_amount : null,
                'min_order_amount'    => (float) $promo->min_order_amount,
                'min_order_text'      => number_format($promo->min_order_amount, 0, ',', '.') . 'đ',
                'end_date'            => $promo->end_date ? $promo->end_date->format('d/m/Y') : null,
                'remaining_uses'      => $promo->remaining_uses,
                'is_current'          => ($currentCouponCode === $promo->code),
                'is_eligible'         => $info['is_eligible'],
                'reason'              => $info['reason'],
                'need_more'           => $info['need_more'],
                'need_more_text'      => number_format($info['need_more'], 0, ',', '.') . 'đ',
                'discount_amount'     => $info['discount_amount'],
                'discount_text'       => number_format($info['discount_amount'], 0, ',', '.') . 'đ',
            ];

            if ($info['is_eligible']) {
                $available[] = $item;
            } else {
                $ineligible[] = $item;
            }
        }

        // Sắp xếp mã khả dụng theo mức giảm giá cao nhất lên đầu (Đề xuất tốt nhất)
        usort($available, fn ($a, $b) => $b['discount_amount'] <=> $a['discount_amount']);

        return response()->json([
            'success'        => true,
            'subtotal'       => $subtotal,
            'subtotal_text'  => number_format($subtotal, 0, ',', '.') . 'đ',
            'available'      => $available,
            'ineligible'     => $ineligible,
            'current_coupon' => $currentCouponCode,
        ]);
    }
}
