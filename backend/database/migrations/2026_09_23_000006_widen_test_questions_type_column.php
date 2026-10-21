<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * test_questions.type was originally a DB-level ENUM('multiple_choice',
 * 'fill_blank'), which rejects any value outside that set — including the
 * new 'identification' type. This widens it to a plain string column;
 * allowed values are validated in TrainingProgramTestController instead, so
 * adding a future question type never needs another ALTER TABLE.
 *
 * doctrine/dbal isn't installed, so Blueprint's fluent ->change() isn't
 * available here. MySQL (production) can widen the column with a raw
 * statement directly; SQLite (the test suite) enforces its "enum" as a CHECK
 * constraint baked into the table definition, which has to be rebuilt.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::create('test_questions_rebuild', function (Blueprint $table) {
                $table->id();
                $table->foreignId('training_program_test_id')->constrained('training_program_tests')->cascadeOnDelete();
                $table->string('type');
                $table->text('question_text');
                $table->json('options')->nullable();
                $table->string('correct_answer');
                $table->unsignedSmallInteger('points')->default(1);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });

            DB::statement('INSERT INTO test_questions_rebuild SELECT * FROM test_questions');
            Schema::drop('test_questions');
            Schema::rename('test_questions_rebuild', 'test_questions');

            return;
        }

        DB::statement("ALTER TABLE test_questions MODIFY type VARCHAR(30) NOT NULL");
    }

    public function down(): void
    {
        // Best-effort only: reverting would require rejecting any
        // 'identification' rows already saved, which isn't safe to do
        // automatically. Leave the widened column in place on rollback.
    }
};
