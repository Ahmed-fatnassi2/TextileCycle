<?php

namespace Tests\Feature;

use App\Exceptions\AssistantUnavailableException;
use App\Models\AiUsageEvent;
use App\Models\RepairRequest;
use App\Models\User;
use App\Models\Workshop;
use App\Services\OpenAiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiUsageTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        config([
            'services.openai.key' => 'test-key',
            'services.openai.model' => 'test-model',
        ]);
    }

    public function test_real_api_requests_record_feature_and_token_usage_without_prompts(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Http::fake(['api.openai.com/*' => Http::response($this->successResponse(), 200)]);

        $message = app(OpenAiClient::class)->chat([
            '_usage_feature' => 'repair_message',
            'messages' => [['role' => 'user', 'content' => 'Contenu confidentiel du message']],
        ]);

        $this->assertSame('Bonjour !', $message['content']);
        $this->assertDatabaseHas('ai_usage_events', [
            'feature' => 'repair_message',
            'user_id' => $user->id,
            'model' => 'test-model',
            'status_code' => 200,
            'succeeded' => true,
            'input_tokens' => 17,
            'output_tokens' => 6,
        ]);
        $this->assertSame(1, AiUsageEvent::count());
        $this->assertArrayNotHasKey('content', AiUsageEvent::first()->getAttributes());

        Http::assertSent(function (Request $request) {
            return ! array_key_exists('_usage_feature', $request->data())
                && $request->hasHeader('Authorization', 'Bearer test-key');
        });
    }

    public function test_failed_api_requests_are_counted_without_storing_the_error_body(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['error' => ['message' => 'Invalid API key']], 401)]);

        try {
            app(OpenAiClient::class)->chat([
                '_usage_feature' => 'repair_assistant',
                'messages' => [['role' => 'user', 'content' => 'Question privée']],
            ]);
            $this->fail('Une réponse API en erreur doit rester une erreur.');
        } catch (AssistantUnavailableException) {
            $this->assertDatabaseHas('ai_usage_events', [
                'feature' => 'repair_assistant',
                'status_code' => 401,
                'succeeded' => false,
                'input_tokens' => null,
                'output_tokens' => null,
            ]);
        }
    }

    public function test_admin_and_workshop_dashboards_show_scoped_ai_usage(): void
    {
        $admin = User::factory()->admin()->create();
        AiUsageEvent::create([
            'feature' => 'repair_quality',
            'user_id' => $admin->id,
            'model' => 'test-model',
            'status_code' => 200,
            'succeeded' => true,
            'input_tokens' => 20,
            'output_tokens' => 10,
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Usage IA du module Réparation')
            ->assertSee('Analyse qualité')
            ->assertSee('20 jetons en entrée');

        $owner = User::factory()->workshopOwner()->create();
        Workshop::factory()->create(['owner_id' => $owner->id]);
        AiUsageEvent::create([
            'feature' => 'repair_message',
            'user_id' => $owner->id,
            'model' => 'test-model',
            'status_code' => 200,
            'succeeded' => true,
            'input_tokens' => 12,
            'output_tokens' => 8,
        ]);

        $this->actingAs($owner)->get(route('workshop-owner.dashboard'))
            ->assertOk()
            ->assertSee('Usage IA de votre compte')
            ->assertSee('1 appel(s) API')
            ->assertSee('12 jeton(s) entrée');
    }

    public function test_workshop_dashboard_summarizes_recent_repair_performance(): void
    {
        $owner = User::factory()->workshopOwner()->create();
        $workshop = Workshop::factory()->create(['owner_id' => $owner->id]);
        RepairRequest::factory()->create([
            'workshop_id' => $workshop->id,
            'status' => RepairRequest::STATUS_COLLECTED,
            'started_at' => now()->subDays(3),
            'ready_at' => now()->subDay(),
            'estimated_completion_date' => today(),
            'quality_score' => 4,
        ]);

        $this->actingAs($owner)->get(route('workshop-owner.dashboard'))
            ->assertOk()
            ->assertSee('Performance de réparation')
            ->assertSee('100%')
            ->assertSee('2 j')
            ->assertSee('4/5');
    }

    private function successResponse(): array
    {
        return [
            'choices' => [['message' => ['role' => 'assistant', 'content' => 'Bonjour !']]],
            'usage' => ['prompt_tokens' => 17, 'completion_tokens' => 6],
        ];
    }
}
