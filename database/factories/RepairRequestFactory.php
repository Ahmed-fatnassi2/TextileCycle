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

<<<<<<< HEAD
        $status = fake()->randomElement(RepairRequest::statuses());

=======
>>>>>>> origin/Donation/Association-"aziz"
        return [
            'user_id' => User::factory(),
            'item_description' => $items[$problem],
            'problem_type' => $problem,
            'cost' => fake()->randomFloat(2, 0, 80),
<<<<<<< HEAD
            'estimated_cost' => in_array($status, [RepairRequest::STATUS_QUOTE_PROPOSED, RepairRequest::STATUS_QUOTE_ACCEPTED, RepairRequest::STATUS_IN_PROGRESS, RepairRequest::STATUS_READY_FOR_PICKUP, RepairRequest::STATUS_REPAIRED, RepairRequest::STATUS_COLLECTED], true) ? fake()->randomFloat(2, 15, 80) : null,
            'estimated_completion_date' => fake()->dateTimeBetween('+2 days', '+3 weeks'),
            'status' => $status,
=======
            'status' => fake()->randomElement(RepairRequest::statuses()),
>>>>>>> origin/Donation/Association-"aziz"
            'workshop_id' => Workshop::factory(),
        ];
    }
}
