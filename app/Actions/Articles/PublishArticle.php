<?php

namespace App\Actions\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\SiteSetting;
use Illuminate\Validation\ValidationException;

class PublishArticle
{
    public function handle(Article $article): Article
    {
        $this->assertReady($article);

        $article->update([
            'status' => ArticleStatus::Published,
            'published_at' => $article->published_at ?? now(),
            'scheduled_at' => null,
            'review_requested_at' => null,
        ]);

        return $article->refresh();
    }

    public function assertReady(Article $article): void
    {
        $errors = [];

        if (filter_var(SiteSetting::value('require_category_before_publish', '0'), FILTER_VALIDATE_BOOLEAN) && ! $article->category_id) {
            $errors['publish'] = 'Choose a category before publishing this article.';
        }

        if (filter_var(SiteSetting::value('require_excerpt_before_publish', '0'), FILTER_VALIDATE_BOOLEAN) && blank($article->excerpt)) {
            $errors['publish'] = 'Add an excerpt before publishing this article.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
