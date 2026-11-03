<?php

namespace Tests\Feature;

use App\Models\RepairPhoto;
use App\Models\RepairRequest;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RepairPhotoAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_can_view_their_repair_photo_but_not_another_citizens_photo(): void
    {
        Storage::fake('public');
        [$repair, $photo] = $this->makePhoto(User::factory()->create());
        $otherRepair = RepairRequest::factory()->create();
        $otherPhoto = $otherRepair->photos()->create(['path' => 'repair-photos/other.jpg', 'type' => RepairPhoto::TYPE_BEFORE]);
        Storage::disk('public')->put($photo->path, 'my-photo');
        Storage::disk('public')->put($otherPhoto->path, 'other-photo');

        $this->actingAs($repair->user)
            ->get(route('repairs.photos.show', [$repair, $photo]))
            ->assertOk()
            ->assertStreamedContent('my-photo');

        $this->get(route('repairs.photos.show', [$otherRepair, $otherPhoto]))->assertNotFound();
    }

    public function test_workshop_owner_can_view_photo_only_for_their_own_workshop(): void
    {
        Storage::fake('public');
        $owner = User::factory()->workshopOwner()->create();
        $repair = RepairRequest::factory()->create(['workshop_id' => Workshop::factory()->create(['owner_id' => $owner->id])->id]);
        $photo = $repair->photos()->create(['path' => 'repair-photos/owner.jpg', 'type' => RepairPhoto::TYPE_BEFORE]);
        Storage::disk('public')->put($photo->path, 'owner-photo');
        $this->actingAs($owner)->get(route('workshop-owner.repairs.photos.show', [$repair, $photo]))->assertOk()->assertStreamedContent('owner-photo');

        $otherOwner = User::factory()->workshopOwner()->create();
        $this->actingAs($otherOwner)->get(route('workshop-owner.repairs.photos.show', [$repair, $photo]))->assertNotFound();
    }

    private function makePhoto(User $citizen): array
    {
        $repair = RepairRequest::factory()->create(['user_id' => $citizen->id]);
        $photo = $repair->photos()->create(['path' => 'repair-photos/mine.jpg', 'type' => RepairPhoto::TYPE_BEFORE]);
        Storage::disk('public')->put($photo->path, 'my-photo');

        return [$repair, $photo];
    }
}
