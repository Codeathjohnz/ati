<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();

            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->enum('sex', ['Male', 'Female']);
            $table->date('birthdate')->nullable();

            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('address_line')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city_municipality');
            $table->string('province');
            $table->string('region')->default('Caraga');
            $table->enum('lgu_income_class', [
                '1st Class', '2nd Class', '3rd Class', '4th Class', '5th Class', '6th Class',
            ])->nullable();

            $table->enum('educational_level', [
                'Elementary', 'High School', 'Vocational', 'College', 'Post Graduate',
            ])->nullable();
            $table->string('organization_affiliation')->nullable();
            $table->string('occupation')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sex', 'province', 'city_municipality', 'lgu_income_class', 'educational_level'], 'trainee_profiles_filter_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainee_profiles');
    }
};
