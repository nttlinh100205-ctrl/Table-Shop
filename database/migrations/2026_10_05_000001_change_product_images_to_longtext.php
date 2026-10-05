<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cho phép lưu trữ URL dài hoặc Base64 Data URI trực tiếp vào database,
     * giúp ảnh không bao giờ bị mất trên Render ngay cả khi không dùng Cloudinary hoặc Git.
     */
    public function up()
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'image')) {
            DB::statement("ALTER TABLE products MODIFY image LONGTEXT NULL");
        }

        if (Schema::hasTable('product_images') && Schema::hasColumn('product_images', 'path')) {
            DB::statement("ALTER TABLE product_images MODIFY path LONGTEXT NULL");
        }

        if (Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'image')) {
            DB::statement("ALTER TABLE product_variants MODIFY image LONGTEXT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'image')) {
            DB::statement("ALTER TABLE products MODIFY image VARCHAR(1000) NULL");
        }

        if (Schema::hasTable('product_images') && Schema::hasColumn('product_images', 'path')) {
            DB::statement("ALTER TABLE product_images MODIFY path VARCHAR(1000) NULL");
        }

        if (Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'image')) {
            DB::statement("ALTER TABLE product_variants MODIFY image VARCHAR(255) NULL");
        }
    }
};
