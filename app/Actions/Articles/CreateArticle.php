<?php

namespace App\Actions\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Str;

class CreateArticle
{
    public function handle(User $author, array $data): Article
    {
        $status = ArticleStatus::from($data['status'] ?? ArticleStatus::Draft->value);

        $data['user_id'] = $author->id;
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title']);
        $data['status'] = $status;
        $data['published_at'] = $status === ArticleStatus::Published ? ($data['published_at'] ?? now()) : null;
        $data['scheduled_at'] = $status === ArticleStatus::Scheduled ? ($data['scheduled_at'] ?? null) : null;
        $data['review_requested_at'] = $status === ArticleStatus::Published ? null : ($data['review_requested_at'] ?? null);

        $article = Article::create($data);
        AuditLog::record('article.created', $article, ['title' => $article->title]);

        return $article;
    }

    private function uniqueSlug(string $value): string
    {
        $slug = Str::slug($value) ?: Str::random(8);
        $original = $slug;
        $counter = 2;

        while (Article::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }
}
