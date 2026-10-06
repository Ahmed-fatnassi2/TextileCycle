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

    public function test_past_deposit_dates_are_rejected_for_citizens_and_admins(): void
    {
        $citizen = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $point = DepositPoint::factory()->create(['state' => DepositPoint::STATE_OUVERT]);
        $yesterday = now()->subDay()->toDateString();
        $depositData = [
            'deposit_point_id' => $point->id,
            'weight_kg' => 2.5,
            'status' => Deposit::STATUS_DEPOSE,
            'state' => Deposit::STATE_BON,
            'deposit_date' => $yesterday,
        ];

        $this->actingAs($citizen)
            ->post(route('deposits.store'), $depositData)
            ->assertSessionHasErrors('deposit_date');

        $this->actingAs($admin)
            ->post(route('admin.deposits.store'), [
                ...$depositData,
                'user_id' => $citizen->id,
            ])
            ->assertSessionHasErrors('deposit_date');

        $deposit = Deposit::factory()->create([
            'user_id' => $citizen->id,
            'deposit_point_id' => $point->id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.deposits.update', $deposit), [
                ...$depositData,
                'user_id' => $citizen->id,
            ])
            ->assertSessionHasErrors('deposit_date');
    }
}