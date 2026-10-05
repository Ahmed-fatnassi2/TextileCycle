<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepositPointFactory extends Factory
{
    public function definition(): array
    {
        $cities = ['Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Tanger', 'Agadir'];

        return [
            'name' => 'Point ' . $this->faker->unique()->company(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->randomElement($cities),
            'capacity' => $this->faker->numberBetween(50, 500),
            'state' => $this->faker->randomElement(['Ouvert', 'Plein', 'En maintenance', 'Fermé']),
        ];
    }
}