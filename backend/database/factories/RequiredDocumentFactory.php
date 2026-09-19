<?php

namespace Database\Factories;

use App\Models\RequiredDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequiredDocument>
 */
class RequiredDocumentFactory extends Factory
{
    protected $model = RequiredDocument::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->slug(2, false),
            'name' => $this->faker->words(3, true),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
