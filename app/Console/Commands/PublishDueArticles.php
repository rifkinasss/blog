<?php

namespace App\Console\Commands;

use App\Actions\Articles\PublishArticle;
use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PublishDueArticles extends Command
{
    protected $signature = 'scheduler:publish-due';

    protected $description = 'Publish scheduled articles whose scheduled time has arrived.';

    public function handle(PublishArticle $publish): int
    {
        $timezone = SiteSetting::value('timezone', config('app.timezone'));
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        $published = 0;
        SiteSetting::put('scheduler_last_heartbeat_at', now()->toIso8601String());
        SiteSetting::put('scheduler_last_publish_run_at', now()->toIso8601String());

        Article::query()
            ->where('status', ArticleStatus::Scheduled)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('id')
            ->eachById(function (Article $candidate) use ($publish, &$published): void {
                $didPublish = DB::transaction(function () use ($candidate, $publish): bool {
                    $article = Article::query()->lockForUpdate()->find($candidate->id);

                    if (! $article || $article->status !== ArticleStatus::Scheduled || ! $article->scheduled_at?->isPast()) {
                        return false;
                    }

                    $scheduledAt = $article->scheduled_at;
                    $publish->handle($article);
                    AuditLog::recordSystem('article.published', $article, [
                        'title' => $article->title,
                        'scheduled_at' => $scheduledAt?->toIso8601String(),
                    ]);

                    return true;
                });

                if ($didPublish) {
                    $published++;
                    SiteSetting::put('scheduler_last_successful_publish_at', now()->toIso8601String());
                }
            });

        SiteSetting::put('scheduler_last_publish_count', (string) $published);
        $this->info("Published {$published} scheduled article(s).");

        return self::SUCCESS;
    }
}
