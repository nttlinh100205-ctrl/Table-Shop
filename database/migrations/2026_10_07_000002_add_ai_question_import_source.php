<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('ai_question_events', function (Blueprint $table) {
            $table->string('source', 20)->default('live');
            $table->string('source_key', 64)->nullable()->unique();
        });
    }
    public function down(): void
    {
        Schema::table('ai_question_events', function (Blueprint $table) {
            $table->dropUnique(['source_key']);
            $table->dropColumn(['source','source_key']);
        });
    }
};
