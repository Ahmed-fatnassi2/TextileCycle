<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DonationPlanningAssistantTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Notification::fake();
        config([
            'donations.daily_deliveries' => 3,
            'donations.daily_items' => 300,
            'donations.planning_horizon_days' => 14,
        ]);
        $this->admin = User::factory()->admin()->create();
    }

    public function test_valid_proposals_are_returned_with_display_data(): void
    {
        $overdue = $this->donation(now()->subDays(5), 60);
        $target = now()->addDays(2)->toDateString();

        $this->fakeAi([['donation_id' => $overdue->id, 'new_date' => $target, 'reason' => 'En retard, premier jour libre.']]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.donations.calendar.optimize'))
            ->assertOk()
            ->assertJsonPath('summary', 'Une livraison en retard à replanifier.')
            ->assertJsonPath('stats.overdue', 1)
            ->assertJsonPath('discarded', 0)
            ->assertJsonPath('proposals.0.donation_id', $overdue->id)
            ->assertJsonPath('proposals.0.to', $target)
            ->assertJsonPath('proposals.0.overdue', true)
            ->assertJsonPath('proposals.0.reschedule_url', route('admin.donations.reschedule', $overdue));

        // Rien n'est modifié tant que l'admin n'a pas accepté
        $this->assertTrue($overdue->refresh()->donation_date->isSameDay(now()->subDays(5)));
    }

    public function test_invalid_proposals_are_discarded_by_the_server(): void
    {
        $a = $this->donation(now()->addDays(1), 50);
        $b = $this->donation(now()->addDays(1), 50);
        $delivered = $this->donation(now()->addDays(1), 10, Donation::STATUS_DELIVERED);
        $farAway = $this->donation(now()->addDays(40), 10);
        $fullDay = now()->addDays(3);
        $this->donation($fullDay, 280, Donation::STATUS_VALIDATED);

        $this->fakeAi([
            ['donation_id' => 99999, 'new_date' => now()->addDays(2)->toDateString(), 'reason' => 'id inventé'],
            ['donation_id' => $delivered->id, 'new_date' => now()->addDays(2)->toDateString(), 'reason' => 'déjà livré'],
            ['donation_id' => $farAway->id, 'new_date' => now()->addDays(2)->toDateString(), 'reason' => 'hors fenêtre'],
            ['donation_id' => $a->id, 'new_date' => now()->subDay()->toDateString(), 'reason' => 'passé'],
            ['donation_id' => $a->id, 'new_date' => now()->addDays(30)->toDateString(), 'reason' => 'après la fenêtre'],
            ['donation_id' => $a->id, 'new_date' => 'demain', 'reason' => 'format invalide'],
            ['donation_id' => $a->id, 'new_date' => $fullDay->toDateString(), 'reason' => 'jour plein'],
            ['donation_id' => $a->id, 'new_date' => now()->addDays(1)->toDateString(), 'reason' => 'même date'],
            ['donation_id' => $b->id, 'new_date' => now()->addDays(4)->toDateString(), 'reason' => 'valide'],
            ['donation_id' => $b->id, 'new_date' => now()->addDays(5)->toDateString(), 'reason' => 'doublon'],
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.donations.calendar.optimize'))->assertOk();

        $this->assertSame([$b->id], array_column($response->json('proposals'), 'donation_id'));
        $this->assertSame(9, $response->json('discarded'));
    }

    public function test_capacity_check_takes_earlier_proposals_into_account(): void
    {
        $day = now()->addDays(2);
        $this->donation($day, 100);
        $this->donation($day, 100);
        $first = $this->donation(now()->addDays(1), 50);
        $second = $this->donation(now()->addDays(1), 50);

        // Le jour cible a 1 place : la 1re proposition la prend, la 2e doit être écartée
        $this->fakeAi([
            ['donation_id' => $first->id, 'new_date' => $day->toDateString(), 'reason' => 'ok'],
            ['donation_id' => $second->id, 'new_date' => $day->toDateString(), 'reason' => 'jour devenu plein'],
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.donations.calendar.optimize'))->assertOk();

        $this->assertSame([$first->id], array_column($response->json('proposals'), 'donation_id'));
    }

    public function test_freed_capacity_can_be_reused_by_a_later_proposal(): void
    {
        $day = now()->addDays(2);
        $this->donation($day, 100);
        $this->donation($day, 100);
        $leaving = $this->donation($day, 100); // jour plein
        $incoming = $this->donation(now()->subDays(3), 80); // en retard

        $this->fakeAi([
            ['donation_id' => $leaving->id, 'new_date' => now()->addDays(6)->toDateString(), 'reason' => 'libère le jour'],
            ['donation_id' => $incoming->id, 'new_date' => $day->toDateString(), 'reason' => 'prend la place libérée'],
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.donations.calendar.optimize'))->assertOk();

        $this->assertCount(2, $response->json('proposals'));
    }

    public function test_the_ai_receives_the_real_situation_and_a_strict_json_schema(): void
    {
        $association = Association::factory()->create([
            'status' => Association::STATUS_APPROVED,
            'name' => 'Main Tendue',
            'needs_description' => 'Urgence : manteaux pour enfants avant l’hiver.',
        ]);
        Donation::factory()->for($association)->create([
            'donation_date' => now()->addDays(3)->toDateString(),
            'quantity_items' => 70,
            'status' => Donation::STATUS_PENDING,
        ]);
        $this->donation(now()->addDays(60), 10); // hors horizon : ne doit pas être envoyé

        $this->fakeAi([]);
        $this->actingAs($this->admin)->postJson(route('admin.donations.calendar.optimize'))->assertOk();

        Http::assertSent(function (Request $request) {
            $situation = json_decode($request['messages'][1]['content'], true);

            return $request['response_format']['type'] === 'json_schema'
                && $request['response_format']['json_schema']['strict'] === true
                && count($situation['donations']) === 1
                && $situation['donations'][0]['association_needs'] === 'Urgence : manteaux pour enfants avant l’hiver.'
                && count($situation['days']) === 14
                && $situation['daily_capacity'] === ['max_deliveries' => 3, 'max_items' => 300];
        });
    }

    public function test_nothing_to_plan_does_not_call_the_ai(): void
    {
        $this->donation(now()->subDays(2), 30, Donation::STATUS_DELIVERED);

        $this->actingAs($this->admin)
            ->postJson(route('admin.donations.calendar.optimize'))
            ->assertOk()
            ->assertJsonPath('proposals', []);

        Http::assertNothingSent();
    }

    public function test_accepting_a_proposal_reschedules_and_emails_the_association(): void
    {
        $overdue = $this->donation(now()->subDays(5), 60);
        $target = now()->addDays(2)->toDateString();
        $this->fakeAi([['donation_id' => $overdue->id, 'new_date' => $target, 'reason' => 'En retard.']]);

        $proposal = $this->actingAs($this->admin)->postJson(route('admin.donations.calendar.optimize'))->json('proposals.0');

        // Ce que fait le bouton « Accepter »
        $this->patchJson($proposal['reschedule_url'], ['date' => $proposal['to']])->assertOk();

        $this->assertSame($target, $overdue->refresh()->donation_date->toDateString());
        Notification::assertSentTo($overdue->association->user, \App\Notifications\DonationRescheduled::class);
    }

    public function test_ai_errors_return_a_friendly_503(): void
    {
        $this->donation(now()->addDay(), 10);
        Http::fake(['api.openai.com/*' => Http::response(['error' => ['message' => 'boom']], 500)]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.donations.calendar.optimize'))
            ->assertStatus(503)
            ->assertJsonStructure(['message']);
    }

    public function test_only_admins_can_run_the_planning_assistant(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('admin.donations.calendar.optimize'))
            ->assertForbidden();

        Http::assertNothingSent();
    }

    private function fakeAi(array $proposals): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => [
            'role' => 'assistant',
            'content' => json_encode([
                'summary' => 'Une livraison en retard à replanifier.',
                'proposals' => $proposals,
            ]),
        ]]]])]);
    }

    private function donation($date, int $quantity, string $status = Donation::STATUS_PENDING): Donation
    {
        return Donation::factory()
            ->for(Association::factory()->state(['status' => Association::STATUS_APPROVED]))
            ->create(['donation_date' => $date->toDateString(), 'quantity_items' => $quantity, 'status' => $status]);
    }
}
