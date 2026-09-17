<?php

namespace App\Livewire\Dashboard\Activity;

use App\Models\AuditLog;
use App\Models\ServiceAccount;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $actor = 'all';

    #[Url]
    public string $resource = 'all';

    #[Url]
    public string $action = 'all';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public ?int $expandedLogId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdministrator(), 403);
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'actor', 'resource', 'action', 'from', 'to');
        $this->resetPage();
    }

    public function toggleDetails(int $logId): void
    {
        $this->expandedLogId = $this->expandedLogId === $logId ? null : $logId;
    }

    public function render()
    {
        $logs = $this->logsQuery()->paginate(25);
        $serviceNames = ServiceAccount::query()
            ->whereIn('id', collect($logs->items())->pluck('properties.service_account_id')->filter()->unique())
            ->pluck('name', 'id');

        return view('livewire.dashboard.activity.index', [
            'logs' => $logs,
            'serviceNames' => $serviceNames,
            'actions' => AuditLog::query()->select('event')->distinct()->orderBy('event')->pluck('event'),
        ])->layout('layouts.dashboard');
    }

    private function logsQuery(): Builder
    {
        $query = AuditLog::query()->with('user')->latest();

        if ($this->search !== '') {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->where(function (Builder $query) use ($term): void {
                $query->whereRaw('LOWER(event) LIKE ?', [$term])
                    ->orWhereRaw("LOWER(COALESCE(properties::text, '')) LIKE ?", [$term]);
            });
        }

        match ($this->actor) {
            'service' => $query->where(function (Builder $query): void {
                $query->where('actor_type', 'service')->orWhereNotNull('properties->service_account_id');
            }),
            'human' => $query->where('actor_type', 'human'),
            'system' => $query->where('actor_type', 'system'),
            default => null,
        };

        if ($this->resource !== 'all') {
            $query->where('event', 'like', $this->resource.'.%');
        }

        if ($this->action !== 'all') {
            $query->where('event', $this->action);
        }

        if ($this->from !== '') {
            $query->whereDate('created_at', '>=', $this->from);
        }

        if ($this->to !== '') {
            $query->whereDate('created_at', '<=', $this->to);
        }

        return $query;
    }
}
