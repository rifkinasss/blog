<?php

namespace App\Actions\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\AuditLog;
use Illuminate\Support\Str;

class UpdateArticle
{
    public function handle(Article $article, array $data): Article
    {
        $status = ArticleStatus::from($data['status'] ?? $article->status->value);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $article->slug, $article);
        $data['status'] = $status;
        $data['published_at'] = $status === ArticleStatus::Published
            ? ($data['published_at'] ?? $article->published_at ?? now())
            : null;
        $data['scheduled_at'] = $status === ArticleStatus::Scheduled ? ($data['scheduled_at'] ?? $article->scheduled_at) : null;
        $data['review_requested_at'] = $status === ArticleStatus::Published ? null : ($data['review_requested_at'] ?? $article->review_requested_at);

        $article->update($data);
        AuditLog::record('article.updated', $article, ['title' => $article->title, 'status' => $article->status->value]);

        return $article->refresh();
    }

    private function uniqueSlug(string $value, Article $article): string
    {
        $slug = Str::slug($value) ?: $article->slug;
        $original = $slug;
        $counter = 2;

        while (Article::where('slug', $slug)->whereKeyNot($article)->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }
}
