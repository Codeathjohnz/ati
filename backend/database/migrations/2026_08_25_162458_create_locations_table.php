<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('region')->default('Caraga');
            $table->string('province');
            $table->string('city_municipality');
            $table->enum('lgu_income_class', [
                '1st Class', '2nd Class', '3rd Class', '4th Class', '5th Class', '6th Class',
            ])->nullable();
            $table->timestamps();

            $table->unique(['province', 'city_municipality']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
