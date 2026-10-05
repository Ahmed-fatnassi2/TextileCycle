<?php

namespace Database\Factories;

use App\Models\Association;
use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'association_id' => Association::factory(),
            'quantity_items' => fake()->numberBetween(10, 250),
            'donation_date' => fake()->dateTimeBetween('-3 months', 'today'),
            'status' => fake()->randomElement(Donation::statuses()),
        ];
    }
}