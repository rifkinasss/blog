<div class="space-y-6">
    <header class="flex flex-col gap-2 border-b border-zinc-200 pb-5 dark:border-zinc-800">
        <flux:heading size="xl">Activity log</flux:heading>
        <flux:subheading>Important publishing, access, and automation activity across the workspace.</flux:subheading>
    </header>

    <div
        class="grid gap-3 border-b border-zinc-200 pb-4 lg:grid-cols-[minmax(16rem,1fr)_10rem_11rem_13rem_10rem_10rem_auto] dark:border-zinc-800">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search activity"
            aria-label="Search activity" />
        <flux:select wire:model.live="actor" aria-label="Filter actor">
            <option value="all">All actors</option>
            <option value="human">Human</option>
            <option value="service">Service account</option>
            <option value="system">System</option>
        </flux:select>
        <flux:select wire:model.live="resource" aria-label="Filter resource">
            <option value="all">All resources</option>
            <option value="article">Articles</option>
            <option value="media">Media</option>
            <option value="site_settings">Settings</option>
            <option value="profile">Profile</option>
            <option value="service_account">Service accounts</option>
            <option value="api_token">API tokens</option>
            <option value="user">Users</option>
        </flux:select>
        <flux:select wire:model.live="action" aria-label="Filter action">
            <option value="all">All actions</option>
            @foreach ($actions as $event)
                <option value="{{ $event }}">{{ str($event)->replace('_', ' ')->headline() }}</option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live="from" type="date" aria-label="From date" />
        <flux:input wire:model.live="to" type="date" aria-label="To date" />
        <flux:button type="button" wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Clear
        </flux:button>
    </div>

    <div class="flex items-center justify-between text-xs text-zinc-500"><span>{{ $logs->total() }}
            event{{ $logs->total() === 1 ? '' : 's' }}</span>
        @if ($search || $actor !== 'all' || $resource !== 'all' || $action !== 'all' || $from || $to)
            <span>Filtered view</span>
        @endif
    </div>
    <flux:table :paginate="$logs" class="dashboard-table">
        <flux:table.columns>
            <flux:table.column>Activity</flux:table.column>
            <flux:table.column class="hidden md:table-cell">Actor</flux:table.column>
            <flux:table.column class="hidden lg:table-cell">Resource</flux:table.column>
            <flux:table.column class="hidden xl:table-cell">Details</flux:table.column>
            <flux:table.column align="end">When</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($logs as $log)
                @php($serviceId = data_get($log->properties, 'service_account_id'))
                <flux:table.row :key="$log->id">
                    <flux:table.cell variant="strong">
                        <div class="flex items-center justify-between gap-3"><span
                                class="text-sm">{{ $log->actionLabel() }}</span>
                            <flux:button type="button" wire:click="toggleDetails({{ $log->id }})" size="sm"
                                variant="ghost">{{ $expandedLogId === $log->id ? 'Hide' : 'Details' }}</flux:button>
                        </div>
                        @if ($expandedLogId === $log->id)
                            <div
                                class="mt-3 border-s border-zinc-200 ps-3 text-xs text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                                <p>{{ data_get($log->properties, 'title') ?? (data_get($log->properties, 'path') ?? (data_get($log->properties, 'key') ?? 'No additional details recorded.')) }}
                                </p>
                                @if (data_get($log->properties, 'scheduled_at'))
                                    <p class="mt-1">Scheduled for
                                        {{ \Illuminate\Support\Carbon::parse(data_get($log->properties, 'scheduled_at'))->timezone(config('app.timezone'))->format('d M Y · H:i') }}
                                    </p>
                                    @endif@if (data_get($log->properties, 'from'))
                                        <p class="mt-1">Rescheduled from
                                            {{ \Illuminate\Support\Carbon::parse(data_get($log->properties, 'from'))->timezone(config('app.timezone'))->format('d M Y · H:i') }}
                                        </p>
                                    @endif
                            </div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="hidden md:table-cell">
                        @if ($log->actor_type === 'service' || $serviceId)
                            <div><span
                                    class="font-medium">{{ $log->actor_name ?? ($serviceNames[$serviceId] ?? 'Service account') }}</span>
                                <flux:text class="text-xs">Service account</flux:text>
                            </div>
                        @elseif ($log->actor_type === 'human' || $log->user)
                            <div><span
                                    class="font-medium">{{ $log->actor_name ?? ($log->user?->name ?? 'Human account') }}</span>
                                <flux:text class="text-xs">Human</flux:text>
                            </div>
                        @else
                            <span class="text-zinc-500">{{ $log->actor_name ?? 'System' }}</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="hidden lg:table-cell">
                        {{ $log->subject_type ? class_basename($log->subject_type) : str($log->event)->before('.')->headline() }}
                    </flux:table.cell>
                    <flux:table.cell class="hidden max-w-sm xl:table-cell"><span
                            class="block truncate text-zinc-500">{{ data_get($log->properties, 'title') ?? (data_get($log->properties, 'path') ?? (data_get($log->properties, 'name') ?? (data_get($log->properties, 'key') ?? '—'))) }}</span>
                    </flux:table.cell>
                    <flux:table.cell align="end"><span
                            title="{{ $log->created_at->format('d M Y, H:i') }}">{{ $log->created_at->diffForHumans() }}</span>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-14 text-center">
                            <flux:heading size="sm">No activity found</flux:heading>
                            <flux:text class="mt-1">Try broadening the filters or return after an editorial action.
                            </flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
