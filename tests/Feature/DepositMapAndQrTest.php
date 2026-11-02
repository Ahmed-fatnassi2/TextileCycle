<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\DepositPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositMapAndQrTest extends TestCase
{
    use RefreshDatabase;

    // ---------- Carte / géolocalisation ----------

    public function test_points_are_sorted_by_distance_from_the_user(): void
    {
        $sfax = DepositPoint::factory()->create(['name' => 'Sfax', 'latitude' => 34.7400, 'longitude' => 10.7600, 'state' => 'Ouvert']);
        $tunis = DepositPoint::factory()->create(['name' => 'Tunis', 'latitude' => 36.7998, 'longitude' => 10.1802, 'state' => 'Ouvert']);
        $sousse = DepositPoint::factory()->create(['name' => 'Sousse', 'latitude' => 35.8256, 'longitude' => 10.6360, 'state' => 'Ouvert']);
        $noCoords = DepositPoint::factory()->withoutCoordinates()->create(['name' => 'Sans GPS', 'state' => 'Ouvert']);

        // Utilisateur à La Marsa
        $response = $this->get(route('deposits.index', ['lat' => 36.8782, 'lng' => 10.3247]))->assertOk();

        $points = $response->viewData('points');
        $this->assertSame([$tunis->id, $sousse->id, $sfax->id, $noCoords->id], $points->pluck('id')->all());
        $this->assertSame($tunis->id, $response->viewData('nearestOpen')->id);
        $this->assertEqualsWithDelta(15.3, $points->first()->distance_km, 1.0);
    }

    public function test_nearest_suggestion_skips_points_that_do_not_accept_deposits(): void
    {
        DepositPoint::factory()->create(['latitude' => 36.80, 'longitude' => 10.18, 'state' => DepositPoint::STATE_PLEIN]);
        $open = DepositPoint::factory()->create(['latitude' => 35.82, 'longitude' => 10.63, 'state' => DepositPoint::STATE_OUVERT]);

        $this->get(route('deposits.index', ['lat' => 36.80, 'lng' => 10.18]))
            ->assertOk()
            ->assertViewHas('nearestOpen', fn ($p) => $p->id === $open->id);
    }

    public function test_invalid_position_is_rejected(): void
    {
        $this->get(route('deposits.index', ['lat' => 200, 'lng' => 10]))->assertSessionHasErrors('lat');
    }

    public function test_map_only_receives_points_with_coordinates(): void
    {
        DepositPoint::factory()->create(['state' => 'Ouvert']);
        DepositPoint::factory()->withoutCoordinates()->create(['state' => 'Ouvert']);

        $this->get(route('deposits.index'))
            ->assertOk()
            ->assertViewHas('mapPoints', fn ($points) => $points->count() === 1);
    }

    public function test_admin_can_save_coordinates_and_both_are_required_together(): void
    {
        $admin = User::factory()->admin()->create();
        $data = ['name' => 'Point GPS', 'address' => '1 Rue', 'city' => 'Tunis', 'capacity' => 100, 'state' => 'Ouvert'];

        $this->actingAs($admin)
            ->post(route('admin.deposit-points.store'), [...$data, 'latitude' => 36.8, 'longitude' => 10.18])
            ->assertRedirect(route('admin.deposit-points.index'));
        $this->assertDatabaseHas('deposit_points', ['name' => 'Point GPS', 'latitude' => 36.8, 'longitude' => 10.18]);

        $this->post(route('admin.deposit-points.store'), [...$data, 'name' => 'Point incomplet', 'latitude' => 36.8])
            ->assertSessionHasErrors('longitude');
    }

    public function test_deposit_form_preselects_the_point_chosen_on_the_map(): void
    {
        $user = User::factory()->create();
        $point = DepositPoint::factory()->create(['state' => 'Ouvert']);

        $this->actingAs($user)
            ->get(route('deposits.create', ['point' => $point->id]))
            ->assertOk()
            ->assertViewHas('selectedPointId', $point->id);
    }

    // ---------- QR code ----------

    public function test_citizen_deposit_is_pending_with_a_code_even_if_status_is_forged(): void
    {
        $user = User::factory()->create();
        $point = DepositPoint::factory()->create(['state' => 'Ouvert']);

        $this->actingAs($user)->post(route('deposits.store'), [
            'deposit_point_id' => $point->id,
            'weight_kg' => 3,
            'state' => Deposit::STATE_BON,
            'deposit_date' => now()->toDateString(),
            'status' => Deposit::STATUS_TRIE, // tentative de fraude : ignorée
        ]);

        $deposit = Deposit::sole();
        $this->assertSame(Deposit::STATUS_EN_ATTENTE, $deposit->status);
        $this->assertMatchesRegularExpression('/^TC-[A-HJ-NP-Z2-9]{8}$/', $deposit->code);
    }

    public function test_citizen_cannot_book_on_a_point_that_is_not_open(): void
    {
        $user = User::factory()->create();
        $full = DepositPoint::factory()->create(['state' => DepositPoint::STATE_PLEIN]);

        $this->actingAs($user)->post(route('deposits.store'), [
            'deposit_point_id' => $full->id,
            'weight_kg' => 3,
            'state' => Deposit::STATE_BON,
            'deposit_date' => now()->toDateString(),
        ])->assertSessionHasErrors('deposit_point_id');
    }

    public function test_citizen_sees_own_qr_code_but_not_someone_elses(): void
    {
        $owner = User::factory()->create();
        $deposit = $this->pendingDeposit($owner);

        $this->actingAs($owner)->get(route('deposits.mine.show', $deposit))
            ->assertOk()
            ->assertSee('<svg', false)
            ->assertSee($deposit->code);

        $this->actingAs($owner)->get(route('deposits.mine.qr', $deposit))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');

        $this->actingAs(User::factory()->create())
            ->get(route('deposits.mine.show', $deposit))
            ->assertNotFound();
    }

    public function test_my_deposits_lists_only_the_citizens_deposits(): void
    {
        $user = User::factory()->create();
        $mine = $this->pendingDeposit($user);
        $other = $this->pendingDeposit(User::factory()->create());

        $this->actingAs($user)->get(route('deposits.mine'))
            ->assertOk()
            ->assertSee($mine->code)
            ->assertDontSee($other->code);
    }

    public function test_admin_validates_a_deposit_by_scanning_its_qr_url(): void
    {
        $admin = User::factory()->admin()->create();
        $deposit = $this->pendingDeposit();

        // Le QR contient l'URL complète : la recherche doit en extraire le code
        $this->actingAs($admin)
            ->get(route('admin.deposits.lookup', ['code' => route('admin.deposits.checkin', $deposit->code)]))
            ->assertRedirect(route('admin.deposits.checkin', $deposit->code));

        $this->get(route('admin.deposits.checkin', $deposit->code))->assertOk()->assertSee($deposit->code);

        $this->post(route('admin.deposits.checkin.confirm', $deposit->code), [
            'decision' => 'accept',
            'weight_kg' => 5.4,
            'state' => Deposit::STATE_USE,
        ])->assertRedirect(route('admin.deposits.scan'));

        $deposit->refresh();
        $this->assertSame(Deposit::STATUS_DEPOSE, $deposit->status);
        $this->assertEquals(5.4, $deposit->weight_kg);
        $this->assertSame(Deposit::STATE_USE, $deposit->state);
        $this->assertSame($admin->id, $deposit->checked_in_by);
        $this->assertNotNull($deposit->checked_in_at);
        $this->assertDatabaseHas('user_activities', ['user_id' => $deposit->user_id, 'actor_id' => $admin->id, 'action' => 'Dépôt validé']);
    }

    public function test_manual_code_lookup_is_case_insensitive_and_unknown_codes_are_reported(): void
    {
        $admin = User::factory()->admin()->create();
        $deposit = $this->pendingDeposit();

        $this->actingAs($admin)
            ->get(route('admin.deposits.lookup', ['code' => ' '.strtolower($deposit->code).' ']))
            ->assertRedirect(route('admin.deposits.checkin', $deposit->code));

        $this->get(route('admin.deposits.lookup', ['code' => 'TC-INCONNU']))
            ->assertRedirect(route('admin.deposits.scan'))
            ->assertSessionHas('error');
    }

    public function test_admin_can_reject_a_deposit(): void
    {
        $admin = User::factory()->admin()->create();
        $deposit = $this->pendingDeposit();

        $this->actingAs($admin)->post(route('admin.deposits.checkin.confirm', $deposit->code), [
            'decision' => 'reject',
            'weight_kg' => 2,
            'state' => Deposit::STATE_DECHIRE,
        ]);

        $this->assertSame(Deposit::STATUS_REJETE, $deposit->refresh()->status);
    }

    public function test_a_qr_code_cannot_be_used_twice(): void
    {
        $admin = User::factory()->admin()->create();
        $deposit = $this->pendingDeposit();
        $payload = ['decision' => 'accept', 'weight_kg' => 3, 'state' => Deposit::STATE_BON];

        $this->actingAs($admin)->post(route('admin.deposits.checkin.confirm', $deposit->code), $payload);
        $firstCheckIn = $deposit->refresh()->checked_in_at;

        $this->travel(5)->minutes();

        $this->post(route('admin.deposits.checkin.confirm', $deposit->code), [...$payload, 'decision' => 'reject'])
            ->assertRedirect(route('admin.deposits.checkin', $deposit->code))
            ->assertSessionHas('error');

        $deposit->refresh();
        $this->assertSame(Deposit::STATUS_DEPOSE, $deposit->status);
        $this->assertEquals($firstCheckIn, $deposit->checked_in_at);
    }

    public function test_only_admins_can_check_in_deposits(): void
    {
        $deposit = $this->pendingDeposit();

        $this->actingAs($deposit->user)
            ->post(route('admin.deposits.checkin.confirm', $deposit->code), [
                'decision' => 'accept', 'weight_kg' => 3, 'state' => Deposit::STATE_BON,
            ])
            ->assertForbidden();

        $this->assertSame(Deposit::STATUS_EN_ATTENTE, $deposit->refresh()->status);
    }

    public function test_admin_can_search_deposits_by_code(): void
    {
        $admin = User::factory()->admin()->create();
        $wanted = $this->pendingDeposit();
        $other = $this->pendingDeposit();

        $this->actingAs($admin)
            ->get(route('admin.deposits.index', ['search' => $wanted->code]))
            ->assertOk()
            ->assertSee($wanted->code)
            ->assertDontSee($other->code);
    }

    private function pendingDeposit(?User $user = null): Deposit
    {
        return Deposit::factory()->create([
            'user_id' => ($user ?? User::factory()->create())->id,
            'status' => Deposit::STATUS_EN_ATTENTE,
        ]);
    }
}
