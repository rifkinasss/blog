<?php

namespace App\Livewire\Dashboard\Analytics;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class AnalyticsIndex extends Component
{
    public string $range = '6m';

    public function selectRange(string $range): void
    {
        abort_unless(in_array($range, ['30d', '6m', '12m', 'all'], true), 404);

        $this->range = $range;
    }

    public function render()
    {
        $publishedDates = Article::published()
            ->pluck('published_at')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date));
        $totalArticles = Article::count();
        $publishedArticles = $publishedDates->count();
        $firstPublication = $publishedDates->sort()->first();
        $publicationMonths = $firstPublication
            ? max(1, $firstPublication->copy()->startOfMonth()->diffInMonths(now()->startOfMonth()) + 1)
            : 0;

        $categories = Category::query()
            ->withCount([
                'articles',
                'articles as published_articles_count' => fn ($query) => $query->published(),
            ])
            ->orderByDesc('articles_count')
            ->orderBy('name')
            ->limit(8)
            ->get();
        $tags = Tag::query()
            ->withCount('articles')
            ->orderByDesc('articles_count')
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('livewire.dashboard.analytics.index', [
            'totalArticles' => $totalArticles,
            'publishedArticles' => $publishedArticles,
            'draftArticles' => Article::query()->where('status', ArticleStatus::Draft)->count(),
            'activePublishingMonths' => $publishedDates->map(fn (Carbon $date) => $date->format('Y-m'))->unique()->count(),
            'averageArticlesPerMonth' => $publicationMonths ? round($publishedArticles / $publicationMonths, 1) : null,
            'activity' => $this->activityForRange($publishedDates),
            'categoryDistribution' => $categories,
            'categoryDistributionMax' => max(1, (int) ($categories->max('articles_count') ?? 0)),
            'statusDistribution' => collect([
                ['label' => 'Draft', 'count' => Article::query()->where('status', ArticleStatus::Draft)->count(), 'tone' => 'amber'],
                ['label' => 'Published', 'count' => $publishedArticles, 'tone' => 'indigo'],
                ['label' => 'Archived', 'count' => Article::query()->where('status', ArticleStatus::Archived)->count(), 'tone' => 'zinc'],
            ]),
            'topTags' => $tags,
            'topTagsMax' => max(1, (int) ($tags->max('articles_count') ?? 0)),
            'health' => [
                ['label' => 'Drafts older than 30 days', 'count' => Article::query()->where('status', ArticleStatus::Draft)->where('updated_at', '<', now()->subDays(30))->count(), 'detail' => 'Review, publish, or archive them.', 'href' => route('dashboard.articles.index', ['status' => 'draft', 'dateRange' => 'older_30'])],
                ['label' => 'Articles without a category', 'count' => Article::query()->whereNull('category_id')->count(), 'detail' => 'Readers cannot browse these by topic.', 'href' => route('dashboard.articles.index', ['category' => 'none'])],
                ['label' => 'Articles without tags', 'count' => Article::query()->doesntHave('tags')->count(), 'detail' => 'Related content is harder to connect.', 'href' => route('dashboard.articles.index', ['tag' => 'none'])],
                ['label' => 'Published articles not updated in 180 days', 'count' => Article::published()->where('updated_at', '<', now()->subDays(180))->count(), 'detail' => 'Consider checking technical accuracy.', 'href' => route('dashboard.articles.index', ['status' => 'published', 'dateRange' => 'older_180'])],
                ['label' => 'Empty categories', 'count' => Category::query()->doesntHave('articles')->count(), 'detail' => 'Remove or assign content to them.', 'href' => route('dashboard.categories.index', ['coverage' => 'empty'])],
                ['label' => 'Unused tags', 'count' => Tag::query()->doesntHave('articles')->count(), 'detail' => 'Remove or apply them to relevant articles.', 'href' => route('dashboard.tags.index', ['usage' => 'unused'])],
            ],
        ])->layout('layouts.dashboard');
    }

    /**
     * @return Collection<int, array{label: string, count: int, height: int}>
     */
    private function activityForRange(Collection $publishedDates): Collection
    {
        if ($this->range === '30d') {
            $start = now()->startOfDay()->subDays(29);
            $points = collect(range(0, 29))->map(function (int $offset) use ($start, $publishedDates): array {
                $day = $start->copy()->addDays($offset);

                return ['label' => $day->format('d M'), 'count' => $publishedDates->filter(fn (Carbon $date) => $date->isSameDay($day))->count()];
            });
        } elseif ($this->range === 'all') {
            $firstPublication = $publishedDates->sort()->first();
            $start = $firstPublication ? $firstPublication->copy()->startOfYear() : now()->startOfYear();
            $years = max(1, $start->diffInYears(now()->startOfYear()) + 1);
            $points = collect(range(0, $years - 1))->map(function (int $offset) use ($start, $publishedDates): array {
                $year = $start->copy()->addYears($offset);

                return ['label' => $year->format('Y'), 'count' => $publishedDates->filter(fn (Carbon $date) => $date->isSameYear($year))->count()];
            });
        } else {
            $months = $this->range === '12m' ? 12 : 6;
            $start = now()->startOfMonth()->subMonths($months - 1);
            $points = collect(range(0, $months - 1))->map(function (int $offset) use ($start, $publishedDates): array {
                $month = $start->copy()->addMonths($offset);

                return ['label' => $month->format('M Y'), 'count' => $publishedDates->filter(fn (Carbon $date) => $date->isSameMonth($month))->count()];
            });
        }

        $maximum = max(1, (int) $points->max('count'));

        return $points->map(fn (array $point): array => [...$point, 'height' => $point['count'] ? max(8, (int) round(($point['count'] / $maximum) * 100)) : 2]);
    }
}
