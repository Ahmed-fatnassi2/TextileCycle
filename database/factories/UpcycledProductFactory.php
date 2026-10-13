<?php

namespace Database\Factories;

use App\Models\MaterialBatch;
use App\Models\UpcycledProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UpcycledProduct>
 */
class UpcycledProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Sac cabas', 'Pochette', 'Veste', 'Écharpe', 'Trousse']),
            'price' => $this->faker->randomFloat(2, 10, 250),
            'stock' => $this->faker->numberBetween(0, 100),
            'material_batch_id' => MaterialBatch::factory(),
        ];
    }
}