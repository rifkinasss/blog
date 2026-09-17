<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Livewire\Dashboard\Automation\OpenClawIndex;
use App\Models\ApiToken;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\ServiceAccount;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OpenClawApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_can_create_only_a_draft_and_the_origin_is_recorded(): void
    {
        [$service, $token] = $this->serviceToken(['articles:create']);

        $response = $this->withToken($token)->postJson('/api/v1/articles', [
            'title' => 'Automated note',
            'content' => '# Hello',
            'status' => 'published',
        ])->assertCreated();

        $article = Article::findOrFail($response->json('data.id'));
        $this->assertSame(ArticleStatus::Draft, $article->status);
        $this->assertSame($service->id, $article->origin_service_account_id);
        $this->assertDatabaseHas('audit_logs', ['event' => 'article.created_via_api', 'actor_type' => 'service', 'actor_name' => 'OpenClaw']);
        $this->assertNotNull($service->apiTokens()->first()->fresh()->last_used_at);
    }

    public function test_service_article_creation_is_idempotent_when_a_key_is_provided(): void
    {
        [, $token] = $this->serviceToken(['articles:create']);
        $payload = ['title' => 'Idempotent note', 'content' => '# One operation'];

        $first = $this->withToken($token)
            ->withHeader('Idempotency-Key', 'create-idempotent-note')
            ->postJson('/api/v1/articles', $payload)
            ->assertCreated();

        $second = $this->withToken($token)
            ->withHeader('Idempotency-Key', 'create-idempotent-note')
            ->postJson('/api/v1/articles', $payload)
            ->assertCreated()
            ->assertHeader('Idempotency-Replayed', 'true');

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertSame(1, Article::query()->where('title', 'Idempotent note')->count());
    }

    public function test_service_scope_and_ownership_are_enforced(): void
    {
        [$service, $token] = $this->serviceToken(['articles:create', 'articles:update']);
        $articleId = $this->withToken($token)->postJson('/api/v1/articles', ['title' => 'Owned draft', 'content' => '# Draft'])->assertCreated()->json('data.id');

        $this->withToken($token)->patchJson("/api/v1/articles/{$articleId}", ['title' => 'Revised draft'])->assertOk();

        $other = Article::create([
            'user_id' => $service->content_user_id,
            'title' => 'Not service owned',
            'slug' => 'not-service-owned',
            'content' => '# Private',
        ]);
        $this->withToken($token)->patchJson("/api/v1/articles/{$other->id}", ['title' => 'Nope'])->assertForbidden();
        $this->withToken($token)->getJson("/api/v1/articles/{$articleId}")->assertForbidden();
    }

    public function test_service_can_submit_review_then_an_approved_article_can_be_scheduled_or_published(): void
    {
        [, $token] = $this->serviceToken(['articles:create', 'review:request', 'articles:schedule', 'articles:publish']);
        SiteSetting::put('allow_schedule', '1');
        SiteSetting::put('allow_publish', '1');
        SiteSetting::put('require_human_review', '1');

        $articleId = $this->withToken($token)->postJson('/api/v1/articles', ['title' => 'Review me', 'content' => '# Draft'])->assertCreated()->json('data.id');
        $this->withToken($token)->postJson("/api/v1/articles/{$articleId}/request-review")->assertOk()->assertJsonPath('data.review_status', 'pending');
        $this->withToken($token)->postJson("/api/v1/articles/{$articleId}/publish")->assertStatus(422);

        Article::findOrFail($articleId)->update(['review_status' => ReviewStatus::Approved]);
        $this->withToken($token)->postJson("/api/v1/articles/{$articleId}/schedule", ['scheduled_at' => now()->addDay()->toIso8601String()])
            ->assertOk()->assertJsonPath('data.status', 'scheduled');

        $article = Article::findOrFail($articleId);
        $article->update(['status' => ArticleStatus::Draft, 'scheduled_at' => null]);
        $this->withToken($token)->postJson("/api/v1/articles/{$articleId}/publish")
            ->assertOk()->assertJsonPath('data.status', 'published');
        $this->assertDatabaseHas('audit_logs', ['event' => 'article.review_requested', 'actor_type' => 'service']);
    }

    public function test_service_media_upload_requires_its_scope_and_never_logs_a_token_value(): void
    {
        Storage::fake('public');
        [, $token] = $this->serviceToken(['media:upload']);

        $this->withToken($token)->withHeader('Accept', 'application/json')->post('/api/v1/media', [
            'image' => UploadedFile::fake()->image('rack.png'),
        ])->assertCreated();

        $this->assertArrayNotHasKey('token', AuditLog::query()->latest()->firstOrFail()->properties);
    }

    public function test_service_media_upload_rejects_svg_and_oversized_files(): void
    {
        Storage::fake('public');
        [, $token] = $this->serviceToken(['media:upload']);

        $this->withToken($token)->withHeader('Accept', 'application/json')->post('/api/v1/media', [
            'image' => UploadedFile::fake()->create('untrusted.svg', 20, 'image/svg+xml'),
        ])->assertUnprocessable()->assertJsonValidationErrors('image')->assertHeader('X-Request-ID');

        $this->withToken($token)->postJson('/api/v1/media', [
            'image' => UploadedFile::fake()->image('oversized.png')->size(4097),
        ])->assertUnprocessable()->assertJsonValidationErrors('image');
    }

    public function test_disabled_service_account_cannot_authenticate_its_token(): void
    {
        [$service, $token] = $this->serviceToken(['articles:create']);
        $service->update(['enabled' => false]);

        $this->withToken($token)->postJson('/api/v1/articles', ['title' => 'Rejected', 'content' => '# Rejected'])->assertUnauthorized();
    }

    public function test_administrator_can_view_and_update_the_openclaw_publishing_policy(): void
    {
        [$service] = $this->serviceToken(['articles:create']);
        $this->actingAs($service->contentUser);

        $this->get('/dashboard/automation/openclaw')->assertOk()->assertSee('OpenClaw automation');

        Livewire::test(OpenClawIndex::class)
            ->set('allow_publish', true)
            ->set('require_human_review', false)
            ->call('savePolicy')
            ->assertHasNoErrors();

        $this->assertSame('1', SiteSetting::value('allow_publish'));
        $this->assertSame('0', SiteSetting::value('require_human_review'));
    }

    /** @return array{0: ServiceAccount, 1: string} */
    private function serviceToken(array $abilities): array
    {
        $owner = User::create(['name' => 'Owner', 'email' => uniqid('owner-', true).'@example.test', 'role' => UserRole::Administrator, 'password' => 'password']);
        $service = ServiceAccount::create(['name' => 'OpenClaw', 'slug' => 'openclaw', 'description' => 'Automated publishing.', 'content_user_id' => $owner->id, 'enabled' => true]);
        $plainToken = 'nbl_'.str()->random(48);
        ApiToken::create(['user_id' => $owner->id, 'service_account_id' => $service->id, 'name' => 'OpenClaw API token', 'token_hash' => hash('sha256', $plainToken), 'token_prefix' => 'nbl_test…', 'abilities' => $abilities]);

        return [$service, $plainToken];
    }
}
