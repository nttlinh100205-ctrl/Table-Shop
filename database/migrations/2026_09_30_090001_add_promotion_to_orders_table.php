<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('promotion_id')
                ->nullable()
                ->after('user_id')
                ->constrained('promotions')
                ->nullOnDelete();
            $table->string('coupon_code', 50)->nullable()->after('promotion_id');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('coupon_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn(['promotion_id', 'coupon_code', 'discount_amount']);
        });
    }
};
