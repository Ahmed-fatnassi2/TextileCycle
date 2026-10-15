<?php

namespace Database\Seeders;

use App\Models\MaterialBatch;
use App\Models\UpcycledProduct;
use Illuminate\Database\Seeder;

class UpcyclingSeeder extends Seeder
{
    public function run(): void
    {
        MaterialBatch::factory(8)
            ->create()
            ->each(function (MaterialBatch $batch): void {
                UpcycledProduct::factory(random_int(1, 4))->create([
                    'material_batch_id' => $batch->id,
                ]);
            });
    }
}