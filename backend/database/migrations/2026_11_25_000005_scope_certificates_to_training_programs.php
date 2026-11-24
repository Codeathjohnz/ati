<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The certificate gate moves from "all 5 chain levels complete, one
 * certificate ever" to "registration + attendance + pre-test + post-test +
 * evaluation complete for a specific training program" — so a trainee can
 * now earn more than one certificate, one per program. Existing
 * certificates (training_program_id left null) stay valid; a null doesn't
 * collide with the new composite unique constraint since SQL treats each
 * null as distinct.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropUnique(['trainee_id']);
            $table->foreignId('training_program_id')->nullable()->after('trainee_id')
                ->constrained('training_programs')->cascadeOnDelete();
            $table->unique(['trainee_id', 'training_program_id']);
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropUnique(['trainee_id', 'training_program_id']);
            $table->dropConstrainedForeignId('training_program_id');
            $table->unique('trainee_id');
        });
    }
};
