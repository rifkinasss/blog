<?php

namespace App\Actions\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class ScheduleArticle
{
    public function __construct(private PublishArticle $publish) {}

    public function handle(Article $article, CarbonInterface $scheduledAt): Article
    {
        if (! $scheduledAt->isFuture()) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'Choose a future publication date and time.',
            ]);
        }

        $this->publish->assertReady($article);

        $article->update([
            'status' => ArticleStatus::Scheduled,
            'scheduled_at' => $scheduledAt,
            'published_at' => null,
            'review_requested_at' => null,
        ]);

        return $article->refresh();
    }
}
