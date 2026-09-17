<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainee_profiles')->cascadeOnDelete();
            $table->foreignId('training_history_id')->constrained('training_history')->cascadeOnDelete();
            $table->foreignId('required_document_id')->constrained('required_documents')->cascadeOnDelete();
            $table->boolean('submitted')->default(false);
            $table->date('date_submitted')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['trainee_id', 'training_history_id', 'required_document_id'], 'doc_submission_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_submissions');
    }
};
