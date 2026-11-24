<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->string('resource_person_name')->nullable()->after('description');
            // Array of {name, duration} — the topics discussed, as officers
            // enter them when creating/editing the program, and what the
            // Resource Person evaluation's per-topic table is built from.
            $table->json('topics')->nullable()->after('resource_person_name');
        });
    }

    public function down(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropColumn(['resource_person_name', 'topics']);
        });
    }
};
