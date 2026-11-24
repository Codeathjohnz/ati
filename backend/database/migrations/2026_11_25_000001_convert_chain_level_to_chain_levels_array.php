<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A training program can now cover more than one chain level at once (a
 * comprehensive program covering all 5, decided by the officer per
 * program), so the single `chain_level` enum becomes a `chain_levels` JSON
 * array on both tables. Avoids Schema::change() (would need doctrine/dbal,
 * not installed) by adding the new column, backfilling it from the old one,
 * then dropping the old column — three plain, portable schema operations
 * instead of one driver-dependent ALTER.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->json('chain_levels')->nullable()->after('description');
        });

        DB::table('training_programs')->select('id', 'chain_level')->orderBy('id')->each(function ($row) {
            DB::table('training_programs')->where('id', $row->id)->update([
                'chain_levels' => json_encode([$row->chain_level]),
            ]);
        });

        // SQLite refuses to drop a column that's still part of an index, so
        // the old composite index (on a column about to disappear) has to go
        // first; a plain index on the two still-scalar columns replaces it
        // (chain_levels is JSON now, not practically indexable the same way).
        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropIndex('training_programs_chain_level_status_start_date_index');
            $table->dropColumn('chain_level');
            $table->index(['status', 'start_date']);
        });

        Schema::table('training_history', function (Blueprint $table) {
            $table->json('chain_levels')->nullable()->after('training_program_id');
        });

        DB::table('training_history')->select('id', 'chain_level')->orderBy('id')->each(function ($row) {
            DB::table('training_history')->where('id', $row->id)->update([
                'chain_levels' => json_encode([$row->chain_level]),
            ]);
        });

        Schema::table('training_history', function (Blueprint $table) {
            $table->dropIndex('training_history_trainee_id_chain_level_index');
            $table->dropColumn('chain_level');
            $table->index('trainee_id');
        });
    }

    public function down(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->string('chain_level')->nullable()->after('description');
        });

        DB::table('training_programs')->select('id', 'chain_levels')->orderBy('id')->each(function ($row) {
            $levels = json_decode($row->chain_levels, true) ?? [];
            DB::table('training_programs')->where('id', $row->id)->update([
                'chain_level' => $levels[0] ?? 'Production',
            ]);
        });

        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropIndex(['status', 'start_date']);
            $table->dropColumn('chain_levels');
            $table->index(['chain_level', 'status', 'start_date']);
        });

        Schema::table('training_history', function (Blueprint $table) {
            $table->string('chain_level')->nullable()->after('training_program_id');
        });

        DB::table('training_history')->select('id', 'chain_levels')->orderBy('id')->each(function ($row) {
            $levels = json_decode($row->chain_levels, true) ?? [];
            DB::table('training_history')->where('id', $row->id)->update([
                'chain_level' => $levels[0] ?? 'Production',
            ]);
        });

        Schema::table('training_history', function (Blueprint $table) {
            $table->dropIndex(['trainee_id']);
            $table->dropColumn('chain_levels');
            $table->index(['trainee_id', 'chain_level']);
        });
    }
};
