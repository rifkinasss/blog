<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Livewire\Dashboard\Articles\ArticleForm;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class PublishingPhaseOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_author_can_add_and_remove_a_featured_image_with_alt_text(): void
    {
        Storage::fake('public');

        $author = $this->author('featured-author@example.test');
        $this->actingAs($author);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Featured image article')
            ->set('content', '# Article body')
            ->set('cover_image', UploadedFile::fake()->image('server-rack.webp'))
            ->set('cover_image_alt', 'A server rack with status lights')
            ->call('saveAsDraft')
            ->assertHasNoErrors();

        $article = Article::query()->where('slug', 'featured-image-article')->firstOrFail();

        $this->assertSame('A server rack with status lights', $article->cover_image_alt);
        $this->assertNotNull($article->cover_image);
        Storage::disk('public')->assertExists($article->cover_image);

        Livewire::test(ArticleForm::class, ['article' => $article])
            ->set('existing_cover_image', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($article->fresh()->cover_image);
    }

    public function test_public_articles_render_their_featured_image_and_alt_text(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('covers/homelab.webp', 'image bytes');

        $article = Article::create([
            'user_id' => $this->author('public-image-author@example.test')->id,
            'title' => 'A visible featured image',
            'slug' => 'visible-featured-image',
            'content' => '# Visible image',
            'cover_image' => 'covers/homelab.webp',
            'cover_image_alt' => 'A compact homelab rack',
            'status' => ArticleStatus::Published,
            'published_at' => now()->subMinute(),
        ]);

        $this->get(route('articles.show', ['locale' => 'id', 'article' => $article]))
            ->assertOk()
            ->assertSee('storage/covers/homelab.webp', false)
            ->assertSee('A compact homelab rack');
    }

    public function test_draft_preview_requires_a_valid_non_expired_signed_url(): void
    {
        $article = Article::create([
            'user_id' => $this->author('draft-preview-author@example.test')->id,
            'title' => 'Private draft preview',
            'slug' => 'private-draft-preview',
            'excerpt' => 'A private draft.',
            'content' => '# Preview only',
            'status' => ArticleStatus::Draft,
        ]);

        $this->get(route('articles.show', ['locale' => 'id', 'article' => $article]))->assertNotFound();
        $this->get(route('articles.preview', $article))->assertForbidden();

        $previewUrl = URL::temporarySignedRoute('articles.preview', now()->addHour(), ['article' => $article]);
        $this->get($previewUrl)
            ->assertOk()
            ->assertSee('Preview mode')
            ->assertSee('Preview only');

        $expiredPreviewUrl = URL::temporarySignedRoute('articles.preview', now()->subMinute(), ['article' => $article]);
        $this->get($expiredPreviewUrl)->assertForbidden();
    }

    private function author(string $email): User
    {
        return User::create([
            'name' => 'Phase One Author',
            'email' => $email,
            'password' => 'password',
        ]);
    }
}
