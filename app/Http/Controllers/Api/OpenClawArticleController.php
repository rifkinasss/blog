<?php

namespace App\Http\Controllers\Api;

use App\Actions\Articles\CreateArticle;
use App\Actions\Articles\PublishArticle;
use App\Actions\Articles\ScheduleArticle;
use App\Actions\Articles\UpdateArticle;
use App\Enums\ArticleStatus;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\ServiceAccount;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class OpenClawArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $service = $this->service($request);
        $articles = Article::query()
            ->when($service, fn ($query) => $query->where('origin_service_account_id', $service->id))
            ->when(! $service, fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['category', 'tags'])
            ->latest()
            ->paginate(25);

        return response()->json(['data' => collect($articles->items())->map(fn (Article $article) => $this->articleData($article)), 'meta' => [
            'current_page' => $articles->currentPage(),
            'last_page' => $articles->lastPage(),
            'total' => $articles->total(),
        ]]);
    }

    public function show(Request $request, Article $article): JsonResponse
    {
        $this->ownedByToken($request, $article);

        return response()->json(['data' => $this->articleData($article->load(['category', 'tags']))]);
    }

    public function store(Request $request, CreateArticle $create): JsonResponse
    {
        return $this->idempotent($request, function () use ($request, $create): JsonResponse {
            $data = $this->validated($request);
            $service = $this->service($request);

            if ($service) {
                $this->ensurePolicy('allow_create_drafts', 'allow_service_drafts', true);
                $data['status'] = ArticleStatus::Draft->value;
                $data['origin_service_account_id'] = $service->id;
            }

            $article = DB::transaction(function () use ($request, $create, $data): Article {
                $article = $create->handle($request->user(), $data);
                $article->tags()->sync($request->input('tag_ids', []));
                AuditLog::record('article.created_via_api', $article, $this->tokenProperties($request, ['title' => $article->title]));

                return $article;
            });

            return response()->json(['data' => $this->articleData($article->fresh(['category', 'tags']))], 201);
        });
    }

    public function update(Request $request, Article $article, UpdateArticle $update): JsonResponse
    {
        $this->ownedByToken($request, $article);
        $service = $this->service($request);

        if ($service) {
            $this->ensurePolicy('allow_update_own_drafts', null, true);
            abort_unless($article->status === ArticleStatus::Draft, 422, 'Service accounts may only update their own drafts.');
        }

        $data = $this->validated($request, $article);
        $article = DB::transaction(function () use ($request, $update, $article, $data): Article {
            $article = $update->handle($article, $data);
            if ($request->has('tag_ids')) {
                $article->tags()->sync($request->input('tag_ids', []));
            }
            AuditLog::record('article.updated_via_api', $article, $this->tokenProperties($request, ['title' => $article->title]));

            return $article;
        });

        return response()->json(['data' => $this->articleData($article->fresh(['category', 'tags']))]);
    }

    public function requestReview(Request $request, Article $article): JsonResponse
    {
        $this->ownedByToken($request, $article);
        $this->ensurePolicy('allow_request_review', null, true);
        abort_unless($article->status === ArticleStatus::Draft, 422, 'Only draft articles can be submitted for review.');

        $article->update([
            'review_status' => ReviewStatus::Pending,
            'review_requested_at' => now(),
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
        AuditLog::record('article.review_requested', $article, $this->tokenProperties($request, ['title' => $article->title]));

        return response()->json(['data' => $this->articleData($article->fresh())]);
    }

    public function schedule(Request $request, Article $article, ScheduleArticle $schedule): JsonResponse
    {
        $this->ownedByToken($request, $article);
        $this->ensurePolicy('allow_schedule', null, false);
        $this->ensureApprovedWhenRequired($article);
        $data = $request->validate(['scheduled_at' => ['required', 'date', 'after:now']]);
        $schedule->handle($article, Carbon::parse($data['scheduled_at'], config('app.timezone')));
        AuditLog::record('article.scheduled', $article, $this->tokenProperties($request, [
            'title' => $article->title,
            'scheduled_at' => $article->scheduled_at?->toIso8601String(),
        ]));

        return response()->json(['data' => $this->articleData($article->fresh())]);
    }

    public function publish(Request $request, Article $article, PublishArticle $publish): JsonResponse
    {
        return $this->idempotent($request, function () use ($request, $article, $publish): JsonResponse {
            $this->ownedByToken($request, $article);
            $this->ensurePolicy('allow_publish', 'allow_service_publish', false);
            $this->ensureApprovedWhenRequired($article);
            $publish->handle($article);
            AuditLog::record('article.published_via_api', $article, $this->tokenProperties($request, ['title' => $article->title]));

            return response()->json(['data' => $this->articleData($article->fresh())]);
        });
    }

    public function preview(Request $request, Article $article): JsonResponse
    {
        $this->ownedByToken($request, $article);
        $expiresAt = now()->addHour();

        return response()->json(['data' => [
            'article_id' => $article->id,
            'preview_url' => URL::temporarySignedRoute('articles.preview', $expiresAt, ['article' => $article]),
            'expires_at' => $expiresAt->toIso8601String(),
        ]]);
    }

    private function ownedByToken(Request $request, Article $article): void
    {
        $service = $this->service($request);

        if ($service) {
            abort_unless($article->origin_service_account_id === $service->id, 403);

            return;
        }

        abort_unless($article->user_id === $request->user()->id, 403);
    }

    private function service(Request $request): ?ServiceAccount
    {
        return $request->attributes->get('apiToken')?->serviceAccount;
    }

    private function validated(Request $request, ?Article $article = null): array
    {
        $data = $request->validate([
            'title' => [$article ? 'sometimes' : 'required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('articles', 'slug')->ignore($article?->id)],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'content' => [$article ? 'sometimes' : 'required', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'cover_image' => ['nullable', 'string', 'max:255', 'regex:/^covers\/[A-Za-z0-9_\-\.]+$/'],
            'cover_image_alt' => ['nullable', 'string', 'max:180'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ]);

        unset($data['tag_ids']);

        return $data;
    }

    private function ensurePolicy(string $key, ?string $legacyKey, bool $default): void
    {
        $value = SiteSetting::value($key, $legacyKey ? SiteSetting::value($legacyKey, $default ? '1' : '0') : ($default ? '1' : '0'));
        abort_unless(filter_var($value, FILTER_VALIDATE_BOOLEAN), 403, 'This automation action is disabled by publishing policy.');
    }

    private function ensureApprovedWhenRequired(Article $article): void
    {
        $requiresReview = filter_var(SiteSetting::value('require_human_review', SiteSetting::value('service_publish_requires_review', '1')), FILTER_VALIDATE_BOOLEAN);

        if ($requiresReview) {
            abort_unless($article->review_status === ReviewStatus::Approved, 422, 'This article requires human approval before publication.');
        }
    }

    private function tokenProperties(Request $request, array $properties = []): array
    {
        $token = $request->attributes->get('apiToken');

        return [...$properties, 'service_account_id' => $token->service_account_id];
    }

    private function articleData(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'status' => $article->status->value,
            'review_status' => $article->review_status?->value ?? ReviewStatus::None->value,
            'scheduled_at' => $article->scheduled_at?->toIso8601String(),
            'published_at' => $article->published_at?->toIso8601String(),
            'category_id' => $article->category_id,
            'tag_ids' => $article->relationLoaded('tags') ? $article->tags->modelKeys() : null,
            'cover_image' => $article->cover_image,
            'cover_image_alt' => $article->cover_image_alt,
            'updated_at' => $article->updated_at?->toIso8601String(),
        ];
    }

    private function idempotent(Request $request, callable $operation): JsonResponse
    {
        $key = trim((string) $request->header('Idempotency-Key'));
        $service = $this->service($request);

        if ($key === '' || ! $service) {
            return $operation();
        }

        abort_if(strlen($key) > 128, 422, 'Idempotency-Key may not exceed 128 characters.');
        $cacheKey = 'api:idempotency:'.hash('sha256', $service->id.'|'.$request->method().'|'.$request->path().'|'.$key);

        if ($cached = Cache::get($cacheKey)) {
            return response()->json($cached['body'], $cached['status'])->header('Idempotency-Replayed', 'true');
        }

        $lock = Cache::lock($cacheKey.':lock', 30);
        abort_unless($lock->get(), 409, 'A request with this Idempotency-Key is already being processed.');

        try {
            if ($cached = Cache::get($cacheKey)) {
                return response()->json($cached['body'], $cached['status'])->header('Idempotency-Replayed', 'true');
            }

            $response = $operation();

            if ($response->isSuccessful()) {
                Cache::put($cacheKey, ['body' => $response->getData(true), 'status' => $response->getStatusCode()], now()->addDay());
            }

            return $response;
        } finally {
            $lock->release();
        }
    }
}
