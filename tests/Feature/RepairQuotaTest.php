<?php

namespace Tests\Feature;

use App\Exceptions\AiQuotaExceededException;
use App\Services\AiQuota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RepairQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_quota_is_keyed_by_user_and_feature(): void
    {
        Cache::flush();
        config(['ai.daily_limits.message' => 1, 'ai.global_daily_limit' => 10]);
        $quota = app(AiQuota::class);
        $user = \App\Models\User::factory()->create();

        $this->assertSame(0, $quota->consume('message', $user, '127.0.0.1'));
        try {
            $quota->consume('message', $user, '127.0.0.1');
            $this->fail('Le quota devait être dépassé.');
        } catch (AiQuotaExceededException $exception) {
            $this->assertSame(0, $exception->remaining);
        }
    }
}
