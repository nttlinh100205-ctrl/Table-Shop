<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_product_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('days')->index();
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->unsignedInteger('question_count');
            $table->unsignedInteger('sampled_count');
            $table->json('evidence');
            $table->json('result');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ai_product_reports'); }
};
