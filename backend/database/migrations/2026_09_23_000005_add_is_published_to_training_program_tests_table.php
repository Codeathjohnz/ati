<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_program_tests', function (Blueprint $table) {
            // A test is editable as a draft at any point; trainees can only
            // see and take it once an officer explicitly publishes it.
            $table->boolean('is_published')->default(false)->after('passing_score');
        });
    }

    public function down(): void
    {
        Schema::table('training_program_tests', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }
};
