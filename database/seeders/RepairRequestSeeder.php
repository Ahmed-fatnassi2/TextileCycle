<?php

namespace Database\Seeders;

use App\Models\RepairRequest;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class RepairRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Permet d'exécuter ce seeder seul, sans dépendance manuelle.
        $this->call(WorkshopSeeder::class);

        Workshop::orderBy('name')->get()->each(function (Workshop $workshop) {
            if ($workshop->repairRequests()->doesntExist()) {
                RepairRequest::factory(5)->create(['workshop_id' => $workshop->id]);
            }
        });
    }
}
