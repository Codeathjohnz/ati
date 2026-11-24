<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The historical RP evaluation exports (~20,000 rows across 8 files) don't
 * share any reliable ID with a specific training_history attendance record
 * — just free-text names, RP codes, and training titles, which risks a
 * wrong match if forced into the live rp_evaluations table (unique per
 * attendance). These two tables mirror the two export shapes field-for-
 * field instead (Full Evaluation = one row per respondent with the 10
 * Likert scores; Topic Evaluation = one row per respondent per topic
 * discussed), kept deliberately separate from — and never joined into —
 * live data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_rp_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('respondent_num')->nullable();
            $table->string('respondent_name')->nullable();
            $table->string('rp_code')->nullable();
            $table->string('training_code')->nullable();
            $table->string('resource_person_name')->nullable();
            $table->string('training_title')->nullable();
            $table->date('delivery_date')->nullable();

            $table->unsignedTinyInteger('clarity_objectives')->nullable();
            $table->unsignedTinyInteger('topic_organization')->nullable();
            $table->unsignedTinyInteger('clarity_presentation')->nullable();
            $table->unsignedTinyInteger('instructional_aids_quality')->nullable();
            $table->unsignedTinyInteger('teaching_ability')->nullable();
            $table->unsignedTinyInteger('question_answering_ability')->nullable();
            $table->unsignedTinyInteger('participant_interest')->nullable();
            $table->unsignedTinyInteger('time_management')->nullable();
            $table->unsignedTinyInteger('topic_ending')->nullable();
            $table->unsignedTinyInteger('overall_satisfaction')->nullable();

            $table->text('liked_about_rp')->nullable();
            $table->text('disliked_about_rp')->nullable();
            $table->text('other_remarks')->nullable();

            $table->string('source_file')->nullable();
            $table->timestamps();

            $table->index(['training_code', 'rp_code']);
        });

        Schema::create('historical_rp_topic_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('respondent_num')->nullable();
            $table->string('respondent_name')->nullable();
            $table->string('rp_code')->nullable();
            $table->string('training_code')->nullable();
            $table->string('resource_person_name')->nullable();
            $table->string('training_title')->nullable();
            $table->date('delivery_date')->nullable();

            $table->string('topic')->nullable();
            $table->unsignedTinyInteger('relevance')->nullable();
            $table->unsignedTinyInteger('time_allocation')->nullable();
            $table->string('suggested_time_allocation')->nullable();
            $table->unsignedTinyInteger('methodology')->nullable();
            $table->string('suggested_methodology')->nullable();

            $table->string('source_file')->nullable();
            $table->timestamps();

            $table->index(['training_code', 'rp_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_rp_topic_evaluations');
        Schema::dropIfExists('historical_rp_evaluations');
    }
};
