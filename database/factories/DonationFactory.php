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
            'category' => fake()->randomElement(Donation::CATEGORIES),
            'description' => fake()->randomElement([
                'Vêtements chauds pour les familles accompagnées cet hiver.',
                'Tenues scolaires et chaussures pour enfants de 6 à 12 ans.',
                'Linge et vêtements adultes en bon état pour notre atelier solidaire.',
            ]),
            'delivery_address' => fake()->buildingNumber().' Avenue Habib Bourguiba',
            'delivery_city' => fake()->randomElement(['Tunis', 'Sfax', 'Sousse', 'Ariana', 'Bizerte']),
            'donation_date' => fake()->dateTimeBetween('-3 months', 'today'),
            'status' => fake()->randomElement(Donation::statuses()),
        ];
    }
}