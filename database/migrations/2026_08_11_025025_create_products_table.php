<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('sku')->nullable()->comment('Mã sản phẩm');
            $table->string('name');
            $table->decimal('price', 12, 0)->default(0);
            $table->decimal('price_old', 12, 0)->nullable()->comment('Giá cũ (nếu có giảm giá)');
            $table->text('description')->nullable();
            $table->string('image')->nullable()->comment('Ảnh chính');
            $table->string('material')->nullable()->comment('Chất liệu');
            $table->string('style')->nullable()->comment('Phong cách / Kiểu dáng');
            $table->string('color')->nullable()->comment('Màu sắc');
            $table->string('warranty')->nullable()->comment('Bảo hành');
            $table->decimal('width', 8, 2)->nullable()->comment('Chiều rộng / Dài (cm)');
            $table->decimal('depth', 8, 2)->nullable()->comment('Chiều sâu / Rộng (cm)');
            $table->decimal('height', 8, 2)->nullable()->comment('Chiều cao (cm)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
