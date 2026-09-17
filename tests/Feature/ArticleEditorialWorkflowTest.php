<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Livewire\Dashboard\Articles\ArticleForm;
use App\Livewire\Dashboard\Articles\ArticleIndex;
use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleEditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_article_can_be_scheduled_with_a_custom_slug_and_tags(): void
    {
        $author = User::create([
            'name' => 'Author',
            'email' => 'author@example.test',
            'password' => 'password',
        ]);
        $laravel = Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $postgres = Tag::create(['name' => 'PostgreSQL', 'slug' => 'postgresql']);
        $scheduledAt = now()->addWeek()->startOfMinute();

        $this->actingAs($author);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Scheduling a Laravel Article')
            ->set('slug', 'laravel-scheduled-article')
            ->set('excerpt', 'A scheduled technical note.')
            ->set('content', '# Scheduled article')
            ->set('status', 'scheduled')
            ->set('scheduled_at', $scheduledAt->format('Y-m-d\TH:i'))
            ->set('tag_ids', [$laravel->id, $postgres->id])
            ->call('save');

        $this->assertDatabaseHas('articles', [
            'title' => 'Scheduling a Laravel Article',
            'slug' => 'laravel-scheduled-article',
            'status' => 'scheduled',
        ]);

        $article = Article::where('slug', 'laravel-scheduled-article')->firstOrFail();

        $this->assertTrue($article->scheduled_at->equalTo($scheduledAt));
        $this->assertNull($article->published_at);
        $this->assertEqualsCanonicalizing([$laravel->id, $postgres->id], $article->tags()->pluck('tags.id')->all());
    }

    public function test_a_new_article_slug_follows_its_title_until_the_slug_is_edited(): void
    {
        $author = User::create([
            'name' => 'Author',
            'email' => 'slug@example.test',
            'password' => 'password',
        ]);

        $this->actingAs($author);

        Livewire::test(ArticleForm::class)
            ->set('title', 'A Practical Laravel Note')
            ->assertSet('slug', 'a-practical-laravel-note')
            ->set('slug', 'custom-note')
            ->set('title', 'A Different Title')
            ->assertSet('slug', 'custom-note');
    }

    public function test_an_author_can_publish_and_unpublish_an_article_from_the_index(): void
    {
        $author = User::create([
            'name' => 'Author',
            'email' => 'publish@example.test',
            'password' => 'password',
        ]);
        $article = Article::create([
            'user_id' => $author->id,
            'title' => 'Draft from index',
            'slug' => 'draft-from-index',
            'content' => '# Draft',
            'status' => ArticleStatus::Draft,
        ]);

        $this->actingAs($author);

        Livewire::test(ArticleIndex::class)
            ->call('publish', $article->id);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => ArticleStatus::Published->value,
        ]);
        $this->assertNotNull($article->fresh()->published_at);

        Livewire::test(ArticleIndex::class)
            ->call('unpublish', $article->id);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => ArticleStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    public function test_article_index_can_bulk_publish_selected_drafts(): void
    {
        $author = User::create([
            'name' => 'Author',
            'email' => 'bulk-publish@example.test',
            'password' => 'password',
        ]);
        $first = Article::create([
            'user_id' => $author->id,
            'title' => 'First bulk draft',
            'slug' => 'first-bulk-draft',
            'content' => '# First',
            'status' => ArticleStatus::Draft,
        ]);
        $second = Article::create([
            'user_id' => $author->id,
            'title' => 'Second bulk draft',
            'slug' => 'second-bulk-draft',
            'content' => '# Second',
            'status' => ArticleStatus::Draft,
        ]);

        $this->actingAs($author);

        Livewire::test(ArticleIndex::class)
            ->set('selected', [$first->id, $second->id])
            ->call('bulkPublish');

        $this->assertDatabaseHas('articles', ['id' => $first->id, 'status' => ArticleStatus::Published->value]);
        $this->assertDatabaseHas('articles', ['id' => $second->id, 'status' => ArticleStatus::Published->value]);
    }

    public function test_an_author_can_restore_a_trashed_article(): void
    {
        $author = User::create([
            'name' => 'Author',
            'email' => 'trash@example.test',
            'password' => 'password',
        ]);
        $article = Article::create([
            'user_id' => $author->id,
            'title' => 'Recoverable draft',
            'slug' => 'recoverable-draft',
            'content' => '# Recoverable',
            'status' => ArticleStatus::Draft,
        ]);

        $this->actingAs($author);

        Livewire::test(ArticleIndex::class)->call('delete', $article->id);
        $this->assertSoftDeleted('articles', ['id' => $article->id]);

        Livewire::test(ArticleIndex::class)->call('restore', $article->id);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'deleted_at' => null]);
    }
}
