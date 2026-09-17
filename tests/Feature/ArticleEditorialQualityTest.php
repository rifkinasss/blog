<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\ReviewStatus;
use App\Livewire\Dashboard\Articles\ArticleIndex;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleEditorialQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_article_with_missing_editorial_data_is_incomplete(): void
    {
        $author = User::create(['name' => 'Author', 'email' => 'quality-author@example.test', 'password' => 'password']);
        $incomplete = Article::create([
            'user_id' => $author->id,
            'title' => 'Unfinished note',
            'slug' => 'unfinished-note',
            'content' => '',
            'status' => ArticleStatus::Draft,
        ]);

        $complete = Article::create([
            'user_id' => $author->id,
            'category_id' => Category::create(['name' => 'Engineering', 'slug' => 'engineering'])->id,
            'title' => 'Complete note',
            'slug' => 'complete-note',
            'excerpt' => 'A complete editorial record.',
            'content' => '# Complete',
            'cover_image' => 'covers/complete.webp',
            'status' => ArticleStatus::Draft,
        ]);
        $complete->tags()->attach(Tag::create(['name' => 'Laravel', 'slug' => 'laravel']));

        $this->assertTrue(Article::incomplete()->whereKey($incomplete)->exists());
        $this->assertFalse(Article::incomplete()->whereKey($complete)->exists());
    }

    public function test_dashboard_can_filter_articles_awaiting_review(): void
    {
        $author = User::create(['name' => 'Author', 'email' => 'review-author@example.test', 'password' => 'password']);
        $review = Article::create([
            'user_id' => $author->id,
            'title' => 'Review required',
            'slug' => 'review-required',
            'content' => '# Review',
            'review_requested_at' => now(),
            'review_status' => ReviewStatus::Pending,
        ]);
        Article::create([
            'user_id' => $author->id,
            'title' => 'Normal draft',
            'slug' => 'normal-draft',
            'content' => '# Draft',
        ]);

        $this->actingAs($author);

        Livewire::test(ArticleIndex::class)
            ->set('status', 'needs_review')
            ->assertSee($review->title)
            ->assertDontSee('Normal draft');
    }
}
