<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepositSeeder::class,
            RepairSeeder::class,
            AssociationSeeder::class,
            TunisiaDemoSeeder::class,
            UpcyclingSeeder::class,
        ]);

        if (env('ADMIN_EMAIL') && env('ADMIN_PASSWORD')) {
            User::updateOrCreate(
                ['email' => env('ADMIN_EMAIL')],
                [
                    'name' => 'Administrateur TexTileCycle',
                    'password' => Hash::make(env('ADMIN_PASSWORD')),
                    'role' => 'admin',
                ],
            );
        }
    }
}
