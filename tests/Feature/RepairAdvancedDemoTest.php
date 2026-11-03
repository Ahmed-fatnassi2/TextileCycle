<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Database\Seeders\RepairAiDemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairAdvancedDemoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class, RepairAiDemoSeeder::class]);
    }

    public function test_seeded_citizen_can_find_the_high_quote_and_see_the_three_alternative_choices(): void
    {
        $citizen = User::where('email', 'citoyen@textilecycle.test')->firstOrFail();
        $repair = RepairRequest::where('item_description', 'Veste en cuir avec doublure et fermeture à remplacer')->firstOrFail();

        $this->actingAs($citizen)->get(route('repairs.index'))
            ->assertOk()
            ->assertSee('Devis élevé')
            ->assertSee('125,00 TND')
            ->assertSee(route('repairs.show', $repair));

        $this->get(route('repairs.show', $repair))
            ->assertOk()
            ->assertSee('Voir les alternatives avec l’assistant')
            ->assertSee('Réparer')
            ->assertSee('Upcycler')
            ->assertSee('Donner');
    }

    public function test_owner_dashboard_highlights_risk_and_changing_eta_recalculates_it(): void
    {
        $owner = User::where('email', 'atelier@textilecycle.test')->firstOrFail();
        $repair = RepairRequest::where('item_description', 'Manteau avec une couture ouverte')->firstOrFail();

        $this->actingAs($owner)->get(route('workshop-owner.dashboard'))
            ->assertOk()
            ->assertSee('Réparations à surveiller')
            ->assertSee('Manteau avec une couture ouverte')
            ->assertSee('Risque élevé');

        $this->patch(route('workshop-owner.repairs.estimate', $repair), [
            'estimated_completion_date' => now()->addDays(30)->toDateString(),
        ])->assertRedirect();

        $this->assertSame(now()->addDays(30)->toDateString(), $repair->fresh()->estimated_completion_date->toDateString());

        $this->get(route('workshop-owner.repairs.show', $repair))->assertOk()->assertSee('Risque modéré');
    }
}
