<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            // Bỏ quan hệ parent_id (chuyển sang bảng sub_categories)
            if (Schema::hasColumn('categories', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }

            // Chỉ mục hỗ trợ tìm / sắp xếp theo tên
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['name']);

            $table->foreignId('parent_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('categories')
                  ->nullOnDelete();
        });
    }
};
