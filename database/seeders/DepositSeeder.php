<?php

namespace Database\Seeders;

use App\Models\Deposit;
use App\Models\DepositPoint;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepositSeeder extends Seeder
{
    public function run(): void
    {
        // 10 points de collecte
        $points = DepositPoint::factory(10)->create();

        // 30 utilisateurs citoyens
        $users = User::factory(30)->create();

        // 80 dépôts
        Deposit::factory(80)->create([
            'user_id' => fn() => $users->random()->id,
            'deposit_point_id' => fn() => $points->random()->id,
        ]);
    }
}