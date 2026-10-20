<?php

namespace Tests\Feature;

<<<<<<< HEAD
=======
use App\Mail\AdminModuleActivityNotification;
use App\Mail\AssociationRequestConfirmation;
use App\Mail\AssociationStatusNotification;
use App\Mail\DonationStatusNotification;
>>>>>>> origin/Donation/Association-"aziz"
use App\Models\Association;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
>>>>>>> origin/Donation/Association-"aziz"
use Tests\TestCase;

class AssociationDonationTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_association_delete_dialog_is_closed_on_page_load(): void
    {
        $admin = User::factory()->admin()->create();
        $association = Association::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.associations.show', $association));

        $response->assertOk()->assertSee('association-delete-dialog');
        $this->assertDoesNotMatchRegularExpression('/<dialog\b[^>]*\bopen(?:\s|>)/', $response->getContent());
    }

=======
>>>>>>> origin/Donation/Association-"aziz"
    public function test_member_can_request_an_association_profile_pending_review(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Samira Test',
            'email' => 'samira@example.test',
            'password' => 'StrongPassword123',
            'password_confirmation' => 'StrongPassword123',
        ]);

        $response->assertRedirect(route('associations.create'));
        $user = User::where('email', 'samira@example.test')->firstOrFail();

        $this->post(route('associations.store'), [
            'name' => 'Main Tendue',
            'contact_person' => 'Samira Test',
            'phone' => '+212600000001',
            'needs_description' => 'Vêtements chauds pour les familles accompagnées.',
            'status' => Association::STATUS_APPROVED,
        ])->assertRedirect(route('association.status'));

        $this->assertDatabaseHas('associations', [
            'user_id' => $user->id,
            'name' => 'Main Tendue',
            'status' => Association::STATUS_PENDING,
        ]);
    }

    public function test_admin_can_approve_an_association(): void
    {
        $admin = User::factory()->admin()->create();
        $association = Association::factory()->create(['status' => Association::STATUS_PENDING]);

        $this->actingAs($admin)->put(route('admin.associations.update', $association), [
            'name' => $association->name,
            'contact_person' => $association->contact_person,
            'phone' => $association->phone,
            'needs_description' => $association->needs_description,
            'status' => Association::STATUS_APPROVED,
        ])->assertRedirect(route('admin.associations.show', $association));

        $this->assertDatabaseHas('associations', [
            'id' => $association->id,
            'status' => Association::STATUS_APPROVED,
        ]);
    }

    public function test_member_donation_is_linked_to_own_approved_association(): void
    {
        $user = User::factory()->create();
        $association = Association::factory()->for($user)->create(['status' => Association::STATUS_APPROVED]);
        $otherAssociation = Association::factory()->create(['status' => Association::STATUS_APPROVED]);

        $this->actingAs($user)->post(route('association.donations.store'), [
            'quantity_items' => 42,
            'donation_date' => now()->addDay()->toDateString(),
            'association_id' => $otherAssociation->id,
            'status' => Donation::STATUS_DELIVERED,
        ])->assertRedirect(route('association.donations.index'));

        $this->assertDatabaseHas('donations', [
            'association_id' => $association->id,
            'quantity_items' => 42,
            'status' => Donation::STATUS_PENDING,
        ]);
        $this->assertDatabaseMissing('donations', ['association_id' => $otherAssociation->id]);
    }

    public function test_association_must_be_approved_before_accessing_donations(): void
    {
        $user = User::factory()->create();
        Association::factory()->for($user)->create(['status' => Association::STATUS_PENDING]);

        $this->actingAs($user)
            ->get(route('association.donations.create'))
            ->assertForbidden();
    }

    public function test_only_admin_can_access_back_office_module(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.associations.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_from_back_office(): void
    {
        $this->get(route('admin.associations.index'))
            ->assertRedirect(route('login'));
    }

    public function test_member_and_admin_module_pages_render(): void
    {
        $user = User::factory()->create();
        $association = Association::factory()->for($user)->create(['status' => Association::STATUS_APPROVED]);
        $donation = Donation::factory()->for($association)->create(['status' => Donation::STATUS_PENDING]);

        $this->actingAs($user);
        foreach ([
            route('association.status'),
            route('association.donations.index'),
            route('association.donations.create'),
            route('association.donations.show', $donation),
            route('association.donations.edit', $donation),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_module_pages_render(): void
    {
        $admin = User::factory()->admin()->create();
        $association = Association::factory()->create(['status' => Association::STATUS_APPROVED]);
        $donation = Donation::factory()->for($association)->create();
        User::factory()->create();
        $this->actingAs($admin);

        foreach ([
            route('admin.dashboard'),
            route('admin.associations.index'),
            route('admin.associations.create'),
            route('admin.associations.show', $association),
            route('admin.associations.edit', $association),
            route('admin.donations.index'),
            route('admin.donations.create'),
            route('admin.donations.show', $donation),
            route('admin.donations.edit', $donation),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }
<<<<<<< HEAD
}
=======

    public function test_validated_donation_has_qr_tracking_and_history(): void
    {
        config(['donation.qr_base_url' => 'http://192.168.1.18:8000']);
        $user = User::factory()->create();
        $association = Association::factory()->for($user)->create(['status' => Association::STATUS_APPROVED]);
        $donation = Donation::factory()->for($association)->create([
            'category' => Donation::CATEGORY_SHOES,
            'status' => Donation::STATUS_VALIDATED,
        ]);

        $qrResponse = $this->actingAs($user)
            ->get(route('association.donations.qr', $donation))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertSee('<svg', false);
        $this->get(route('association.donations.qr', $donation))->assertContent($qrResponse->getContent());
        $phoneTrackingUrl = 'http://192.168.1.18:8000'.route('donations.track', $donation->tracking_token, false);
        $this->assertTrue(Cache::has('donation-qr:'.hash('sha256', $phoneTrackingUrl)));

        $this->get(route('donations.track', $donation->tracking_token))
            ->assertOk()
            ->assertSee($donation->trackingReference())
            ->assertSee(Donation::CATEGORY_SHOES)
            ->assertSee('Historique');

        $this->assertDatabaseHas('donation_status_histories', [
            'donation_id' => $donation->id,
            'to_status' => Donation::STATUS_VALIDATED,
        ]);
        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => Donation::STATUS_VALIDATED,
        ]);
    }

    public function test_qr_tracking_page_is_read_only_and_does_not_require_login(): void
    {
        $association = Association::factory()->create(['status' => Association::STATUS_APPROVED]);
        $donation = Donation::factory()->for($association)->create(['status' => Donation::STATUS_VALIDATED]);
        $otherUser = User::factory()->create();

        $this->get(route('donations.track', $donation->tracking_token))
            ->assertOk()
            ->assertSee($donation->trackingReference())
            ->assertDontSee('Confirmer la réception')
            ->assertDontSee('Retour au détail');

        $this->actingAs($otherUser)->post(route('donations.received', $donation))
            ->assertForbidden();

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => Donation::STATUS_VALIDATED,
        ]);
    }

    public function test_association_can_confirm_a_validated_donation_as_received(): void
    {
        $user = User::factory()->create();
        $association = Association::factory()->for($user)->create(['status' => Association::STATUS_APPROVED]);
        $donation = Donation::factory()->for($association)->create(['status' => Donation::STATUS_VALIDATED]);

        $this->actingAs($user)
            ->post(route('donations.received', $donation))
            ->assertRedirect(route('donations.track', $donation->tracking_token));

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => Donation::STATUS_DELIVERED,
        ]);
        $this->assertDatabaseHas('donation_status_histories', [
            'donation_id' => $donation->id,
            'from_status' => Donation::STATUS_VALIDATED,
            'to_status' => Donation::STATUS_DELIVERED,
            'changed_by' => $user->id,
        ]);
    }

    public function test_donation_submission_and_each_status_change_queue_one_email(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $association = Association::factory()->for($user)->create(['status' => Association::STATUS_APPROVED]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($user)->post(route('association.donations.store'), [
            'category' => Donation::CATEGORY_CLOTHING,
            'quantity_items' => 12,
            'donation_date' => now()->addDay()->toDateString(),
        ])->assertRedirect(route('association.donations.index'));

        $donation = $association->donations()->firstOrFail();
        $this->actingAs($admin)->put(route('admin.donations.update', $donation), [
            'association_id' => $association->id,
            'category' => Donation::CATEGORY_CLOTHING,
            'quantity_items' => $donation->quantity_items,
            'donation_date' => $donation->donation_date->toDateString(),
            'status' => Donation::STATUS_VALIDATED,
        ])->assertRedirect(route('admin.donations.show', $donation));

        $this->put(route('admin.donations.update', $donation), [
            'association_id' => $association->id,
            'category' => Donation::CATEGORY_CLOTHING,
            'quantity_items' => $donation->quantity_items,
            'donation_date' => $donation->donation_date->toDateString(),
            'status' => Donation::STATUS_VALIDATED,
        ])->assertRedirect(route('admin.donations.show', $donation));

        Mail::assertQueued(DonationStatusNotification::class, 2);
        Mail::assertQueued(AdminModuleActivityNotification::class, 2);
        Mail::assertQueued(
            AdminModuleActivityNotification::class,
            fn (AdminModuleActivityNotification $mail) => $mail->activity === 'Statut de don modifié'
                && $mail->status === Donation::STATUS_VALIDATED,
        );
        Mail::assertQueued(
            DonationStatusNotification::class,
            fn (DonationStatusNotification $mail) => $mail->status === Donation::STATUS_PENDING
                && str_contains($mail->render(), $mail->trackingUrl),
        );
        Mail::assertQueued(
            DonationStatusNotification::class,
            fn (DonationStatusNotification $mail) => $mail->status === Donation::STATUS_VALIDATED
                && str_contains($mail->render(), $mail->trackingUrl),
        );
    }

    public function test_association_request_and_decision_queue_confirmation_emails(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        User::factory()->admin()->create();

        $this->actingAs($user)->post(route('associations.store'), [
            'name' => 'Association Fil Solidaire',
            'contact_person' => 'Amel Ben Salem',
            'phone' => '+216 20 123 456',
            'needs_description' => 'Des vêtements pour les familles accompagnées.',
        ])->assertRedirect(route('association.status'));

        $association = $user->association()->firstOrFail();
        Mail::assertQueued(AssociationRequestConfirmation::class, fn (AssociationRequestConfirmation $mail) => str_contains($mail->render(), $association->name));
        Mail::assertQueued(AdminModuleActivityNotification::class, fn (AdminModuleActivityNotification $mail) => $mail->activity === 'Nouvelle demande d’association');

        $this->actingAs(User::factory()->admin()->create())->put(route('admin.associations.update', $association), [
            'name' => $association->name,
            'contact_person' => $association->contact_person,
            'phone' => $association->phone,
            'needs_description' => $association->needs_description,
            'status' => Association::STATUS_APPROVED,
        ])->assertRedirect(route('admin.associations.show', $association));

        Mail::assertQueued(AssociationStatusNotification::class, fn (AssociationStatusNotification $mail) => str_contains($mail->render(), Association::STATUS_APPROVED));
    }
}
>>>>>>> origin/Donation/Association-"aziz"
