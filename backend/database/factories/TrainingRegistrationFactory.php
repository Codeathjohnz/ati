<?php

namespace Database\Factories;

use App\Models\TraineeProfile;
use App\Models\TrainingProgram;
use App\Models\TrainingRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingRegistration>
 */
class TrainingRegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'trainee_id' => TraineeProfile::factory(),
            'training_program_id' => TrainingProgram::factory(),
            'registered_at' => now(),
        ];
    }
}
