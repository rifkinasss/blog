<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\ServiceAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_can_view_human_and_service_account_activity(): void
    {
        $administrator = User::create(['name' => 'Administrator', 'email' => 'activity-admin@example.test', 'password' => 'password', 'role' => UserRole::Administrator]);
        $service = ServiceAccount::create([
            'name' => 'OpenClaw',
            'slug' => 'openclaw',
            'description' => 'Automated publishing.',
            'content_user_id' => $administrator->id,
        ]);

        AuditLog::create([
            'user_id' => $administrator->id,
            'event' => 'article.updated',
            'properties' => ['title' => 'Human note'],
        ]);
        AuditLog::create([
            'user_id' => $administrator->id,
            'event' => 'article.created_via_api',
            'properties' => ['title' => 'Automated note', 'service_account_id' => $service->id],
        ]);

        $this->actingAs($administrator)->get('/dashboard/activity')
            ->assertOk()
            ->assertSee('Activity log')
            ->assertSee('Human note')
            ->assertSee('OpenClaw');
    }

    public function test_an_editor_cannot_view_activity_log(): void
    {
        $editor = User::create(['name' => 'Editor', 'email' => 'activity-editor@example.test', 'password' => 'password', 'role' => UserRole::Editor]);

        $this->actingAs($editor)->get('/dashboard/activity')->assertForbidden();
    }
}
