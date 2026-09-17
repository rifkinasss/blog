<?php

namespace Tests\Feature;

use App\Actions\Articles\PublishArticle;
use App\Enums\ArticleStatus;
use App\Livewire\Dashboard\Articles\ArticleForm;
use App\Livewire\Dashboard\Articles\ArticleIndex;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduledPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_author_can_schedule_an_article_and_past_dates_are_rejected(): void
    {
        $author = $this->author('schedule-form@example.test');
        $this->actingAs($author);

        Livewire::test(ArticleForm::class)
            ->set('title', 'A scheduled note')
            ->set('content', '# Scheduled')
            ->set('scheduled_at', now()->addDay()->startOfMinute()->format('Y-m-d\TH:i'))
            ->call('schedule')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('articles', [
            'slug' => 'a-scheduled-note',
            'status' => ArticleStatus::Scheduled->value,
            'published_at' => null,
        ]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'article.scheduled']);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Expired schedule')
            ->set('content', '# Expired')
            ->set('scheduled_at', now()->subMinute()->format('Y-m-d\TH:i'))
            ->call('schedule')
            ->assertHasErrors(['scheduled_at']);
    }

    public function test_scheduler_publishes_only_due_articles_and_records_a_system_activity(): void
    {
        $author = $this->author('due-schedule@example.test');
        $due = $this->scheduledArticle($author, 'Due article', now()->subMinute());
        $future = $this->scheduledArticle($author, 'Future article', now()->addDay());

        $this->artisan('scheduler:publish-due')->assertSuccessful();
        $this->artisan('scheduler:publish-due')->assertSuccessful();

        $due->refresh();
        $future->refresh();

        $this->assertSame(ArticleStatus::Published, $due->status);
        $this->assertNotNull($due->published_at);
        $this->assertNull($due->scheduled_at);
        $this->assertSame(ArticleStatus::Scheduled, $future->status);
        $this->assertSame(1, AuditLog::query()->where('event', 'article.published')->where('subject_id', $due->id)->count());
        $this->assertDatabaseHas('audit_logs', ['subject_id' => $due->id, 'actor_type' => 'system', 'actor_name' => 'Laravel Scheduler']);
    }

    public function test_rescheduling_cancelling_and_publishing_now_keep_lifecycle_consistent(): void
    {
        $author = $this->author('reschedule@example.test');
        $this->actingAs($author);
        $article = $this->scheduledArticle($author, 'Reschedule article', now()->addDay());

        $newTime = now()->addDays(2)->startOfMinute();
        Livewire::test(ArticleForm::class, ['article' => $article])
            ->set('scheduled_at', $newTime->format('Y-m-d\TH:i'))
            ->call('schedule')
            ->assertHasNoErrors();

        $this->assertTrue($article->fresh()->scheduled_at->equalTo($newTime));
        $this->assertDatabaseHas('audit_logs', ['event' => 'article.rescheduled', 'subject_id' => $article->id]);

        Livewire::test(ArticleIndex::class)
            ->call('cancelSchedule', $article->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('articles', ['id' => $article->id, 'status' => 'draft', 'scheduled_at' => null]);

        $article->update(['status' => ArticleStatus::Scheduled, 'scheduled_at' => now()->addDay(), 'published_at' => null]);
        app(PublishArticle::class)->handle($article);

        $article->refresh();
        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertNull($article->scheduled_at);
        $this->assertNotNull($article->published_at);
    }

    public function test_overview_surfaces_the_next_scheduled_article(): void
    {
        $author = $this->author('overview-schedule@example.test');
        $next = $this->scheduledArticle($author, 'Next scheduled article', now()->addDay());
        $this->scheduledArticle($author, 'Later scheduled article', now()->addDays(2));

        $this->actingAs($author)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Upcoming publishing')
            ->assertSee($next->title)
            ->assertSee('1 more scheduled article');
    }

    private function author(string $email): User
    {
        return User::create(['name' => 'Scheduler Author', 'email' => $email, 'password' => 'password']);
    }

    private function scheduledArticle(User $author, string $title, CarbonInterface $scheduledAt): Article
    {
        return Article::create([
            'user_id' => $author->id,
            'title' => $title,
            'slug' => str($title)->slug(),
            'content' => '# '.$title,
            'status' => ArticleStatus::Scheduled,
            'scheduled_at' => $scheduledAt,
        ]);
    }
}
