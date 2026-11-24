<?php

namespace Database\Factories;

use App\Models\RpEvaluation;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RpEvaluation>
 */
class RpEvaluationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'trainee_id' => TraineeProfile::factory(),
            'training_history_id' => TrainingHistory::factory(),
            'topic_ratings' => [],
            'clarity_objectives' => fake()->numberBetween(1, 5),
            'topic_organization' => fake()->numberBetween(1, 5),
            'clarity_presentation' => fake()->numberBetween(1, 5),
            'instructional_aids_quality' => fake()->numberBetween(1, 5),
            'teaching_ability' => fake()->numberBetween(1, 5),
            'question_answering_ability' => fake()->numberBetween(1, 5),
            'participant_interest' => fake()->numberBetween(1, 5),
            'time_management' => fake()->numberBetween(1, 5),
            'topic_ending' => fake()->numberBetween(1, 5),
            'overall_satisfaction' => fake()->numberBetween(1, 5),
        ];
    }
}
