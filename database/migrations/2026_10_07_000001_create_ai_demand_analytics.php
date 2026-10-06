<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_question_events', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->string('question_key', 64)->index();
            $table->string('visitor_key', 64);
            $table->string('topic', 30)->index();
            $table->json('criteria')->nullable();
            $table->string('demand_key', 64)->nullable()->index();
            $table->unsignedTinyInteger('match_count')->nullable();
            $table->timestamps();
            $table->index(['created_at', 'topic']);
        });
        Schema::create('ai_demand_plans', function (Blueprint $table) {
            $table->id();
            $table->string('demand_key', 64)->unique();
            $table->string('status', 20)->default('reviewing');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_demand_plans');
        Schema::dropIfExists('ai_question_events');
    }
};
