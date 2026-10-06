<?php

namespace Database\Factories;

use App\Models\RepairRequest;
use App\Models\Workshop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepairRequestFactory extends Factory
{
    public function definition(): array
    {
        $problem = fake()->randomElement(RepairRequest::problemTypes());
        $items = [
            RepairRequest::PROBLEM_ZIPPER => 'Veste en jean, fermeture éclair cassée',
            RepairRequest::PROBLEM_HOLE => 'Pull en laine avec un trou au coude',
            RepairRequest::PROBLEM_HEM => 'Pantalon noir à raccourcir avec un nouvel ourlet',
        ];

        return [
            'user_id' => User::factory(),
            'item_description' => $items[$problem],
            'problem_type' => $problem,
            'cost' => fake()->randomFloat(2, 0, 80),
            'status' => fake()->randomElement(RepairRequest::statuses()),
            'workshop_id' => Workshop::factory(),
        ];
    }
}
