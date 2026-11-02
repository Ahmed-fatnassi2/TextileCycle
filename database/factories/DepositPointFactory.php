<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepositPointFactory extends Factory
{
    // Centre approximatif de chaque ville (latitude, longitude)
    private const CITIES = [
        'Tunis' => [36.8065, 10.1815],
        'Ariana' => [36.8625, 10.1956],
        'La Marsa' => [36.8782, 10.3247],
        'Sousse' => [35.8256, 10.6360],
        'Sfax' => [34.7400, 10.7600],
        'Monastir' => [35.7643, 10.8113],
        'Bizerte' => [37.2744, 9.8739],
        'Kairouan' => [35.6781, 10.0963],
        'Nabeul' => [36.4561, 10.7376],
        'Gabès' => [33.8815, 10.0982],
    ];

    public function definition(): array
    {
        $streets = ['Avenue Habib Bourguiba', 'Avenue de la Liberté', 'Rue de la République', 'Avenue Farhat Hached'];
        $city = $this->faker->randomElement(array_keys(self::CITIES));
        [$lat, $lng] = self::CITIES[$city];

        return [
            'name' => 'Point textile '.$city.' '.$this->faker->numerify('##'),
            'address' => $this->faker->buildingNumber().' '.$this->faker->randomElement($streets),
            'city' => $city,
            // Jusqu'à ~2 km autour du centre-ville
            'latitude' => $lat + $this->faker->randomFloat(4, -0.02, 0.02),
            'longitude' => $lng + $this->faker->randomFloat(4, -0.02, 0.02),
            'capacity' => $this->faker->numberBetween(50, 500),
            'state' => $this->faker->randomElement(['Ouvert', 'Plein', 'En maintenance', 'Fermé']),
        ];
    }

    public function withoutCoordinates(): static
    {
        return $this->state(fn () => ['latitude' => null, 'longitude' => null]);
    }
}
