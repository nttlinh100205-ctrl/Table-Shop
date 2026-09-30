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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percent', 'fixed'])->default('fixed'); // percent (%) hoặc fixed (số tiền)
            $table->decimal('discount_value', 12, 2); // Giá trị giảm (% hoặc VNĐ)
            $table->decimal('max_discount_amount', 12, 2)->nullable(); // Số tiền giảm tối đa (khi giảm theo %)
            $table->decimal('min_order_amount', 12, 2)->default(0); // Giá trị đơn hàng tối thiểu
            $table->unsignedInteger('usage_limit')->nullable(); // Số lượt dùng tối đa (null = không giới hạn)
            $table->unsignedInteger('used_count')->default(0); // Số lượt đã dùng
            $table->dateTime('start_date')->nullable(); // Ngày bắt đầu hiệu lực
            $table->dateTime('end_date')->nullable(); // Ngày hết hạn (HSD)
            $table->boolean('is_active')->default(true); // Trạng thái kích hoạt
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
