<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            // Separate from `status` (upcoming/ongoing/completed/cancelled) —
            // a program can be scheduled without yet being open for trainees
            // to register their own interest in it.
            $table->boolean('is_posted')->default(false)->after('status');
        });

        Schema::create('training_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainee_profiles')->cascadeOnDelete();
            $table->foreignId('training_program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->timestamp('registered_at');
            $table->timestamps();

            $table->unique(['trainee_id', 'training_program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_registrations');

        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropColumn('is_posted');
        });
    }
};
