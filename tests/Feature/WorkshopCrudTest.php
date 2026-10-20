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

<<<<<<< HEAD
    public function test_admin_can_assign_one_owner_to_multiple_workshops(): void
    {
        $admin = $this->admin();
        $owner = User::factory()->create(['role' => User::ROLE_WORKSHOP_OWNER]);
        $this->actingAs($admin);

        $workshopData = [
            'name' => 'Atelier propriétaire Tunis',
            'specialty' => 'Couture',
            'address' => '10 Avenue Habib Bourguiba, Tunis',
            'phone' => '21 234 567',
            'owner_id' => $owner->id,
        ];

        $this->post(route('admin.workshops.store'), $workshopData)
            ->assertRedirect(route('admin.workshops.index'));

        $workshop = Workshop::where('name', $workshopData['name'])->firstOrFail();
        $this->assertDatabaseHas('workshops', ['id' => $workshop->id, 'owner_id' => $owner->id]);
        $this->assertTrue($owner->fresh()->ownedWorkshops->contains($workshop));

        $this->post(route('admin.workshops.store'), [...$workshopData, 'name' => 'Second atelier'])
            ->assertRedirect(route('admin.workshops.index'));

        $this->assertSame(2, $owner->fresh()->ownedWorkshops()->count());
    }

=======
>>>>>>> origin/Donation/Association-"aziz"
    public function test_invalid_workshop_is_rejected(): void
    {
        $this->actingAs($this->admin())->from(route('admin.workshops.create'))->post(route('admin.workshops.store'), [])->assertRedirect(route('admin.workshops.create'))->assertSessionHasErrors(['name', 'specialty', 'address', 'phone']);
    }
<<<<<<< HEAD

    public function test_admin_workshop_search_rejects_non_string_input(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.workshops.index', ['search' => ['invalid']]))
            ->assertSessionHasErrors('search');
    }

    public function test_recent_repair_requests_on_workshop_page_are_admin_only(): void
    {
        $workshop = Workshop::factory()->create();
        RepairRequest::factory()->create([
            'workshop_id' => $workshop->id,
            'item_description' => 'Private repair request description',
        ]);

        $this->get(route('workshops.show', $workshop))
            ->assertOk()
            ->assertDontSee('Demandes récentes')
            ->assertDontSee('Private repair request description');

        $this->actingAs($this->admin())
            ->get(route('workshops.show', $workshop))
            ->assertOk()
            ->assertSee('Demandes récentes')
            ->assertSee('Private repair request description');
    }
=======
>>>>>>> origin/Donation/Association-"aziz"
}
