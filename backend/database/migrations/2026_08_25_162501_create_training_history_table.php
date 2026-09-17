<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainee_profiles')->cascadeOnDelete();
            $table->foreignId('training_program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->enum('chain_level', [
                'Production', 'Post-Harvest', 'Processing', 'Marketing', 'Consumption',
            ]);
            $table->date('date_attended');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['trainee_id', 'training_program_id']);
            $table->index(['trainee_id', 'chain_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_history');
    }
};
