<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Biến thể (nếu có)
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->string('color')->nullable();
            $table->string('size_label')->nullable();

            $table->integer('quantity');
            $table->decimal('price', 15, 2)->comment('Giá tại thời điểm mua');

            $table->timestamps();

            $table->foreign('product_variant_id')
                ->references('id')
                ->on('product_variants')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
