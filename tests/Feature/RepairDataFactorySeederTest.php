<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\Workshop;
use Database\Seeders\RepairRequestSeeder;
use Database\Seeders\WorkshopSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairDataFactorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_workshop_and_repair_factories_create_related_realistic_data(): void
    {
        $workshop = Workshop::factory()->create();
        $repairRequest = RepairRequest::factory()->for($workshop)->create();

        $this->assertMatchesRegularExpression('/^2\d \d{3} \d{3}$/', $workshop->phone);
        $this->assertSame($workshop->id, $repairRequest->workshop_id);
        $this->assertContains($repairRequest->problem_type, RepairRequest::problemTypes());
    }

    public function test_workshop_and_repair_seeders_are_idempotent(): void
    {
        $this->seed(WorkshopSeeder::class);
        $this->assertDatabaseCount('workshops', 6);

        $this->seed(RepairRequestSeeder::class);
        $this->assertDatabaseCount('repair_requests', 30);

        $this->seed(RepairRequestSeeder::class);
        $this->assertDatabaseCount('workshops', 6);
        $this->assertDatabaseCount('repair_requests', 30);
    }
}
