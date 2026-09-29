<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // VD: Vân gỗ sồi
            $table->string('code', 20)->nullable();          // Mã nội bộ: VG-SOI, ST-TRANG...
            $table->string('hex', 7)->nullable();            // #C4A574 — hiển thị chip
            $table->string('group')->default('khac');        // van_go | son_tinh_dien | son_van_bua | vai | khac
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['group', 'is_active']);
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('colors');
    }
};
