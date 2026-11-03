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
            RolesAndPermissionsSeeder::class,
            DepositSeeder::class,
            RepairSeeder::class,
            AssociationSeeder::class,
            TunisiaDemoSeeder::class,
            RepairAiDemoSeeder::class,
            UpcyclingSeeder::class,
        ]);

        if (env('ADMIN_EMAIL') && env('ADMIN_PASSWORD')) {
            $admin = User::updateOrCreate(
                ['email' => env('ADMIN_EMAIL')],
                [
                    'name' => 'Administrateur TexTileCycle',
                    'password' => Hash::make(env('ADMIN_PASSWORD')),
                    'role' => User::ROLE_ADMIN,
                ],
            );
            $admin->hasVerifiedEmail() || $admin->markEmailAsVerified();
        }
    }
}
