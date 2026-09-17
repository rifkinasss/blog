<?php

namespace App\Actions\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;

class CancelScheduledArticle
{
    public function handle(Article $article): Article
    {
        $article->update([
            'status' => ArticleStatus::Draft,
            'scheduled_at' => null,
            'published_at' => null,
        ]);

        return $article->refresh();
    }
}
