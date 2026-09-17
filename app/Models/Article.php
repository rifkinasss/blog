<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'category_id', 'origin_service_account_id', 'reviewed_by', 'title', 'slug', 'excerpt', 'content', 'cover_image', 'cover_image_alt', 'og_image', 'status', 'scheduled_at', 'published_at', 'review_requested_at', 'review_status', 'reviewed_at', 'meta_title', 'meta_description', 'canonical_url', 'robots_index'];

    protected function casts(): array
    {
        return ['status' => ArticleStatus::class, 'review_status' => ReviewStatus::class, 'robots_index' => 'boolean', 'scheduled_at' => 'datetime', 'published_at' => 'datetime', 'review_requested_at' => 'datetime', 'reviewed_at' => 'datetime'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function originServiceAccount(): BelongsTo
    {
        return $this->belongsTo(ServiceAccount::class, 'origin_service_account_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', ArticleStatus::Published)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeScheduled(Builder $query): void
    {
        $query->where('status', ArticleStatus::Scheduled)->whereNotNull('scheduled_at');
    }

    public function scopeNeedsReview(Builder $query): void
    {
        $query->where('review_status', ReviewStatus::Pending);
    }

    public function scopeIncomplete(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            $query->whereNull('category_id')
                ->orWhereNull('excerpt')->orWhere('excerpt', '')
                ->orWhereNull('cover_image')->orWhere('cover_image', '')
                ->orWhereNull('content')->orWhere('content', '')
                ->orWhereDoesntHave('tags');
        });
    }

    public function isScheduled(): bool
    {
        return $this->status === ArticleStatus::Scheduled;
    }

    /** @return array<int, string> */
    public function editorialIssues(): array
    {
        return array_values(array_filter([
            ! $this->category_id ? 'Missing category' : null,
            blank($this->excerpt) ? 'Missing excerpt' : null,
            blank($this->cover_image) ? 'Missing featured image' : null,
            blank($this->content) ? 'Empty content' : null,
            $this->relationLoaded('tags') && $this->tags->isEmpty() ? 'No tags' : null,
        ]));
    }

    public function wordCount(): int
    {
        return str_word_count(strip_tags($this->content));
    }

    public function readingTime(): int
    {
        return max(1, (int) ceil($this->wordCount() / 200));
    }
}
