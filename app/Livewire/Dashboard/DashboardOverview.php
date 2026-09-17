<?php

namespace App\Livewire\Dashboard;

use App\Actions\Articles\PublishArticle;
use App\Actions\Articles\UnpublishArticle;
use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class DashboardOverview extends Component
{
    public string $range = '6';

    public function selectRange(string $range): void
    {
        abort_unless(in_array($range, ['6', '12'], true), 404);

        $this->range = $range;
    }

    public function publish(Article $article, PublishArticle $publish): void
    {
        $this->authorize('update', $article);
        $publish->handle($article);
        AuditLog::record('article.published', $article, ['title' => $article->title]);
        session()->flash('success', 'Article published.');
    }

    public function unpublish(Article $article, UnpublishArticle $unpublish): void
    {
        $this->authorize('update', $article);
        $unpublish->handle($article);
        AuditLog::record('article.unpublished', $article, ['title' => $article->title]);
        session()->flash('success', 'Article moved to draft.');
    }

    public function openPreview(Article $article): void
    {
        $this->authorize('update', $article);

        $this->redirect(URL::temporarySignedRoute('articles.preview', now()->addHour(), [
            'article' => $article,
        ]), navigate: false);
    }

    public function render()
    {
        $months = (int) $this->range;
        $start = now()->startOfMonth()->subMonths($months - 1);
        $totalArticles = Article::count();

        $activity = collect(range(0, $months - 1))->map(function (int $offset) use ($start): array {
            $month = $start->copy()->addMonths($offset);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            return [
                'label' => $month->format('M'),
                'created' => Article::query()->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'published' => Article::query()
                    ->where('status', ArticleStatus::Published)
                    ->whereBetween('published_at', [$monthStart, $monthEnd])
                    ->count(),
            ];
        });
        $activityMax = max(1, $activity->max(fn (array $month) => max($month['created'], $month['published'])));
        $mostActiveMonth = $activity->sortByDesc('published')->first();

        $statusCounts = [
            ArticleStatus::Draft->value => Article::query()->where('status', ArticleStatus::Draft)->count(),
            ArticleStatus::Scheduled->value => Article::query()->where('status', ArticleStatus::Scheduled)->count(),
            ArticleStatus::Published->value => Article::published()->count(),
            ArticleStatus::Archived->value => Article::query()->where('status', ArticleStatus::Archived)->count(),
        ];

        $topCategories = Category::query()
            ->withCount('articles')
            ->withMax(['articles as last_published_at' => fn ($query) => $query->published()], 'published_at')
            ->orderByDesc('articles_count')
            ->limit(6)
            ->get();

        $recentActivity = AuditLog::query()
            ->with('user')
            ->when(! auth()->user()->isAdministrator(), fn ($query) => $query->where('user_id', auth()->id()))
            ->latest()
            ->limit(8)
            ->get();

        $insights = collect([
            $topCategories->first() ? [
                'label' => 'Most used category',
                'value' => $topCategories->first()->name,
                'detail' => $topCategories->first()->articles_count.' article'.($topCategories->first()->articles_count === 1 ? '' : 's'),
            ] : null,
            Article::query()->whereNull('category_id')->count() > 0 ? [
                'label' => 'Without category',
                'value' => Article::query()->whereNull('category_id')->count(),
                'detail' => 'article'.(Article::query()->whereNull('category_id')->count() === 1 ? '' : 's').' need taxonomy',
            ] : null,
            Article::query()->doesntHave('tags')->count() > 0 ? [
                'label' => 'Without tags',
                'value' => Article::query()->doesntHave('tags')->count(),
                'detail' => 'article'.(Article::query()->doesntHave('tags')->count() === 1 ? '' : 's').' need cross-links',
            ] : null,
            Article::query()->where('status', ArticleStatus::Draft)->where('updated_at', '<', now()->subDays(7))->count() > 0 ? [
                'label' => 'Inactive drafts',
                'value' => Article::query()->where('status', ArticleStatus::Draft)->where('updated_at', '<', now()->subDays(7))->count(),
                'detail' => 'not updated in 7 days',
            ] : null,
        ])->filter()->values();

        return view('livewire.dashboard.overview', [
            'totalArticles' => $totalArticles,
            'publishedArticles' => $statusCounts[ArticleStatus::Published->value],
            'draftArticles' => $statusCounts[ArticleStatus::Draft->value],
            'scheduledArticles' => $statusCounts[ArticleStatus::Scheduled->value],
            'archivedArticles' => $statusCounts[ArticleStatus::Archived->value],
            'categoryCount' => Category::count(),
            'tagCount' => Tag::count(),
            'publishedThisMonth' => Article::published()->whereBetween('published_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'updatedThisWeek' => Article::query()->where('updated_at', '>=', now()->startOfWeek())->count(),
            'activity' => $activity->map(fn (array $month) => [
                ...$month,
                'createdHeight' => $month['created'] ? max(8, (int) round(($month['created'] / $activityMax) * 100)) : 2,
                'publishedHeight' => $month['published'] ? max(8, (int) round(($month['published'] / $activityMax) * 100)) : 2,
            ]),
            'mostActiveMonth' => $mostActiveMonth,
            'pipeline' => collect($statusCounts)->map(fn (int $count, string $status) => [
                'status' => $status,
                'count' => $count,
                'percentage' => $totalArticles ? (int) round(($count / $totalArticles) * 100) : 0,
            ])->values(),
            'topCategories' => $topCategories,
            'recentActivity' => $recentActivity,
            'nextScheduledArticle' => Article::query()->scheduled()->orderBy('scheduled_at')->first(),
            'scheduledThisWeek' => Article::query()->scheduled()->whereBetween('scheduled_at', [now(), now()->endOfWeek()])->count(),
            'recentArticles' => Article::query()->with(['category', 'tags'])->latest()->limit(10)->get(),
            'insights' => $insights,
        ])->layout('layouts.dashboard');
    }
}
