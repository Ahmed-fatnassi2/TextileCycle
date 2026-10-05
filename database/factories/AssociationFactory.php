<?php

namespace Database\Factories;

use App\Models\Association;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Association>
 */
class AssociationFactory extends Factory
{
    protected $model = Association::class;

    public function definition(): array
    {
        $cities = ['Tunis', 'Sfax', 'Sousse', 'Kairouan', 'Bizerte', 'Monastir', 'Nabeul', 'Gabès'];
        $firstNames = ['Amel', 'Sami', 'Ines', 'Yassine', 'Meriem', 'Fares', 'Nour', 'Aymen'];
        $lastNames = ['Ben Salem', 'Trabelsi', 'Ben Youssef', 'Gharbi', 'Mansouri', 'Jaziri'];
        $city = fake()->randomElement($cities);
        $phone = (string) fake()->numberBetween(10000000, 99999999);

        return [
            'user_id' => User::factory(),
            'name' => 'Association Nouvelle Trame '.$city,
            'contact_person' => fake()->randomElement($firstNames).' '.fake()->randomElement($lastNames),
            'phone' => '+216 '.substr($phone, 0, 2).' '.substr($phone, 2, 3).' '.substr($phone, 5, 3),
            'needs_description' => 'Collecte de vêtements propres et en bon état pour les familles accompagnées à '.$city.'.',
            'status' => fake()->randomElement(Association::statuses()),
        ];
    }
}