<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_program_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->enum('type', ['pre_test', 'post_test']);
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->unsignedTinyInteger('passing_score')->default(70);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['training_program_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_program_tests');
    }
};
