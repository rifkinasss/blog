<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\UserRole;
use App\Livewire\Dashboard\Articles\ArticleForm;
use App\Livewire\Dashboard\Settings\SettingsIndex;
use App\Models\Article;
use App\Models\Category;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class AdminFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_persist_public_blog_settings(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'role' => UserRole::Administrator, 'password' => 'password']);
        $this->actingAs($admin);

        Livewire::test(SettingsIndex::class)
            ->set('site_name', 'NasLabs Journal')
            ->set('site_description', 'Engineering notes from the bench.')
            ->set('public_url', 'https://blog.example.test')
            ->call('saveSiteSettings');

        $this->assertSame('NasLabs Journal', SiteSetting::value('site_name'));
        $this->assertDatabaseHas('audit_logs', ['event' => 'site_settings.updated']);
    }

    public function test_signed_preview_can_show_a_draft_but_public_route_cannot(): void
    {
        $author = User::create(['name' => 'Author', 'email' => 'author@example.test', 'role' => UserRole::Editor, 'password' => 'password']);
        $article = Article::create(['user_id' => $author->id, 'title' => 'Draft', 'slug' => 'draft', 'content' => '# Draft', 'status' => ArticleStatus::Draft]);

        $this->get('/id/articles/draft')->assertNotFound();
        $this->get(URL::temporarySignedRoute('articles.preview', now()->addMinutes(10), ['article' => $article]))->assertOk()->assertSee('Preview mode');
    }

    public function test_administrator_can_persist_publishing_defaults_used_by_new_article_forms(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'publishing@example.test', 'role' => UserRole::Administrator, 'password' => 'password']);
        $category = Category::create(['name' => 'Homelab', 'slug' => 'homelab']);
        $this->actingAs($admin);

        Livewire::test(SettingsIndex::class)
            ->set('default_article_status', ArticleStatus::Archived->value)
            ->set('default_category_id', $category->id)
            ->set('articles_per_page', '12')
            ->set('date_format', 'd/m/Y')
            ->call('savePublishing');

        Livewire::test(ArticleForm::class)
            ->assertSet('status', ArticleStatus::Archived->value)
            ->assertSet('category_id', (string) $category->id);

        $this->assertSame('12', SiteSetting::value('articles_per_page'));
        $this->assertSame('d/m/Y', SiteSetting::value('date_format'));
    }
}
