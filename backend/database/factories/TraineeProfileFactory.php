<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TraineeProfile>
 */
class TraineeProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'sex' => fake()->randomElement(['Male', 'Female']),
            'birthdate' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'contact_number' => fake()->numerify('09#########'),
            'email' => fake()->unique()->safeEmail(),
            'city_municipality' => 'Butuan City',
            'province' => 'Agusan del Norte',
            'region' => 'Caraga',
            'lgu_income_class' => '1st Class',
            'educational_level' => fake()->randomElement(['Elementary', 'High School', 'Vocational', 'College', 'Post Graduate']),
            'organization_affiliation' => fake()->company(),
        ];
    }
}
