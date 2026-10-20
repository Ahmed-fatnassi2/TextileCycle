<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_is_redirected_to_the_back_office_after_login(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }
<<<<<<< HEAD

    public function test_workshop_owner_is_redirected_to_their_management_space(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_WORKSHOP_OWNER,
            'email' => 'atelier@example.test',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => $owner->email,
            'password' => 'password',
        ])->assertRedirect(route('workshop-owner.dashboard'));
    }
=======
>>>>>>> origin/Donation/Association-"aziz"
}
