<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Cấp 2 (nullable)
            $table->foreignId('sub_category_id')
                  ->nullable()
                  ->after('category_id')
                  ->constrained('sub_categories')
                  ->nullOnDelete();

            // Cấp 3 (nullable)
            $table->foreignId('sub_sub_category_id')
                  ->nullable()
                  ->after('sub_category_id')
                  ->constrained('sub_sub_categories')
                  ->nullOnDelete();

            // Chỉ mục hỗ trợ lọc sản phẩm
            $table->index('name');
            $table->index('sku');
            $table->index('price');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['sub_category_id']);
            $table->dropForeign(['sub_sub_category_id']);
            $table->dropColumn(['sub_category_id', 'sub_sub_category_id']);

            $table->dropIndex(['name']);
            $table->dropIndex(['sku']);
            $table->dropIndex(['price']);
            $table->dropIndex(['created_at']);
        });
    }
};
