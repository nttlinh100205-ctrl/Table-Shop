<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('reply_source', 16)->nullable();
            $table->string('ai_reply_status', 16)->nullable();
        });
    }
    public function down(): void {
        Schema::table('reviews', fn(Blueprint $table) => $table->dropColumn(['reply_source', 'ai_reply_status']));
    }
};
