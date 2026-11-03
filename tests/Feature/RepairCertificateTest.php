<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairCertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_can_print_a_certificate_for_their_completed_repair(): void
    {
        $citizen = User::factory()->create();
        $repair = RepairRequest::factory()->create([
            'user_id' => $citizen->id,
            'status' => RepairRequest::STATUS_COLLECTED,
            'co2_saved_kg' => 3.5,
            'waste_saved_kg' => 0.3,
            'ready_at' => now()->subDay(),
        ]);

        $this->actingAs($citizen)->get(route('repairs.certificate', $repair))
            ->assertOk()
            ->assertSee('Certificat de réparation responsable')
            ->assertSee('3,50 kg CO₂e')
            ->assertSee('0,30 kg');
    }

    public function test_citizen_cannot_print_another_users_repair_certificate(): void
    {
        $repair = RepairRequest::factory()->create([
            'status' => RepairRequest::STATUS_COLLECTED,
            'co2_saved_kg' => 3.5,
            'waste_saved_kg' => 0.3,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('repairs.certificate', $repair))
            ->assertNotFound();
    }

    public function test_certificate_is_unavailable_until_repair_and_impact_are_recorded(): void
    {
        $citizen = User::factory()->create();
        $repair = RepairRequest::factory()->create([
            'user_id' => $citizen->id,
            'status' => RepairRequest::STATUS_IN_PROGRESS,
            'co2_saved_kg' => null,
            'waste_saved_kg' => null,
        ]);

        $this->actingAs($citizen)
            ->get(route('repairs.certificate', $repair))
            ->assertNotFound();
    }
}
