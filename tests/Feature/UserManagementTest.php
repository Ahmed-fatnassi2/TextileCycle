<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

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
}
