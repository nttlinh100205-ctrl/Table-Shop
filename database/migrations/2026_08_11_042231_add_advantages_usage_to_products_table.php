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
    Schema::table('products', function (Blueprint $table) {
        $table->text('advantages')->nullable()->after('description');
        $table->text('usage_guide')->nullable()->after('advantages');
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn(['advantages', 'usage_guide']);
    });
}
};
