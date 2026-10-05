<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepositPointFactory extends Factory
{
    public function definition(): array
    {
        $cities = ['Tunis', 'Ariana', 'La Marsa', 'Sousse', 'Sfax', 'Monastir', 'Bizerte', 'Kairouan', 'Nabeul', 'Gabès'];
        $streets = ['Avenue Habib Bourguiba', 'Avenue de la Liberté', 'Rue de la République', 'Avenue Farhat Hached'];
        $city = $this->faker->randomElement($cities);

        return [
            'name' => 'Point textile '.$city.' '.$this->faker->numerify('##'),
            'address' => $this->faker->buildingNumber().' '.$this->faker->randomElement($streets),
            'city' => $city,
            'capacity' => $this->faker->numberBetween(50, 500),
            'state' => $this->faker->randomElement(['Ouvert', 'Plein', 'En maintenance', 'Fermé']),
        ];
    }
}