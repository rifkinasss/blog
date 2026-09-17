<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ArticleSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_article_uses_its_seo_metadata_and_social_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('covers/social.webp', 'image');
        $author = User::create(['name' => 'SEO Author', 'email' => 'seo-author@example.test', 'password' => 'password']);
        $article = Article::create([
            'user_id' => $author->id,
            'title' => 'Article title',
            'slug' => 'article-title',
            'excerpt' => 'Fallback excerpt',
            'content' => '# Article body',
            'status' => ArticleStatus::Published,
            'published_at' => now()->subMinute(),
            'meta_title' => 'Custom SEO title',
            'meta_description' => 'Custom search description',
            'canonical_url' => 'https://example.test/canonical-article',
            'og_image' => 'covers/social.webp',
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('<title>Custom SEO title', false)
            ->assertSee('name="description" content="Custom search description"', false)
            ->assertSee('rel="canonical" href="https://example.test/canonical-article"', false)
            ->assertSee('property="og:image" content="/storage/covers/social.webp"', false);
    }

    public function test_signed_preview_is_always_noindex(): void
    {
        $author = User::create(['name' => 'Preview Author', 'email' => 'preview-seo@example.test', 'password' => 'password']);
        $article = Article::create(['user_id' => $author->id, 'title' => 'Private draft', 'slug' => 'private-draft', 'content' => '# Draft']);

        $this->get(URL::temporarySignedRoute('articles.preview', now()->addHour(), ['article' => $article]))
            ->assertOk()
            ->assertSee('name="robots" content="noindex,nofollow"', false);
    }
}
