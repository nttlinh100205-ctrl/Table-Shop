<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('shipping_status');
            $table->string('cancel_previous_status')->nullable()->after('cancel_reason');
            $table->text('cancel_admin_note')->nullable()->after('cancel_previous_status');
            $table->timestamp('cancel_requested_at')->nullable()->after('cancel_admin_note');
            $table->timestamp('cancel_processed_at')->nullable()->after('cancel_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'cancel_reason',
                'cancel_previous_status',
                'cancel_admin_note',
                'cancel_requested_at',
                'cancel_processed_at',
            ]);
        });
    }
};