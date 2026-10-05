<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\DepositPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_and_deposit_uses_authenticated_user(): void
    {
        $this->get(route('deposits.create'))->assertRedirect(route('login'));

        $user = User::factory()->create();
        $point = DepositPoint::factory()->create(['state' => DepositPoint::STATE_OUVERT]);

        $this->actingAs($user)->post(route('deposits.store'), [
            'deposit_point_id' => $point->id,
            'weight_kg' => 3.5,
            'status' => Deposit::STATUS_DEPOSE,
            'state' => Deposit::STATE_BON,
            'deposit_date' => now()->toDateString(),
        ])->assertRedirect(route('deposits.index'));

        $this->assertDatabaseHas('deposits', [
            'user_id' => $user->id,
            'deposit_point_id' => $point->id,
            'weight_kg' => 3.5,
        ]);
    }
}