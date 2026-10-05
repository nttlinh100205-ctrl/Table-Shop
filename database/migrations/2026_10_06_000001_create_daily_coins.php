<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->unsignedBigInteger('coin_balance')->default(0));
        Schema::table('orders', function (Blueprint $t) {
            $t->unsignedBigInteger('coins_used')->default(0);
            $t->unsignedBigInteger('coin_discount_amount')->default(0);
            $t->timestamp('coins_refunded_at')->nullable();
        });
        Schema::create('daily_check_ins', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->date('checked_date');
            $t->unsignedTinyInteger('cycle_day');
            $t->unsignedInteger('coins');
            $t->timestamps();
            $t->unique(['user_id', 'checked_date']);
        });
        Schema::create('coin_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('daily_check_in_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type', 20);
            $t->bigInteger('amount');
            $t->string('description');
            $t->timestamps();
            $t->unique(['order_id', 'type']);
            $t->unique('daily_check_in_id');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
        Schema::dropIfExists('daily_check_ins');
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn(['coins_used', 'coin_discount_amount', 'coins_refunded_at']));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('coin_balance'));
    }
};
