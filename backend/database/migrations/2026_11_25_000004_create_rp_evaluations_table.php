<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Digitizes the Resource Person (RP) Evaluation Form (client-approved
 * "RP eval.docx"): a header identifying the resource person/training/date,
 * a per-topic relevance/time/methodology table, a 10-criterion 1-5 Likert
 * table, and three open-ended questions. Column names mirror the
 * historical RP evaluation export datasets (see the Phase 7 import) so the
 * two line up without any field-name translation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rp_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainee_profiles')->cascadeOnDelete();
            $table->foreignId('training_history_id')->unique()->constrained('training_history')->cascadeOnDelete();

            // One row per topic discussed: {topic, relevance, time_allocation,
            // suggested_time, methodology, suggested_methodology}.
            $table->json('topic_ratings')->nullable();

            $table->unsignedTinyInteger('clarity_objectives');
            $table->unsignedTinyInteger('topic_organization');
            $table->unsignedTinyInteger('clarity_presentation');
            $table->unsignedTinyInteger('instructional_aids_quality');
            $table->unsignedTinyInteger('teaching_ability');
            $table->unsignedTinyInteger('question_answering_ability');
            $table->unsignedTinyInteger('participant_interest');
            $table->unsignedTinyInteger('time_management');
            $table->unsignedTinyInteger('topic_ending');
            $table->unsignedTinyInteger('overall_satisfaction');

            $table->text('liked_about_rp')->nullable();
            $table->text('disliked_about_rp')->nullable();
            $table->text('other_remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rp_evaluations');
    }
};
