<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_submissions', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('remarks');
            $table->string('attachment_original_name')->nullable()->after('attachment_path');
            $table->enum('review_status', ['pending', 'approved', 'needs_revision'])
                ->default('pending')
                ->after('attachment_original_name');
        });

        // Backfill: a row that was already `submitted = true` under the old
        // boolean-only model was already the terminal "done" state, so it
        // becomes 'approved' rather than sitting unreviewed as 'pending'.
        DB::table('document_submissions')->where('submitted', true)->update(['review_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('document_submissions', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_original_name', 'review_status']);
        });
    }
};
