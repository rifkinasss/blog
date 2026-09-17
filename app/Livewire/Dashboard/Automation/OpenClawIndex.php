<?php

namespace App\Livewire\Dashboard\Automation;

use App\Enums\ArticleStatus;
use App\Enums\ReviewStatus;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\ServiceAccount;
use App\Models\SiteSetting;
use Livewire\Component;

class OpenClawIndex extends Component
{
    public bool $allow_create_drafts = true;

    public bool $allow_update_own_drafts = true;

    public bool $allow_upload_media = true;

    public bool $allow_request_review = true;

    public bool $allow_schedule = false;

    public bool $allow_publish = false;

    public bool $require_human_review = true;

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdministrator(), 403);

        $this->allow_create_drafts = $this->setting('allow_create_drafts', 'allow_service_drafts', true);
        $this->allow_update_own_drafts = $this->setting('allow_update_own_drafts', null, true);
        $this->allow_upload_media = $this->setting('allow_upload_media', null, true);
        $this->allow_request_review = $this->setting('allow_request_review', null, true);
        $this->allow_schedule = $this->setting('allow_schedule', null, false);
        $this->allow_publish = $this->setting('allow_publish', 'allow_service_publish', false);
        $this->require_human_review = $this->setting('require_human_review', 'service_publish_requires_review', true);
    }

    public function savePolicy(): void
    {
        $data = $this->validate([
            'allow_create_drafts' => ['boolean'],
            'allow_update_own_drafts' => ['boolean'],
            'allow_upload_media' => ['boolean'],
            'allow_request_review' => ['boolean'],
            'allow_schedule' => ['boolean'],
            'allow_publish' => ['boolean'],
            'require_human_review' => ['boolean'],
        ]);

        foreach ($data as $key => $value) {
            SiteSetting::put($key, $value ? '1' : '0');
        }

        // Preserve the established keys while existing deployments move to the clearer policy names.
        SiteSetting::put('allow_service_drafts', $data['allow_create_drafts'] ? '1' : '0');
        SiteSetting::put('allow_service_publish', $data['allow_publish'] ? '1' : '0');
        SiteSetting::put('service_publish_requires_review', $data['require_human_review'] ? '1' : '0');

        $service = ServiceAccount::query()->where('slug', 'openclaw')->first();
        AuditLog::record('service_account.policy_updated', $service, [
            'service_account_id' => $service?->id,
            'policy' => $data,
        ]);

        session()->flash('success', 'Automation publishing policy saved.');
    }

    public function render()
    {
        $service = ServiceAccount::query()
            ->where('slug', 'openclaw')
            ->with(['contentUser', 'apiTokens' => fn ($query) => $query->latest()])
            ->first();

        $articles = $service
            ? Article::query()->where('origin_service_account_id', $service->id)
            : Article::query()->whereRaw('1 = 0');

        return view('livewire.dashboard.automation.openclaw-index', [
            'service' => $service,
            'token' => $service?->apiTokens->first(),
            'scopes' => $service?->apiTokens->flatMap(fn ($token) => $token->abilities)->unique()->values() ?? collect(),
            'metrics' => [
                'drafts' => (clone $articles)->where('status', ArticleStatus::Draft)->count(),
                'pendingReview' => (clone $articles)->where('review_status', ReviewStatus::Pending)->count(),
                'scheduled' => (clone $articles)->where('status', ArticleStatus::Scheduled)->count(),
                'published' => (clone $articles)->where('status', ArticleStatus::Published)->count(),
            ],
            'activity' => $service
                ? AuditLog::query()->where('properties->service_account_id', $service->id)->latest()->limit(12)->get()
                : collect(),
        ])->layout('layouts.dashboard');
    }

    private function setting(string $key, ?string $legacyKey, bool $default): bool
    {
        $fallback = $legacyKey ? SiteSetting::value($legacyKey, $default ? '1' : '0') : ($default ? '1' : '0');

        return filter_var(SiteSetting::value($key, $fallback), FILTER_VALIDATE_BOOLEAN);
    }
}
