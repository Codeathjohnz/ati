<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainee_profiles')->cascadeOnDelete();
            $table->foreignId('training_program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'declined'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['trainee_id', 'training_program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_confirmations');
    }
};
