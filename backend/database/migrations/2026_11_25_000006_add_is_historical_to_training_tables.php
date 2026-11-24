<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks rows created by the historical dataset import (Phase 7) so they
 * stay invisible to the live portal — dashboards, trainee lists, reports —
 * by default, via a global scope on each model. They exist in the database
 * for future historical reporting, but never show up in, or skew, anything
 * a current officer/admin/trainee actually interacts with today.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainee_profiles', function (Blueprint $table) {
            $table->boolean('is_historical')->default(false)->after('created_by');
        });

        Schema::table('training_programs', function (Blueprint $table) {
            $table->boolean('is_historical')->default(false)->after('created_by');
        });

        Schema::table('training_history', function (Blueprint $table) {
            $table->boolean('is_historical')->default(false)->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('trainee_profiles', function (Blueprint $table) {
            $table->dropColumn('is_historical');
        });

        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropColumn('is_historical');
        });

        Schema::table('training_history', function (Blueprint $table) {
            $table->dropColumn('is_historical');
        });
    }
};
