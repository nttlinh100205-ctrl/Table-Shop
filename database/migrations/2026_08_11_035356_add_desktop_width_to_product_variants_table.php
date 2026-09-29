<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('desktop_width', 8, 2)->nullable()->after('height')
                  ->comment('Chiều rộng mặt bàn (cm)');
        });
    }

    public function down()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('desktop_width');
        });
    }
};
