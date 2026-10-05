<?php

namespace Database\Seeders;

use App\Models\Association;
use App\Models\DepositPoint;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TunisiaDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@textilecycle.test'],
            [
                'name' => 'Admin de démonstration',
                'password' => Hash::make('DemoTunisie2026!'),
                'role' => 'admin',
            ],
        );

        $points = [
            ['name' => 'Point textile Tunis Centre', 'address' => '12 Avenue Habib Bourguiba', 'city' => 'Tunis', 'capacity' => 240, 'state' => DepositPoint::STATE_OUVERT],
            ['name' => 'Point textile La Marsa', 'address' => '8 Rue de la République', 'city' => 'La Marsa', 'capacity' => 160, 'state' => DepositPoint::STATE_OUVERT],
            ['name' => 'Point solidaire Ariana', 'address' => '21 Avenue de la Liberté', 'city' => 'Ariana', 'capacity' => 180, 'state' => DepositPoint::STATE_OUVERT],
            ['name' => 'Point textile Sousse', 'address' => '5 Avenue Farhat Hached', 'city' => 'Sousse', 'capacity' => 200, 'state' => DepositPoint::STATE_OUVERT],
            ['name' => 'Point collecte Sfax', 'address' => '17 Avenue Habib Bourguiba', 'city' => 'Sfax', 'capacity' => 220, 'state' => DepositPoint::STATE_OUVERT],
            ['name' => 'Point réemploi Monastir', 'address' => '3 Rue de la République', 'city' => 'Monastir', 'capacity' => 120, 'state' => DepositPoint::STATE_PLEIN],
            ['name' => 'Point textile Bizerte', 'address' => '9 Avenue de la Liberté', 'city' => 'Bizerte', 'capacity' => 150, 'state' => DepositPoint::STATE_OUVERT],
            ['name' => 'Point solidaire Kairouan', 'address' => '14 Avenue Farhat Hached', 'city' => 'Kairouan', 'capacity' => 130, 'state' => DepositPoint::STATE_OUVERT],
        ];

        foreach ($points as $point) {
            DepositPoint::updateOrCreate(['name' => $point['name']], $point);
        }

        $profiles = [
            [
                'email' => 'partenaire.tunis@textilecycle.test',
                'user_name' => 'Amel Ben Salem',
                'name' => 'Collectif Fil Solidaire Tunis',
                'contact_person' => 'Amel Ben Salem',
                'phone' => '+216 20 123 456',
                'needs_description' => 'Vêtements chauds pour les familles accompagnées dans le Grand Tunis.',
                'status' => Association::STATUS_APPROVED,
            ],
            [
                'email' => 'partenaire.sfax@textilecycle.test',
                'user_name' => 'Sami Trabelsi',
                'name' => 'Association Nouvelle Trame Sfax',
                'contact_person' => 'Sami Trabelsi',
                'phone' => '+216 22 234 567',
                'needs_description' => 'Vêtements enfants et tenues scolaires pour les bénéficiaires de Sfax.',
                'status' => Association::STATUS_PENDING,
            ],
            [
                'email' => 'partenaire.sousse@textilecycle.test',
                'user_name' => 'Ines Gharbi',
                'name' => 'Atelier Seconde Vie Sousse',
                'contact_person' => 'Ines Gharbi',
                'phone' => '+216 25 345 678',
                'needs_description' => 'Vêtements adultes en bon état et linge de maison pour notre atelier solidaire.',
                'status' => Association::STATUS_APPROVED,
            ],
        ];

        $associations = [];

        foreach ($profiles as $profile) {
            $user = User::firstOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['user_name'],
                    'password' => Hash::make('DemoTunisie2026!'),
                    'role' => 'member',
                ],
            );

            $associations[$profile['email']] = Association::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $profile['name'],
                    'contact_person' => $profile['contact_person'],
                    'phone' => $profile['phone'],
                    'needs_description' => $profile['needs_description'],
                    'status' => $profile['status'],
                ],
            );
        }

        $donations = [
            ['association' => 'partenaire.tunis@textilecycle.test', 'quantity_items' => 85, 'donation_date' => '2026-10-20', 'status' => Donation::STATUS_PENDING],
            ['association' => 'partenaire.tunis@textilecycle.test', 'quantity_items' => 42, 'donation_date' => '2026-10-02', 'status' => Donation::STATUS_DELIVERED],
            ['association' => 'partenaire.sousse@textilecycle.test', 'quantity_items' => 60, 'donation_date' => '2026-10-25', 'status' => Donation::STATUS_VALIDATED],
        ];

        foreach ($donations as $donation) {
            Donation::updateOrCreate(
                [
                    'association_id' => $associations[$donation['association']]->id,
                    'quantity_items' => $donation['quantity_items'],
                    'donation_date' => $donation['donation_date'],
                ],
                ['status' => $donation['status']],
            );
        }
    }
}