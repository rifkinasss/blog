<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_legacy_public_url_redirects_to_the_default_locale(): void
    {
        $this->get('/articles')->assertRedirect('/id/articles');
    }

    public function test_authenticated_user_can_create_a_draft_and_draft_is_not_public(): void
    {
        $user = User::create(['name' => 'Test Admin', 'email' => 'test@example.com', 'password' => 'password']);
        $this->actingAs($user)->get('/dashboard/articles/create')->assertOk();
        $article = Article::create(['user_id' => $user->id, 'title' => 'Draft article', 'slug' => 'draft-article', 'content' => '# Draft', 'status' => ArticleStatus::Draft]);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'status' => 'draft']);
        $this->get('/id/articles')->assertOk()->assertDontSee('Draft article');
    }

    public function test_published_article_is_public(): void
    {
        $user = User::create(['name' => 'Test Admin', 'email' => 'test@example.com', 'password' => 'password']);
        $article = Article::create(['user_id' => $user->id, 'title' => 'Published article', 'slug' => 'published-article', 'content' => '# Hello', 'status' => ArticleStatus::Published, 'published_at' => now()]);
        $this->get('/id/articles')->assertOk()->assertSee('Published article');
        $this->get('/id/articles/'.$article->slug)->assertOk()->assertSee('Hello');
    }

    public function test_dashboard_overview_surfaces_real_editorial_state(): void
    {
        $user = User::create(['name' => 'Test Admin', 'email' => 'overview@example.com', 'password' => 'password']);
        Article::create(['user_id' => $user->id, 'title' => 'Draft queue item', 'slug' => 'draft-queue-item', 'content' => '# Draft', 'status' => ArticleStatus::Draft]);
        Article::create(['user_id' => $user->id, 'title' => 'Published overview item', 'slug' => 'published-overview-item', 'content' => '# Published', 'status' => ArticleStatus::Published, 'published_at' => now()]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Overview')
            ->assertSee('Draft queue item')
            ->assertSee('Published overview item');
    }

    public function test_analytics_uses_editorial_data_without_presenting_traffic_metrics(): void
    {
        $user = User::create(['name' => 'Test Admin', 'email' => 'analytics@example.com', 'password' => 'password']);
        $category = Category::create(['name' => 'Architecture', 'slug' => 'architecture']);
        $tag = Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $article = Article::create(['user_id' => $user->id, 'category_id' => $category->id, 'title' => 'Analytics article', 'slug' => 'analytics-article', 'content' => '# Analytics', 'status' => ArticleStatus::Published, 'published_at' => now()]);
        $article->tags()->attach($tag);

        $this->actingAs($user)
            ->get('/dashboard/analytics')
            ->assertOk()
            ->assertSee('Articles published over time')
            ->assertSee('Architecture')
            ->assertSee('Laravel')
            ->assertSee('Editorial health')
            ->assertDontSee('Traffic analytics');
    }

    public function test_category_workspace_shows_content_distribution(): void
    {
        $user = User::create(['name' => 'Test Admin', 'email' => 'categories@example.com', 'password' => 'password']);
        $category = Category::create(['name' => 'Homelab', 'slug' => 'homelab']);
        Article::create(['user_id' => $user->id, 'category_id' => $category->id, 'title' => 'Category article', 'slug' => 'category-article', 'content' => '# Category', 'status' => ArticleStatus::Published, 'published_at' => now()]);

        $this->actingAs($user)
            ->get('/dashboard/categories')
            ->assertOk()
            ->assertSee('Category distribution')
            ->assertSee('Homelab');
    }

    public function test_tag_workspace_shows_usage_distribution(): void
    {
        $user = User::create(['name' => 'Test Admin', 'email' => 'tags@example.com', 'password' => 'password']);
        $tag = Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $article = Article::create(['user_id' => $user->id, 'title' => 'Tagged article', 'slug' => 'tagged-article', 'content' => '# Tagged', 'status' => ArticleStatus::Draft]);
        $article->tags()->attach($tag);

        $this->actingAs($user)
            ->get('/dashboard/tags')
            ->assertOk()
            ->assertSee('Top tags')
            ->assertSee('Laravel');
    }

    public function test_media_library_surfaces_stored_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('covers/sample-note.txt', 'media library sample');
        $user = User::create(['name' => 'Test Admin', 'email' => 'media@example.com', 'password' => 'password']);

        $this->actingAs($user)
            ->get('/dashboard/media')
            ->assertOk()
            ->assertSee('Total files')
            ->assertSee('sample-note.txt');
    }

    public function test_scheduled_article_is_not_public_until_its_publish_time(): void
    {
        $user = User::create(['name' => 'Author', 'email' => 'schedule@example.test', 'password' => 'password']);
        $article = Article::create([
            'user_id' => $user->id,
            'title' => 'Scheduled article',
            'slug' => 'scheduled-article',
            'content' => '## Future section',
            'status' => ArticleStatus::Published,
            'published_at' => now()->addHour(),
        ]);

        $this->get('/id/articles/'.$article->slug)->assertNotFound();
    }
}
