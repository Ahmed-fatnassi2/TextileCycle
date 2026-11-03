<?php

namespace Database\Seeders;

use App\Models\RepairRequest;
use App\Models\User;
use App\Models\Workshop;
use App\Services\RepairImpactCalculator;
use Illuminate\Database\Seeder;

class RepairAiDemoSeeder extends Seeder
{
    public const PASSWORD = 'DemoTunisie2026!';

    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@textilecycle.test'], [
            'name' => 'Admin de démonstration', 'password' => self::PASSWORD, 'role' => User::ROLE_ADMIN, 'email_verified_at' => now(),
        ]);
        $citizen = User::updateOrCreate(['email' => 'citoyen@textilecycle.test'], [
            'name' => 'Youssef Mansouri', 'password' => self::PASSWORD, 'role' => User::ROLE_USER, 'email_verified_at' => now(),
        ]);
        $otherCitizen = User::updateOrCreate(['email' => 'citoyenne2@textilecycle.test'], [
            'name' => 'Amira Trabelsi', 'password' => self::PASSWORD, 'role' => User::ROLE_USER, 'email_verified_at' => now(),
        ]);
        $owner = User::updateOrCreate(['email' => 'atelier@textilecycle.test'], [
            'name' => 'Nadia Ben Youssef', 'password' => self::PASSWORD, 'role' => User::ROLE_WORKSHOP_OWNER, 'email_verified_at' => now(),
        ]);

        $workshop = Workshop::updateOrCreate(['name' => 'Atelier Démo AI Tunis'], [
            'specialty' => 'Couture, retouche et réparation textile',
            'address' => '42 Rue de Marseille, Tunis', 'phone' => '+216 22 456 789', 'owner_id' => $owner->id,
        ]);

        $repairs = [
            ['key' => 'demo-pending', 'user' => $citizen, 'description' => 'Pantalon en jean avec fermeture éclair cassée', 'problem' => RepairRequest::PROBLEM_ZIPPER, 'category' => 'pantalon', 'status' => RepairRequest::STATUS_PENDING, 'quote' => null, 'days' => 8],
            ['key' => 'demo-expensive-quote', 'user' => $citizen, 'description' => 'Veste en cuir avec doublure et fermeture à remplacer', 'problem' => RepairRequest::PROBLEM_ZIPPER, 'category' => 'veste', 'status' => RepairRequest::STATUS_QUOTE_PROPOSED, 'quote' => 125, 'days' => 12],
            ['key' => 'demo-accepted', 'user' => $citizen, 'description' => 'Robe à reprendre à la taille', 'problem' => RepairRequest::PROBLEM_HEM, 'category' => 'robe', 'status' => RepairRequest::STATUS_QUOTE_ACCEPTED, 'quote' => 35, 'days' => 4],
            ['key' => 'demo-in-progress', 'user' => $citizen, 'description' => 'Manteau avec une couture ouverte', 'problem' => RepairRequest::PROBLEM_HOLE, 'category' => 'veste', 'status' => RepairRequest::STATUS_IN_PROGRESS, 'quote' => 28, 'days' => -1],
            ['key' => 'demo-ready-quality-impact', 'user' => $citizen, 'description' => 'Pantalon noir à raccourcir avec un nouvel ourlet', 'problem' => RepairRequest::PROBLEM_HEM, 'category' => 'pantalon', 'status' => RepairRequest::STATUS_READY_FOR_PICKUP, 'quote' => 32, 'days' => 0],
            ['key' => 'demo-private-other-citizen', 'user' => $otherCitizen, 'description' => 'Chemise avec un bouton manquant', 'problem' => RepairRequest::PROBLEM_HOLE, 'category' => 'haut', 'status' => RepairRequest::STATUS_IN_PROGRESS, 'quote' => 15, 'days' => 5],
        ];

        foreach ($repairs as $item) {
            $repair = RepairRequest::updateOrCreate(
                ['user_id' => $item['user']->id, 'workshop_id' => $workshop->id, 'item_description' => $item['description']],
                [
                    'problem_type' => $item['problem'], 'item_category' => $item['category'], 'status' => $item['status'],
                    'estimated_cost' => $item['quote'], 'estimated_completion_date' => now()->addDays($item['days'])->toDateString(),
                    'cost' => $item['status'] === RepairRequest::STATUS_READY_FOR_PICKUP ? $item['quote'] : 0,
                ],
            );

            if (in_array($item['status'], [RepairRequest::STATUS_IN_PROGRESS, RepairRequest::STATUS_READY_FOR_PICKUP], true)) {
                $repair->forceFill(['started_at' => now()->subDays(3), 'ready_at' => $item['status'] === RepairRequest::STATUS_READY_FOR_PICKUP ? now()->subDay() : null])->save();
            }
            if ($item['status'] === RepairRequest::STATUS_READY_FOR_PICKUP) {
                app(RepairImpactCalculator::class)->calculate($repair);
                $repair->update(['quality_score' => 4, 'quality_note' => 'Coutures régulières sur les zones visibles. Vérifiez les finitions à la réception.', 'quality_checked_at' => now()->subDay()]);
            }
        }

        // Trois réparations clôturées donnent à l'assistant une base tarifaire historique exploitable.
        foreach ([24, 29, 35] as $i => $cost) {
            RepairRequest::updateOrCreate(
                ['user_id' => $citizen->id, 'workshop_id' => $workshop->id, 'item_description' => 'Ourlet terminé — exemple tarifaire '.($i + 1)],
                ['problem_type' => RepairRequest::PROBLEM_HEM, 'item_category' => 'pantalon', 'status' => RepairRequest::STATUS_REPAIRED, 'cost' => $cost, 'estimated_cost' => $cost, 'estimated_completion_date' => now()->subDays(10 + $i)->toDateString(), 'started_at' => now()->subDays(15 + $i), 'ready_at' => now()->subDays(10 + $i)],
            );
        }

        // Le compte association est déjà créé par TunisiaDemoSeeder : le rôle citoyen donne accès à son espace.
        $associationUser = User::where('email', 'partenaire.tunis@textilecycle.test')->first();
        if ($associationUser) $associationUser->update(['password' => self::PASSWORD, 'email_verified_at' => now(), 'role' => User::ROLE_USER]);
    }
}
