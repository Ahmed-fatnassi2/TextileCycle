<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkshopFactory extends Factory
{
    public function definition(): array
    {
        $city = fake()->randomElement(['Tunis', 'Sfax', 'Sousse', 'Bizerte', 'Nabeul', 'Monastir']);

        return [
            'name' => 'Atelier Couture '.$city.' '.fake()->unique()->numerify('##'),
            'specialty' => fake()->randomElement(['Retouche', 'Couture', 'Maroquinerie']),
            'address' => fake()->buildingNumber().' Avenue '.fake()->randomElement(['Habib Bourguiba', 'Farhat Hached', 'de la Liberté']).', '.$city,
            'phone' => fake()->numerify('2# ### ###'),
        ];
    }
}
