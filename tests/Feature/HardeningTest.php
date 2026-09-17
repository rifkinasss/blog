<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_is_safe_and_has_operational_headers(): void
    {
        $response = $this->getJson('/health');

        $response
            ->assertOk()
            ->assertJson(['status' => 'healthy'])
            ->assertHeader('X-Request-ID')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $this->assertSame(['status'], array_keys($response->json()));
    }

    public function test_only_published_non_deleted_articles_are_exposed_in_public_surfaces(): void
    {
        $author = User::create(['name' => 'Author', 'email' => 'public-surfaces@example.test', 'password' => 'password']);
        $published = $this->article($author, 'Published public article', 'published-public-article', ArticleStatus::Published, now()->subMinute());
        $archived = $this->article($author, 'Archived private article', 'archived-private-article', ArticleStatus::Archived);
        $deleted = $this->article($author, 'Deleted private article', 'deleted-private-article', ArticleStatus::Published, now()->subMinute());
        $deleted->delete();

        $this->get('/id/articles')
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($archived->title)
            ->assertDontSee($deleted->title);

        $this->get('/rss.xml')
            ->assertOk()
            ->assertSee($published->slug)
            ->assertDontSee($archived->slug)
            ->assertDontSee($deleted->slug);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee($published->slug)
            ->assertDontSee($archived->slug)
            ->assertDontSee($deleted->slug);
    }

    public function test_openclaw_api_rate_limit_returns_too_many_requests(): void
    {
        foreach (range(1, 60) as $attempt) {
            $this->getJson('/api/v1/articles')->assertUnauthorized();
        }

        $this->getJson('/api/v1/articles')->assertTooManyRequests();
    }

    private function article(User $author, string $title, string $slug, ArticleStatus $status, $publishedAt = null): Article
    {
        return Article::create([
            'user_id' => $author->id,
            'title' => $title,
            'slug' => $slug,
            'content' => '# '.$title,
            'status' => $status,
            'published_at' => $publishedAt,
        ]);
    }
}
