<?php

namespace App\Livewire\Dashboard\Articles;

use App\Actions\Articles\ArchiveArticle;
use App\Actions\Articles\CancelScheduledArticle;
use App\Actions\Articles\PublishArticle;
use App\Actions\Articles\UnpublishArticle;
use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Url as UrlAttribute;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleIndex extends Component
{
    use WithPagination;

    #[UrlAttribute]
    public string $search = '';

    #[UrlAttribute]
    public string $status = 'all';

    #[UrlAttribute]
    public string $category = 'all';

    #[UrlAttribute]
    public string $tag = 'all';

    #[UrlAttribute]
    public string $sort = 'updated_desc';

    #[UrlAttribute]
    public string $dateRange = 'all';

    public bool $selectPage = false;

    /** @var array<int, int> */
    public array $selected = [];

    public function updatedSearch(): void
    {
        $this->resetPaginationAndSelection();
    }

    public function updatedStatus(): void
    {
        $this->resetPaginationAndSelection();
    }

    public function updatedCategory(): void
    {
        $this->resetPaginationAndSelection();
    }

    public function updatedTag(): void
    {
        $this->resetPaginationAndSelection();
    }

    public function updatedSort(): void
    {
        $this->resetPaginationAndSelection();
    }

    public function updatedDateRange(): void
    {
        $this->resetPaginationAndSelection();
    }

    public function updatedSelectPage(bool $selected): void
    {
        $this->selected = $selected
            ? $this->articlesQuery()->paginate(15)->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'category', 'tag', 'dateRange', 'selected', 'selectPage']);
        $this->sort = 'updated_desc';
        $this->resetPage();
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

    public function archive(Article $article, ArchiveArticle $archive): void
    {
        $this->authorize('update', $article);
        $archive->handle($article);
        AuditLog::record('article.archived', $article, ['title' => $article->title]);
        session()->flash('success', 'Article archived.');
    }

    public function cancelSchedule(Article $article, CancelScheduledArticle $cancel): void
    {
        $this->authorize('update', $article);
        abort_unless($article->status === ArticleStatus::Scheduled, 422);

        $scheduledAt = $article->scheduled_at;
        $cancel->handle($article);
        AuditLog::record('article.schedule_cancelled', $article, [
            'title' => $article->title,
            'scheduled_at' => $scheduledAt?->toIso8601String(),
        ]);
        session()->flash('success', 'Schedule cancelled. Article moved to draft.');
    }

    public function delete(Article $article): void
    {
        $this->authorize('delete', $article);
        AuditLog::record('article.deleted', $article, ['title' => $article->title, 'recovery_available' => true]);
        $article->delete();
        $this->selected = array_values(array_diff($this->selected, [$article->id]));
        session()->flash('success', 'Article moved to Trash.');
    }

    public function restore(int $articleId): void
    {
        $article = Article::onlyTrashed()->findOrFail($articleId);
        $this->authorize('update', $article);
        $article->restore();
        AuditLog::record('article.restored', $article, ['title' => $article->title]);
        session()->flash('success', 'Article restored.');
    }

    public function forceDelete(int $articleId): void
    {
        $article = Article::onlyTrashed()->findOrFail($articleId);
        abort_unless(auth()->user()->isAdministrator(), 403);
        AuditLog::record('article.permanently_deleted', $article, ['title' => $article->title]);
        $article->forceDelete();
        session()->flash('success', 'Article permanently deleted.');
    }

    public function openPreview(Article $article): void
    {
        $this->authorize('update', $article);

        $this->redirect(URL::temporarySignedRoute('articles.preview', now()->addHour(), [
            'article' => $article,
        ]), navigate: false);
    }

    public function bulkPublish(PublishArticle $publish): void
    {
        $articles = $this->selectedArticles()->where('status', ArticleStatus::Draft);
        $articles->each(fn (Article $article) => $this->authorize('update', $article));
        $articles->each(function (Article $article) use ($publish): void {
            $publish->handle($article);
            AuditLog::record('article.published', $article, ['title' => $article->title, 'bulk' => true]);
        });

        $this->finishBulkAction($articles->count(), 'published');
    }

    public function bulkArchive(ArchiveArticle $archive): void
    {
        $articles = $this->selectedArticles()->reject(fn (Article $article) => $article->status === ArticleStatus::Archived);
        $articles->each(fn (Article $article) => $this->authorize('update', $article));
        $articles->each(function (Article $article) use ($archive): void {
            $archive->handle($article);
            AuditLog::record('article.archived', $article, ['title' => $article->title, 'bulk' => true]);
        });

        $this->finishBulkAction($articles->count(), 'archived');
    }

    public function bulkDelete(): void
    {
        $articles = $this->selectedArticles();
        $articles->each(fn (Article $article) => $this->authorize('delete', $article));
        $articles->each(function (Article $article): void {
            AuditLog::record('article.deleted', $article, ['title' => $article->title, 'bulk' => true]);
            $article->delete();
        });

        $this->finishBulkAction($articles->count(), 'deleted');
    }

    public function render()
    {
        $articles = $this->articlesQuery()->paginate(15);

        return view('livewire.dashboard.articles.index', [
            'articles' => $articles,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::query()->orderBy('name')->get(['id', 'name']),
            'summary' => [
                'all' => Article::count(),
                'published' => Article::published()->count(),
                'draft' => Article::query()->where('status', ArticleStatus::Draft)->count(),
                'scheduled' => Article::scheduled()->count(),
                'needs_review' => Article::needsReview()->count(),
                'incomplete' => Article::incomplete()->count(),
                'archived' => Article::query()->where('status', ArticleStatus::Archived)->count(),
                'trash' => Article::onlyTrashed()->count(),
            ],
        ])->layout('layouts.dashboard');
    }

    private function articlesQuery(): Builder
    {
        $query = Article::query()
            ->with(['author', 'category', 'tags'])
            ->when($this->search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('title', 'ilike', '%'.$this->search.'%')
                ->orWhere('slug', 'ilike', '%'.$this->search.'%')
                ->orWhere('excerpt', 'ilike', '%'.$this->search.'%')))
            ->when($this->category === 'none', fn (Builder $query) => $query->whereNull('category_id'))
            ->when($this->category !== 'all' && $this->category !== 'none', fn (Builder $query) => $query->where('category_id', $this->category))
            ->when($this->tag === 'none', fn (Builder $query) => $query->doesntHave('tags'))
            ->when($this->tag !== 'all' && $this->tag !== 'none', fn (Builder $query) => $query->whereHas('tags', fn (Builder $query) => $query->whereKey($this->tag)));

        match ($this->status) {
            'published' => $query->published(),
            'draft' => $query->where('status', ArticleStatus::Draft),
            'scheduled' => $query->scheduled(),
            'needs_review' => $query->needsReview(),
            'incomplete' => $query->incomplete(),
            'archived' => $query->where('status', ArticleStatus::Archived),
            'trash' => $query->onlyTrashed(),
            default => null,
        };

        if (in_array($this->dateRange, ['older_30', 'older_180'], true)) {
            $query->where('updated_at', '<', now()->subDays($this->dateRange === 'older_30' ? 30 : 180));
        } elseif ($this->dateRange !== 'all') {
            $query->where('updated_at', '>=', now()->subDays((int) $this->dateRange));
        }

        return match ($this->sort) {
            'published_desc' => $query->orderByDesc('published_at'),
            'created_desc' => $query->latest('created_at'),
            'title_asc' => $query->orderBy('title'),
            default => $query->latest('updated_at'),
        };
    }

    private function selectedArticles()
    {
        return Article::query()->whereIn('id', $this->selected)->get();
    }

    private function resetPaginationAndSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
        $this->resetPage();
    }

    private function finishBulkAction(int $count, string $action): void
    {
        $this->selected = [];
        $this->selectPage = false;
        session()->flash('success', $count.' article'.($count === 1 ? '' : 's').' '.$action.'.');
    }
}
