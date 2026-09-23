<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainee_test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_test_id')->constrained('training_program_tests')->cascadeOnDelete();
            $table->foreignId('trainee_id')->constrained('trainee_profiles')->cascadeOnDelete();
            $table->foreignId('training_history_id')->constrained('training_history')->cascadeOnDelete();
            $table->json('answers');
            $table->unsignedInteger('raw_score');
            $table->unsignedInteger('max_score');
            $table->unsignedTinyInteger('percentage');
            $table->boolean('passed');
            $table->timestamp('attempted_at');
            $table->timestamps();

            $table->index(['trainee_id', 'training_history_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainee_test_attempts');
    }
};
