<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_test_id')->constrained('training_program_tests')->cascadeOnDelete();
            // Plain string, not a DB enum: validated against the allowed
            // type list in TrainingProgramTestController instead, so adding
            // a new question type later never requires an ALTER TABLE.
            $table->string('type');
            $table->text('question_text');
            $table->json('options')->nullable();
            $table->string('correct_answer');
            $table->unsignedSmallInteger('points')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_questions');
    }
};
