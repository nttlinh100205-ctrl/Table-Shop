<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
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

        return view('user.cart.index', compact('cart', 'total'));
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
        session()->forget('cart');
        return redirect()->route('user.cart.index')->with('success', 'Đã xóa toàn bộ giỏ hàng.');
    }
}
