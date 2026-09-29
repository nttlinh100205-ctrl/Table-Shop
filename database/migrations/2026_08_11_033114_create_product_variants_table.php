<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('sku')->nullable()->comment('Mã biến thể');
            $table->string('size_label')->nullable()->comment('Nhãn size, VD: 120x60');
            $table->decimal('width', 8, 2)->nullable()->comment('Chiều dài (cm)');
            $table->decimal('depth', 8, 2)->nullable()->comment('Chiều sâu (cm)');
            $table->decimal('height', 8, 2)->nullable()->comment('Chiều cao (cm)');
            $table->string('color')->nullable()->comment('Màu sắc');
            $table->decimal('price', 12, 0)->default(0)->comment('Giá theo size + màu');
            $table->decimal('price_old', 12, 0)->nullable();
            $table->integer('stock')->default(0)->comment('Tồn kho');
            $table->string('image')->nullable()->comment('Ảnh riêng của biến thể');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variants');
    }
};
