<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sub_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_category_id')
                  ->constrained('sub_categories')
                  ->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            // Tối ưu query: lấy tất cả cấp 3 của 1 danh mục cấp 2
            $table->index(['sub_category_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_sub_categories');
    }
};
