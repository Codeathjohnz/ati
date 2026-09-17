<?php

namespace Database\Factories;

use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingProgram>
 */
class TrainingProgramFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 year', '+2 months');

        return [
            'name' => fake()->words(4, true).' Training',
            'description' => fake()->sentence(),
            'chain_level' => fake()->randomElement(TrainingProgram::CHAIN_LEVELS),
            'venue' => 'ATI-Caraga Training Center',
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $start->format('Y-m-d'),
            'status' => 'completed',
        ];
    }
}
