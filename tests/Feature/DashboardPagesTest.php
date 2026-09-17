<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ServiceAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_render_every_dashboard_page(): void
    {
        $user = User::create([
            'name' => 'Dashboard Admin',
            'email' => 'dashboard@example.com',
            'role' => UserRole::Administrator,
            'password' => 'password',
        ]);

        foreach ([
            '/dashboard',
            '/dashboard/articles',
            '/dashboard/articles/create',
            '/dashboard/categories',
            '/dashboard/tags',
            '/dashboard/media',
            '/dashboard/analytics',
            '/dashboard/activity',
            '/dashboard/users',
            '/dashboard/settings',
        ] as $uri) {
            $this->actingAs($user)->get($uri)->assertOk();
        }
    }

    public function test_an_editor_cannot_manage_user_accounts(): void
    {
        $editor = User::create([
            'name' => 'Editor',
            'email' => 'editor@example.com',
            'role' => UserRole::Editor,
            'password' => 'password',
        ]);

        $this->actingAs($editor)->get('/dashboard/articles/create')->assertOk();
        $this->actingAs($editor)->get('/dashboard/users')->assertForbidden();
    }

    public function test_administrator_can_view_service_accounts_separately_from_human_accounts(): void
    {
        $admin = User::create(['name' => 'NasLabs Admin', 'email' => 'owner@example.com', 'role' => UserRole::Administrator, 'password' => 'password']);
        ServiceAccount::create(['name' => 'OpenClaw', 'slug' => 'openclaw', 'description' => 'Automated content publishing and media upload.', 'content_user_id' => $admin->id]);

        $this->actingAs($admin)->get('/dashboard/users')
            ->assertOk()
            ->assertSee('Human accounts')
            ->assertSee('Service accounts')
            ->assertSee('OpenClaw')
            ->assertSee('Owner / Administrator');
    }
}
