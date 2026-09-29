<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('return_status')->nullable()->after('shipping_status')
                ->comment('null | requested | approved | rejected | completed');
            $table->text('return_reason')->nullable()->after('return_status');
            $table->text('return_admin_note')->nullable()->after('return_reason');
            $table->timestamp('return_requested_at')->nullable()->after('return_admin_note');
            $table->timestamp('return_processed_at')->nullable()->after('return_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'return_status',
                'return_reason',
                'return_admin_note',
                'return_requested_at',
                'return_processed_at',
            ]);
        });
    }
};
