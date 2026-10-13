<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopOwnerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_workshop_owner_can_manage_only_their_workshop_repairs_through_customer_pickup(): void
    {
        $owner = User::factory()->create(['role' => 'workshop_owner']);
        $otherOwner = User::factory()->create(['role' => 'workshop_owner']);
        $customer = User::factory()->create();
        $workshop = Workshop::factory()->create(['owner_id' => $owner->id]);
        $secondWorkshop = Workshop::factory()->create(['owner_id' => $owner->id]);
        $otherWorkshop = Workshop::factory()->create(['owner_id' => $otherOwner->id]);
        $repair = RepairRequest::factory()->create([
            'user_id' => $customer->id,
            'workshop_id' => $workshop->id,
            'status' => RepairRequest::STATUS_PENDING,
            'item_description' => 'Veste client de mon atelier',
        ]);
        $secondRepair = RepairRequest::factory()->create([
            'workshop_id' => $secondWorkshop->id,
            'status' => RepairRequest::STATUS_PENDING,
            'item_description' => 'Jupe client de mon second atelier',
        ]);
        $otherRepair = RepairRequest::factory()->create([
            'workshop_id' => $otherWorkshop->id,
            'status' => RepairRequest::STATUS_PENDING,
            'item_description' => 'Pantalon client autre atelier',
        ]);

        $this->actingAs($owner)
            ->get(route('workshop-owner.repairs.index'))
            ->assertOk()
            ->assertSee($repair->item_description)
            ->assertSee($secondRepair->item_description)
            ->assertDontSee($otherRepair->item_description);

        $this->get(route('workshop-owner.repairs.show', $otherRepair))->assertNotFound();

        $this->put(route('workshop-owner.repairs.quote', $repair), [
            'estimated_cost' => 45.50,
            'estimated_completion_date' => now()->addWeek()->toDateString(),
        ])->assertSessionHas('success');
        $this->assertDatabaseHas('repair_requests', [
            'id' => $repair->id,
            'status' => RepairRequest::STATUS_QUOTE_PROPOSED,
            'estimated_cost' => 45.50,
        ]);

        $this->actingAs($customer)
            ->post(route('repairs.quote', $repair), ['decision' => 'accept'])
            ->assertRedirect();

        $this->actingAs($owner)
            ->patch(route('workshop-owner.repairs.status', $repair), [
                'status' => RepairRequest::STATUS_IN_PROGRESS,
            ])->assertSessionHas('success');

        $this->patch(route('workshop-owner.repairs.status', $repair), [
            'status' => RepairRequest::STATUS_READY_FOR_PICKUP,
            'cost' => 42.00,
        ])->assertSessionHas('success');

        $this->actingAs($customer)
            ->post(route('repairs.pickup', $repair))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repair->id,
            'status' => RepairRequest::STATUS_COLLECTED,
            'cost' => 42.00,
        ]);
    }

    public function test_non_owner_cannot_access_the_workshop_management_portal(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('workshop-owner.dashboard'))
            ->assertForbidden();
    }

    public function test_unassigned_owner_is_told_to_contact_admin(): void
    {
        $owner = User::factory()->create(['role' => 'workshop_owner']);

        $this->actingAs($owner)
            ->get(route('workshop-owner.dashboard'))
            ->assertOk()
            ->assertSee('Aucun atelier affecté');
    }

    public function test_workshop_owner_cannot_access_admin_back_office(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_WORKSHOP_OWNER]);

        $this->actingAs($owner)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_workshop_owner_cannot_submit_customer_repair_requests(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_WORKSHOP_OWNER]);

        $this->actingAs($owner)
            ->get(route('repairs.create'))
            ->assertForbidden();
    }
}