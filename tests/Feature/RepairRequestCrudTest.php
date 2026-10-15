<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairRequestCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_repair_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $workshop = Workshop::factory()->create();
        $citizen = User::factory()->create();
        $data = ['user_id' => $citizen->id, 'item_description' => 'Veste en jean avec fermeture cassée', 'problem_type' => RepairRequest::PROBLEM_ZIPPER, 'cost' => 25, 'estimated_cost' => 25, 'estimated_completion_date' => now()->addWeek()->toDateString(), 'status' => RepairRequest::STATUS_PENDING, 'workshop_id' => $workshop->id];
        $this->actingAs($admin)->get(route('admin.repair-requests.index'))->assertOk();
        $this->post(route('admin.repair-requests.store'), $data)->assertRedirect(route('admin.repair-requests.index'));
        $repair = RepairRequest::first();
        $this->put(route('admin.repair-requests.update', $repair), [...$data, 'status' => RepairRequest::STATUS_REPAIRED, 'cost' => 30])->assertRedirect(route('admin.repair-requests.index'));
        $this->assertDatabaseHas('repair_requests', ['id' => $repair->id, 'status' => RepairRequest::STATUS_REPAIRED]);
        $this->delete(route('admin.repair-requests.destroy', $repair))->assertRedirect(route('admin.repair-requests.index'));
        $this->assertDatabaseMissing('repair_requests', ['id' => $repair->id]);
    }

    public function test_invalid_repair_request_is_rejected(): void
    {
        $this->actingAs(User::factory()->admin()->create())->from(route('admin.repair-requests.create'))->post(route('admin.repair-requests.store'), [])->assertRedirect(route('admin.repair-requests.create'))->assertSessionHasErrors(['user_id', 'item_description', 'problem_type', 'cost', 'status', 'workshop_id']);
    }
}
