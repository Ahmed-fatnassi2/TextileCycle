<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshopCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_manage_workshops_and_cascade_repairs(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.workshops.index'))->assertOk();
        $data = ['name' => 'Atelier Test Tunis', 'specialty' => 'Retouche', 'address' => '10 Avenue Habib Bourguiba, Tunis', 'phone' => '21 234 567'];
        $this->post(route('admin.workshops.store'), $data)->assertRedirect(route('admin.workshops.index'));
        $workshop = Workshop::firstWhere('name', $data['name']);
        $this->assertDatabaseHas('workshops', $data);
        $this->put(route('admin.workshops.update', $workshop), [...$data, 'specialty' => 'Couture'])->assertRedirect(route('admin.workshops.index'));
        $repair = RepairRequest::factory()->create(['workshop_id' => $workshop->id]);
        $this->delete(route('admin.workshops.destroy', $workshop))->assertRedirect(route('admin.workshops.index'));
        $this->assertDatabaseMissing('repair_requests', ['id' => $repair->id]);
    }

    public function test_invalid_workshop_is_rejected(): void
    {
        $this->actingAs($this->admin())->from(route('admin.workshops.create'))->post(route('admin.workshops.store'), [])->assertRedirect(route('admin.workshops.create'))->assertSessionHasErrors(['name', 'specialty', 'address', 'phone']);
    }
}
