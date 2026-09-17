<?php

namespace App\Livewire\Dashboard\Articles;

use App\Actions\Articles\CreateArticle;
use App\Actions\Articles\UpdateArticle;
use App\Enums\ArticleStatus;
use App\Enums\ReviewStatus;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ArticleForm extends Component
{
    use WithFileUploads;

    public ?Article $article = null;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $content = '';

    public string $status = 'draft';

    public string $published_at = '';

    public string $scheduled_at = '';

    public string $meta_title = '';

    public string $meta_description = '';

    public string $canonical_url = '';

    public string $og_image = '';

    public bool $robots_index = true;

    public string $cover_image_alt = '';

    public array $tag_ids = [];

    public mixed $category_id = null;

    public mixed $author_id = null;

    public mixed $cover_image = null;

    public string $existing_cover_image = '';

    public bool $preview = false;

    public bool $slugManuallyEdited = false;

    public function mount(?Article $article = null): void
    {
        $this->article = $article?->exists ? $article : null;

        $this->authorize($this->article ? 'update' : 'create', $this->article ?? Article::class);

        if (! $this->article) {
            $this->status = SiteSetting::value('default_article_status', ArticleStatus::Draft->value);
            $this->category_id = SiteSetting::value('default_category_id');
            $this->author_id = auth()->user()->isAdministrator()
                ? SiteSetting::value('default_author_id', (string) auth()->id())
                : auth()->id();

            return;
        }

        $this->title = $this->article->title;
        $this->slug = $this->article->slug;
        $this->excerpt = $this->article->excerpt ?? '';
        $this->content = $this->article->content ?? '';
        $this->meta_title = $this->article->meta_title ?? '';
        $this->meta_description = $this->article->meta_description ?? '';
        $this->canonical_url = $this->article->canonical_url ?? '';
        $this->og_image = $this->article->og_image ?? '';
        $this->robots_index = $this->article->robots_index ?? true;
        $this->cover_image_alt = $this->article->cover_image_alt ?? '';
        $this->category_id = $this->article->category_id;

        $this->status = $this->article->status->value;
        $this->author_id = $this->article->user_id;
        $this->slugManuallyEdited = true;
        $this->published_at = $this->article->published_at?->format('Y-m-d\TH:i') ?? '';
        $this->scheduled_at = $this->article->scheduled_at?->timezone(config('app.timezone'))->format('Y-m-d\TH:i') ?? '';
        $this->existing_cover_image = $this->article->cover_image ?? '';
        $this->tag_ids = $this->article->tags()->pluck('tags.id')->all();
    }

    public function updatedTitle(string $title): void
    {
        if (! $this->slugManuallyEdited && filter_var(SiteSetting::value('auto_generate_slug', '1'), FILTER_VALIDATE_BOOLEAN)) {
            $this->slug = Str::slug($title);
        }
    }

    public function updatedSlug(): void
    {
        $this->slugManuallyEdited = true;
    }

    public function save(CreateArticle $create, UpdateArticle $update): void
    {
        $this->persist($create, $update);
    }

    public function saveAsDraft(CreateArticle $create, UpdateArticle $update): void
    {
        $this->status = ArticleStatus::Draft->value;

        $this->persist($create, $update);
    }

    public function publish(CreateArticle $create, UpdateArticle $update): void
    {
        $this->status = ArticleStatus::Published->value;
        $this->scheduled_at = '';

        $this->persist($create, $update);
    }

    public function schedule(CreateArticle $create, UpdateArticle $update): void
    {
        $this->status = ArticleStatus::Scheduled->value;

        $this->persist($create, $update);
    }

    public function archive(CreateArticle $create, UpdateArticle $update): void
    {
        $this->status = ArticleStatus::Archived->value;
        $this->scheduled_at = '';

        $this->persist($create, $update);
    }

    public function approveReview(): void
    {
        abort_unless($this->article, 404);
        $this->authorize('update', $this->article);
        abort_unless($this->article->review_status === ReviewStatus::Pending, 422, 'This article is not awaiting review.');

        $this->article->update([
            'review_status' => ReviewStatus::Approved,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        AuditLog::record('article.review_approved', $this->article, ['title' => $this->article->title]);
        session()->flash('success', 'Review approved. The article can now be scheduled or published according to policy.');
    }

    public function requestChanges(): void
    {
        abort_unless($this->article, 404);
        $this->authorize('update', $this->article);
        abort_unless($this->article->review_status === ReviewStatus::Pending, 422, 'This article is not awaiting review.');

        $this->article->update([
            'review_status' => ReviewStatus::ChangesRequested,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        AuditLog::record('article.review_changes_requested', $this->article, ['title' => $this->article->title]);
        session()->flash('success', 'Changes requested. The automation can update the draft and submit it again.');
    }

    private function persist(CreateArticle $create, UpdateArticle $update): void
    {
        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'slug' => [filter_var(SiteSetting::value('auto_generate_slug', '1'), FILTER_VALIDATE_BOOLEAN) ? 'nullable' : 'required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('articles', 'slug')->ignore($this->article?->id)],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::enum(ArticleStatus::class)],
            'published_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date', Rule::requiredIf($this->status === ArticleStatus::Scheduled->value), 'after:now'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'author_id' => ['nullable', 'exists:users,id'],
            'tag_ids' => ['array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'existing_cover_image' => ['nullable', 'string', Rule::in($this->availableCoverImages())],
            'cover_image_alt' => ['nullable', 'string', 'max:180'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'og_image' => ['nullable', 'string', Rule::in($this->availableCoverImages())],
            'robots_index' => ['boolean'],
        ];

        if (in_array($this->status, [ArticleStatus::Published->value, ArticleStatus::Scheduled->value], true) && filter_var(SiteSetting::value('require_category_before_publish', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $rules['category_id'][] = 'required';
        }

        if (in_array($this->status, [ArticleStatus::Published->value, ArticleStatus::Scheduled->value], true) && filter_var(SiteSetting::value('require_excerpt_before_publish', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $rules['excerpt'][] = 'required';
        }

        $data = $this->validate($rules);

        $tagIds = $data['tag_ids'];
        $authorId = $data['author_id'] ?? auth()->id();
        unset($data['tag_ids'], $data['author_id']);

        if ($data['published_at']) {
            $data['published_at'] = Carbon::parse($data['published_at']);
        }

        if ($data['scheduled_at']) {
            $data['scheduled_at'] = Carbon::parse($data['scheduled_at'], config('app.timezone'));
        }

        if ($this->cover_image) {
            $data['cover_image'] = $this->cover_image->store('covers', 'public');
        } else {
            $data['cover_image'] = $data['existing_cover_image'] ?: null;
        }

        unset($data['existing_cover_image']);

        $author = auth()->user()->isAdministrator() ? User::findOrFail($authorId) : auth()->user();

        if ($this->article && auth()->user()->isAdministrator()) {
            $data['user_id'] = $author->id;
        }

        $previousStatus = $this->article?->status;
        $previousScheduledAt = $this->article?->scheduled_at;

        $this->article = $this->article
            ? $update->handle($this->article, $data)
            : $create->handle($author, $data);

        $this->article->tags()->sync($tagIds);

        if ($this->article->status === ArticleStatus::Scheduled) {
            AuditLog::record(
                $previousStatus === ArticleStatus::Scheduled ? 'article.rescheduled' : 'article.scheduled',
                $this->article,
                [
                    'title' => $this->article->title,
                    'from' => $previousScheduledAt?->toIso8601String(),
                    'scheduled_at' => $this->article->scheduled_at?->toIso8601String(),
                    'previous_status' => $previousStatus?->value,
                ],
            );
        } elseif ($this->article->status === ArticleStatus::Published && $previousStatus !== ArticleStatus::Published) {
            AuditLog::record('article.published', $this->article, ['title' => $this->article->title]);
        } elseif ($this->article->status === ArticleStatus::Archived && $previousStatus !== ArticleStatus::Archived) {
            AuditLog::record('article.archived', $this->article, ['title' => $this->article->title]);
        }

        session()->flash('success', 'Artikel berhasil disimpan.');
        $this->redirectRoute('dashboard.articles.edit', $this->article);
    }

    public function openPreview(): void
    {
        if (! $this->article) {
            $this->addError('preview', 'Save the article once before opening a shareable preview.');

            return;
        }

        $this->redirect(URL::temporarySignedRoute('articles.preview', now()->addHour(), [
            'article' => $this->article,
        ]), navigate: false);
    }

    public function render()
    {
        $words = str_word_count(strip_tags($this->content));
        $slugIssue = null;

        if ($this->slug !== '') {
            if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $this->slug)) {
                $slugIssue = 'Slug may only use lowercase letters, numbers, and hyphens.';
            } elseif (Article::query()->where('slug', $this->slug)->when($this->article, fn ($query) => $query->whereKeyNot($this->article))->exists()) {
                $slugIssue = 'This slug is already used by another article.';
            }
        }

        $editorialWarnings = collect([
            trim($this->excerpt) === '' ? 'Add an excerpt for article listings and search previews.' : null,
            ! $this->category_id ? 'Choose a category so readers can browse this article by topic.' : null,
            count($this->tag_ids) === 0 ? 'Add at least one tag to connect related notes.' : null,
            ! $this->cover_image && $this->existing_cover_image === '' ? 'Choose a featured image to give the article a clear visual identity.' : null,
            trim($this->content) === '' ? 'Write article content before saving or publishing.' : null,
            $slugIssue,
            $this->robots_index ? null : 'This article will be excluded from search engine indexing.',
        ])->filter()->values();

        return view('livewire.dashboard.articles.form', [
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'authors' => User::orderBy('name')->get(['id', 'name', 'email']),
            'statuses' => ArticleStatus::cases(),
            'timezone' => config('app.timezone'),
            'wordCount' => $words,
            'readingTime' => max(1, (int) ceil($words / 200)),
            'editorialWarnings' => $editorialWarnings,
            'slugIssue' => $slugIssue,
            'coverImages' => collect($this->availableCoverImages())->map(fn (string $path) => [
                'path' => $path,
                'label' => basename($path),
                'url' => Storage::disk('public')->url($path),
            ]),
            'reviewStatus' => $this->article?->review_status,
        ])->layout('layouts.dashboard');
    }

    /** @return array<int, string> */
    private function availableCoverImages(): array
    {
        return Storage::disk('public')->files('covers');
    }
}
