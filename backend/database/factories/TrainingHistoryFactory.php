<?php

namespace Database\Factories;

use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingHistory>
 */
class TrainingHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'trainee_id' => TraineeProfile::factory(),
            'training_program_id' => TrainingProgram::factory(),
            'chain_level' => fake()->randomElement(TrainingProgram::CHAIN_LEVELS),
            'date_attended' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }
}
