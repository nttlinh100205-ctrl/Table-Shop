<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('referral_rewarded_at')->nullable();
        });
        Schema::table('spin_histories', function (Blueprint $table) {
            $table->uuid('request_id')->nullable();
            $table->unique(['user_id', 'request_id']);
        });
    }
    public function down(): void
    {
        Schema::table('spin_histories', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'request_id']);
            $table->dropColumn('request_id');
        });
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('referral_rewarded_at'));
    }
};
