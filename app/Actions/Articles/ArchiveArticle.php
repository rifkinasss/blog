<?php

namespace App\Actions\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;

class ArchiveArticle
{
    public function handle(Article $article): Article
    {
        $article->update(['status' => ArticleStatus::Archived, 'review_requested_at' => null]);

        return $article->refresh();
    }
}
