<?php

namespace App\Actions\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;

class UnpublishArticle
{
    public function handle(Article $article): Article
    {
        $article->update(['status' => ArticleStatus::Draft, 'published_at' => null, 'scheduled_at' => null, 'review_requested_at' => null]);

        return $article->refresh();
    }
}
