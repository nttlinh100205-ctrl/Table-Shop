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
        // 1. Thêm avatar và trường điểm vào users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->text('avatar')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'points_balance')) {
                $table->unsignedBigInteger('points_balance')->default(0)->after('role');
            }
            if (!Schema::hasColumn('users', 'lifetime_points')) {
                $table->unsignedBigInteger('lifetime_points')->default(0)->after('points_balance');
            }
        });

        // 2. Thêm user_id cho promotions (voucher gắn riêng theo user)
        Schema::table('promotions', function (Blueprint $table) {
            if (!Schema::hasColumn('promotions', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            }
        });

        // 3. Tạo bảng point_transactions (lịch sử điểm, FIFO hạn 1 năm)
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->enum('type', ['earn', 'redeem', 'refund', 'expire']);
            $table->bigInteger('points'); // Dương (+) khi nhận, Âm (-) khi đổi voucher hoặc hết hạn/hoàn trả
            $table->unsignedBigInteger('points_used')->default(0); // Số điểm của lô này đã bị cấn trừ (dùng theo FIFO)
            $table->timestamp('expires_at')->nullable(); // Hạn 1 năm cho lô 'earn'
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'type']);
            $table->index(['type', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');

        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('users', 'avatar')) $cols[] = 'avatar';
            if (Schema::hasColumn('users', 'points_balance')) $cols[] = 'points_balance';
            if (Schema::hasColumn('users', 'lifetime_points')) $cols[] = 'lifetime_points';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
