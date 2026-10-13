<?php

namespace Database\Factories;

use App\Models\MaterialBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaterialBatch>
 */
class MaterialBatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'material_type' => $this->faker->randomElement(['Jean', 'Laine', 'Coton', 'Lin', 'Polyester']),
            'weight' => $this->faker->randomFloat(2, 5, 500),
            'quality_grade' => $this->faker->randomElement(['A', 'B', 'C']),
        ];
    }
}