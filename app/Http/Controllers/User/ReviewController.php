<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    /**
     * Gửi đánh giá cho sản phẩm thuộc đơn hàng hoàn thành
     */
    public function store(Request $request, Order $order)
    {
        // 1. Chỉ đúng chủ đơn mới được đánh giá
        abort_unless($order->user_id === Auth::id(), 403, 'Bạn không có quyền đánh giá đơn hàng này.');

        // 2. Chỉ đơn có trạng thái hoàn thành (completed) mới được đánh giá
        if ($order->status !== 'completed') {
            return back()->with('error', 'Chỉ những đơn hàng đã giao thành công và hoàn thành mới có thể gửi đánh giá.');
        }

        // 3. Validate dữ liệu đầu vào
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('order_items', 'product_id')->where('order_id', $order->id),
            ],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'comment'    => ['required', 'string', 'min:5', 'max:2000'],
            'images'     => ['nullable', 'array', 'max:5'], // Tối đa 5 ảnh
            'images.*'   => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'], // 5MB
        ], [
            'product_id.required' => 'Vui lòng chọn sản phẩm cần đánh giá.',
            'product_id.exists'   => 'Sản phẩm không thuộc đơn hàng này.',
            'rating.required'     => 'Vui lòng chọn số sao đánh giá (1 - 5 sao).',
            'rating.min'          => 'Số sao tối thiểu là 1.',
            'rating.max'          => 'Số sao tối đa là 5.',
            'comment.required'    => 'Vui lòng nhập nội dung chia sẻ trải nghiệm.',
            'comment.min'         => 'Nội dung đánh giá cần có ít nhất 5 ký tự.',
            'images.max'          => 'Chỉ được tải lên tối đa 5 hình ảnh thực tế.',
            'images.*.image'      => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'images.*.max'        => 'Dung lượng mỗi ảnh tối đa là 5MB.',
        ]);

        $productId = (int) $validated['product_id'];

        // 4. Kiểm tra mỗi đơn/sản phẩm chỉ được đánh giá 1 lần duy nhất
        $alreadyReviewed = Review::where('order_id', $order->id)
            ->where('product_id', $productId)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'Sản phẩm này trong đơn hàng đã được bạn gửi đánh giá trước đó.');
        }

        // 5. Upload ảnh lên Cloudinary
        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $url = CloudinaryService::uploadOrStore($file, 'reviews');
                    if ($url) {
                        $uploadedImages[] = $url;
                    }
                }
            }
        }

        // 6. Lưu vào cơ sở dữ liệu
        $createdReview = null;
        $awarded = \Illuminate\Support\Facades\DB::transaction(function () use ($order, $productId, $validated, $uploadedImages, &$createdReview) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status !== 'completed' || Review::where('order_id', $order->id)->where('product_id', $productId)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['review' => 'Đơn chưa hoàn thành hoặc sản phẩm đã được đánh giá.']);
            }
            $createdReview = Review::create([
                'ai_reply_status' => 'queued',
                'order_id'   => $order->id,
                'product_id' => $productId,
                'user_id'    => Auth::id(),
                'rating'     => $validated['rating'],
                'comment'    => trim($validated['comment']),
                'images'     => !empty($uploadedImages) ? $uploadedImages : null,
            ]);

            $user = \App\Models\User::whereKey($lockedOrder->user_id)->lockForUpdate()->firstOrFail();
            if (\Illuminate\Support\Facades\DB::table('coin_transactions')->where('order_id', $order->id)->where('type', 'review')->exists()) return false;
            $user->increment('coin_balance', 200);
            \Illuminate\Support\Facades\DB::table('coin_transactions')->insert([
                'user_id'=>$user->id,'order_id'=>$order->id,'type'=>'review','amount'=>200,
                'description'=>'Thưởng đánh giá đơn #'.$order->id,'created_at'=>now(),'updated_at'=>now(),
            ]);
            return true;
        }, 3);
        try {
            \App\Jobs\ReplyToReview::dispatch($createdReview->id);
        } catch (\Throwable $e) {
            $createdReview->update(['ai_reply_status'=>'failed']);
            \Illuminate\Support\Facades\Log::warning('Unable to enqueue review reply', ['review_id'=>$createdReview->id]);
        }
        return back()->with('success', 'Gửi đánh giá thành công!'.($awarded ? ' Bạn được cộng 200 xu.' : ' Đơn này đã nhận thưởng đánh giá.'));
    }
}
