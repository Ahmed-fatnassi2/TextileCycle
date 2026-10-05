<?php

namespace Database\Seeders;

use App\Models\Association;
use App\Models\Donation;
use Illuminate\Database\Seeder;

class AssociationSeeder extends Seeder
{
    public function run(): void
    {
        $associations = Association::factory(8)->create();

        Donation::factory(24)->state(fn () => [
            'association_id' => $associations->random()->id,
        ])->create();
    }
}