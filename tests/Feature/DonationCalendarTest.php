<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\Donation;
use App\Models\User;
use App\Notifications\DonationRescheduled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DonationCalendarTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private const DETAILS = [
        'category' => 'Mixte',
        'description' => 'Vêtements chauds pour les familles accompagnées.',
        'delivery_address' => '15 Rue de Marseille',
        'delivery_city' => 'Tunis',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        config(['donations.daily_deliveries' => 3, 'donations.daily_items' => 300]);
        $this->admin = User::factory()->admin()->create();
    }

    public function test_calendar_page_renders_for_admins_only(): void
    {
        $this->actingAs($this->admin)->get(route('admin.donations.calendar'))
            ->assertOk()
            ->assertSee('data-donation-calendar', false);

        $this->actingAs(User::factory()->create())->get(route('admin.donations.calendar'))->assertForbidden();
    }

    public function test_events_endpoint_returns_donations_in_the_visible_range(): void
    {
        $inside = $this->donation(now()->addDays(3), 80, Donation::STATUS_VALIDATED);
        $this->donation(now()->addMonths(3), 40);
        $delivered = $this->donation(now()->subDay(), 20, Donation::STATUS_DELIVERED);

        $response = $this->actingAs($this->admin)->getJson(route('admin.donations.calendar.events', [
            'start' => now()->subWeek()->toIso8601String(),
            'end' => now()->addWeeks(2)->toIso8601String(),
        ]))->assertOk();

        $events = collect($response->json())->keyBy('id');
        $this->assertCount(2, $events);
        $this->assertSame($inside->donation_date->toDateString(), $events[(string) $inside->id]['start']);
        $this->assertSame(80, $events[(string) $inside->id]['extendedProps']['quantity']);
        $this->assertTrue($events[(string) $inside->id]['editable']);
        $this->assertFalse($events[(string) $delivered->id]['editable']);
    }

    public function test_admin_reschedules_by_drag_and_drop_and_association_is_emailed(): void
    {
        $donation = $this->donation(now()->addDays(2), 50);
        $target = now()->addDays(5)->toDateString();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.donations.reschedule', $donation), ['date' => $target])
            ->assertOk()
            ->assertJsonPath('emailSent', true);

        $this->assertSame($target, $donation->refresh()->donation_date->toDateString());

        Notification::assertSentTo($donation->association->user, DonationRescheduled::class,
            fn ($n) => $n->oldDate->isSameDay(now()->addDays(2)));
        $this->assertDatabaseHas('user_activities', [
            'user_id' => $donation->association->user_id,
            'actor_id' => $this->admin->id,
            'action' => 'Livraison de don replanifiée',
        ]);
    }

    public function test_reschedule_email_contains_both_dates(): void
    {
        $donation = $this->donation(now()->addDays(2), 50);
        $mail = (new DonationRescheduled($donation, now()->addDay()))->toMail($donation->association->user);

        $this->assertSame('Nouvelle date de livraison pour votre don', $mail->subject);
        $this->assertStringContainsString('50 pièces', implode(' ', $mail->introLines));
        $this->assertSame(route('association.donations.show', $donation), $mail->actionUrl);
    }

    public function test_a_day_with_the_maximum_number_of_deliveries_is_refused(): void
    {
        $day = now()->addDays(4);
        $this->donation($day, 10);
        $this->donation($day, 10);
        $this->donation($day, 10);
        $donation = $this->donation(now()->addDays(1), 10);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.donations.reschedule', $donation), ['date' => $day->toDateString()])
            ->assertUnprocessable()
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'complet'));

        $this->assertTrue($donation->refresh()->donation_date->isSameDay(now()->addDay()));
        Notification::assertNothingSent();
    }

    public function test_a_day_without_enough_item_capacity_is_refused(): void
    {
        $day = now()->addDays(4);
        $this->donation($day, 250);
        $donation = $this->donation(now()->addDays(1), 80);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.donations.reschedule', $donation), ['date' => $day->toDateString()])
            ->assertUnprocessable()
            ->assertJsonPath('message', fn ($m) => str_contains($m, '50 pièces'));
    }

    public function test_moving_a_donation_within_its_own_day_does_not_count_it_twice(): void
    {
        $day = now()->addDays(4);
        $this->donation($day, 100);
        $this->donation($day, 100);
        $donation = $this->donation($day, 100); // jour plein avec ce don inclus

        $this->actingAs($this->admin)
            ->patchJson(route('admin.donations.reschedule', $donation), ['date' => $day->toDateString()])
            ->assertOk();
    }

    public function test_past_dates_and_delivered_donations_cannot_be_rescheduled(): void
    {
        $pending = $this->donation(now()->addDays(2), 10);
        $delivered = $this->donation(now()->addDays(2), 10, Donation::STATUS_DELIVERED);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.donations.reschedule', $pending), ['date' => now()->subDay()->toDateString()])
            ->assertJsonValidationErrors('date');

        $this->patchJson(route('admin.donations.reschedule', $delivered), ['date' => now()->addDays(3)->toDateString()])
            ->assertUnprocessable();

        $this->assertTrue($delivered->refresh()->donation_date->isSameDay(now()->addDays(2)));
    }

    public function test_non_admins_cannot_reschedule(): void
    {
        $donation = $this->donation(now()->addDays(2), 10);

        $this->actingAs($donation->association->user)
            ->patchJson(route('admin.donations.reschedule', $donation), ['date' => now()->addDays(3)->toDateString()])
            ->assertForbidden();
    }

    public function test_admin_form_date_change_checks_capacity_and_notifies(): void
    {
        $donation = $this->donation(now()->addDays(2), 40, Donation::STATUS_VALIDATED);
        $full = now()->addDays(6);
        $this->donation($full, 300);

        $payload = fn ($date) => [
            'association_id' => $donation->association_id,
            'quantity_items' => 40,
            'category' => 'Enfants',
                'description' => 'Pulls et manteaux pour enfants de 6 à 10 ans.',
                'delivery_address' => '15 Rue de Marseille',
                'delivery_city' => 'Tunis',
                'donation_date' => $date,
            'status' => Donation::STATUS_VALIDATED,
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.donations.update', $donation), $payload($full->toDateString()))
            ->assertSessionHasErrors('donation_date');
        Notification::assertNothingSent();

        $this->put(route('admin.donations.update', $donation), $payload(now()->addDays(8)->toDateString()))
            ->assertRedirect(route('admin.donations.show', $donation));
        Notification::assertSentTo($donation->association->user, DonationRescheduled::class);
    }

    public function test_changing_only_the_status_in_the_admin_form_sends_no_email(): void
    {
        $donation = $this->donation(now()->addDays(2), 40);

        $this->actingAs($this->admin)->put(route('admin.donations.update', $donation), [
            'association_id' => $donation->association_id,
            'quantity_items' => 40,
            'donation_date' => $donation->donation_date->toDateString(),
            'status' => Donation::STATUS_VALIDATED,
        ])->assertRedirect();

        Notification::assertNothingSent();
    }

    public function test_associations_cannot_request_a_full_day(): void
    {
        $user = User::factory()->create();
        Association::factory()->for($user)->create(['status' => Association::STATUS_APPROVED]);
        $full = now()->addDays(3);
        $this->donation($full, 280);

        $this->actingAs($user)->post(route('association.donations.store'), [
            'quantity_items' => 40,
            'donation_date' => $full->toDateString(),
            ...self::DETAILS,
        ])->assertSessionHasErrors('donation_date');

        $this->post(route('association.donations.store'), [
            'quantity_items' => 20,
            'donation_date' => $full->toDateString(),
            ...self::DETAILS,
        ])->assertRedirect(route('association.donations.index'));
    }

    private function donation($date, int $quantity, string $status = Donation::STATUS_PENDING): Donation
    {
        return Donation::factory()
            ->for(Association::factory()->state(['status' => Association::STATUS_APPROVED]))
            ->create(['donation_date' => $date->toDateString(), 'quantity_items' => $quantity, 'status' => $status]);
    }
}
