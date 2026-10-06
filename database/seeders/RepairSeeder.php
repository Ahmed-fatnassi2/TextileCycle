<?php

namespace Database\Seeders;

use App\Models\RepairRequest;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class RepairSeeder extends Seeder
{
    public function run(): void
    {
        $workshops = collect([
            ['name' => 'Atelier Couture Sfax', 'specialty' => 'Retouche', 'address' => '18 Avenue Habib Bourguiba, Sfax', 'phone' => '21 345 678'],
            ['name' => 'La Main d’Or Tunis', 'specialty' => 'Couture', 'address' => '42 Rue de Marseille, Tunis', 'phone' => '22 456 789'],
            ['name' => 'Retouches du Sahel', 'specialty' => 'Retouche', 'address' => '9 Avenue Farhat Hached, Sousse', 'phone' => '23 567 890'],
            ['name' => 'Cuir & Fil Bizerte', 'specialty' => 'Maroquinerie', 'address' => '6 Rue de la Corniche, Bizerte', 'phone' => '24 678 901'],
            ['name' => 'Aiguille de Nabeul', 'specialty' => 'Couture', 'address' => '15 Avenue Habib Thameur, Nabeul', 'phone' => '25 789 012'],
            ['name' => 'Atelier Monastir Retouche', 'specialty' => 'Retouche', 'address' => '27 Avenue de la République, Monastir', 'phone' => '26 890 123'],
        ])->map(fn (array $data) => Workshop::updateOrCreate(
            ['name' => $data['name']],
            $data,
        ));

        // Le seeder peut être relancé sans dupliquer les données de démonstration.
        $workshops->each(function (Workshop $workshop) {
            if ($workshop->repairRequests()->doesntExist()) {
                RepairRequest::factory(5)->create([
                    'workshop_id' => $workshop->id,
                ]);
            }
        });
    }
}
