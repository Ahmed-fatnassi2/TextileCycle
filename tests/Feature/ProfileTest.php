<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_and_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee('Mon profil');

        $this->put(route('profile.update'), [
            'name' => 'Nouveau Nom',
            'email' => 'nouveau@example.test',
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nouveau Nom', 'email' => 'nouveau@example.test']);
    }
}
