<?php

namespace Tests\Feature;

use App\Models\User;
<<<<<<< HEAD
use App\Models\Workshop;
=======
>>>>>>> origin/Donation/Association-"aziz"
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_admin_can_create_user_accounts(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Créer un utilisateur');

        $this->post(route('admin.users.store'), [
            'name' => 'Nouveau citoyen',
            'email' => 'nouveau@example.test',
            'password' => 'StrongPassword123',
            'password_confirmation' => 'StrongPassword123',
            'role' => 'user',
        ])->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Nouveau citoyen',
            'email' => 'nouveau@example.test',
            'role' => 'user',
        ]);
        $this->assertDatabaseHas('user_activities', [
            'action' => 'Compte créé',
            'description' => 'Compte créé par un administrateur.',
            'actor_id' => $admin->id,
        ]);
    }

=======
>>>>>>> origin/Donation/Association-"aziz"
    public function test_admin_can_manage_users_and_audit_role_changes(): void
    {
        $admin = User::factory()->admin()->create();
        $citizen = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee($citizen->email);

        $this->put(route('admin.users.update', $citizen), ['role' => 'admin'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $citizen->id, 'role' => 'admin']);
        $this->assertDatabaseHas('user_activities', ['user_id' => $citizen->id, 'actor_id' => $admin->id, 'action' => 'Rôle modifié']);
        $this->get(route('admin.users.show', $citizen))->assertOk()->assertSee('Journal d’activité');
    }
<<<<<<< HEAD

    public function test_admin_user_list_exposes_an_edit_action(): void
    {
        $admin = User::factory()->admin()->create();
        $citizen = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Créer un utilisateur')
            ->assertSee('Modifier')
            ->assertSee($citizen->email);
    }

    public function test_admin_user_list_filters_reject_invalid_roles(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.users.index', ['role' => 'owner']))
            ->assertSessionHasErrors('role');
    }

    public function test_admin_cannot_demote_an_owner_until_the_workshop_is_unassigned(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create(['role' => User::ROLE_WORKSHOP_OWNER]);
        Workshop::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $owner), ['role' => User::ROLE_USER])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => User::ROLE_WORKSHOP_OWNER]);
    }

    public function test_admin_dashboard_displays_recent_activity(): void
    {
        $admin = User::factory()->admin()->create();
        $citizen = User::factory()->create(['role' => 'user']);

        User::logActivity($citizen, 'Connexion', 'Connexion réussie au compte.', $citizen);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Activité récente')
            ->assertSee('Connexion réussie au compte.');
    }

    public function test_user_login_records_last_login_and_activity(): void
    {
        $user = User::factory()->create([
            'email' => 'citizen@example.com',
            'password' => 'password123',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect();

        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertDatabaseHas('user_activities', [
            'user_id' => $user->id,
            'action' => 'Connexion',
        ]);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertDatabaseHas('user_activities', [
            'user_id' => $user->id,
            'actor_id' => $user->id,
            'action' => 'Déconnexion',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect();

        $this->assertDatabaseCount('user_activities', 3);
    }
=======
>>>>>>> origin/Donation/Association-"aziz"
}
