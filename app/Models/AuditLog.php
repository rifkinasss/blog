<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'actor_type', 'actor_name', 'event', 'subject_type', 'subject_id', 'description', 'properties'];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $event, ?Model $subject = null, array $properties = []): void
    {
        $user = auth()->user();
        $service = isset($properties['service_account_id'])
            ? ServiceAccount::find($properties['service_account_id'])
            : null;

        static::create([
            'user_id' => $user?->id,
            'actor_type' => $service ? 'service' : ($user ? 'human' : 'system'),
            'actor_name' => $service?->name ?? $user?->name ?? 'System',
            'event' => $event,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
        ]);
    }

    public static function recordSystem(string $event, ?Model $subject = null, array $properties = []): void
    {
        static::create([
            'actor_type' => 'system',
            'actor_name' => 'Laravel Scheduler',
            'event' => $event,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
        ]);
    }

    public function actionLabel(): string
    {
        return match ($this->event) {
            'article.created' => 'Created article',
            'article.updated' => 'Updated article',
            'article.published' => 'Published article',
            'article.scheduled' => 'Scheduled article',
            'article.rescheduled' => 'Rescheduled article',
            'article.schedule_cancelled' => 'Cancelled schedule',
            'article.archived' => 'Archived article',
            'article.review_requested' => 'Requested review',
            'article.review_approved' => 'Approved review',
            'article.review_changes_requested' => 'Requested changes',
            'article.deleted' => 'Deleted article',
            'profile.updated' => 'Updated account profile',
            'password.updated' => 'Changed password',
            'profile.avatar_updated' => 'Updated profile photo',
            'profile.avatar_removed' => 'Removed profile photo',
            'site_settings.updated' => 'Updated settings',
            default => str($this->event)->replace('_via_api', ' via API')->replace('_', ' ')->headline()->toString(),
        };
    }
}
