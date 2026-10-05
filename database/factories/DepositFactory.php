<?php

namespace Database\Factories;

use App\Models\DepositPoint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepositFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'deposit_point_id' => DepositPoint::factory(),
            'weight_kg' => $this->faker->randomFloat(2, 0.5, 15),
            'status' => $this->faker->randomElement(['Déposé', 'Trié', 'Rejeté']),
            'state' => $this->faker->randomElement(['Neuf', 'Bon état', 'Usé', 'Déchiré']),
            'deposit_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}