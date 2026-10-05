<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 32)->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'spin_tickets')) {
                $table->unsignedInteger('spin_tickets')->default(3)->after('lifetime_points');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'referral_code_applied')) {
                $table->string('referral_code_applied', 32)->nullable()->after('coupon_code');
            }
        });

        // Tự động sinh mã giới thiệu duy nhất cho các user đã có trong hệ thống
        $users = \App\Models\User::whereNull('referral_code')->get();
        foreach ($users as $u) {
            $code = 'REF-' . strtoupper(Str::random(6));
            while (\App\Models\User::where('referral_code', $code)->exists()) {
                $code = 'REF-' . strtoupper(Str::random(6));
            }
            $u->update(['referral_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'referral_code_applied')) {
                $table->dropColumn('referral_code_applied');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('users', 'referral_code')) $cols[] = 'referral_code';
            if (Schema::hasColumn('users', 'spin_tickets')) $cols[] = 'spin_tickets';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
