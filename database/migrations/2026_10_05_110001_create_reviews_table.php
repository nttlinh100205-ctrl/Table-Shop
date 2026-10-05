<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->default(5); // 1 - 5 sao
            $table->text('comment'); // Nội dung trải nghiệm
            $table->json('images')->nullable(); // Mảng URL ảnh tải lên qua Cloudinary
            $table->timestamps();

            // Mỗi đơn hàng / sản phẩm chỉ đánh giá 1 lần duy nhất
            $table->unique(['order_id', 'product_id'], 'order_product_review_unique');
            $table->index(['product_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
