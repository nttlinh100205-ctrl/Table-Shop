<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            // Tối ưu query: lấy tất cả cấp 2 của 1 danh mục cha
            $table->index(['category_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_categories');
    }
};
