<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssociationDonationTest extends TestCase
{
    use RefreshDatabase;

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
}