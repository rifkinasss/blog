<div class="mx-auto max-w-[1440px] space-y-7">
    <header class="flex flex-col gap-4 border-b border-zinc-200 pb-5 dark:border-zinc-800 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">OpenClaw automation</flux:heading>
            <flux:text class="mt-1">Review the service account’s access and set the publishing boundary for automated work.</flux:text>
        </div>
        <flux:button :href="route('dashboard.users.index')" variant="outline" size="sm" icon="key">Manage service token</flux:button>
    </header>

    @if (session('success'))
        <flux:callout variant="success">{{ session('success') }}</flux:callout>
    @endif

    @if (! $service)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:heading size="sm">OpenClaw has not been created</flux:heading>
            <flux:text class="mt-1">Create the OpenClaw service account in Users &amp; Access, then issue a scoped token. Dashboard credentials must not be used by the automation.</flux:text>
        </flux:callout>
    @else
        <section class="grid gap-5 border-b border-zinc-200 pb-6 lg:grid-cols-[minmax(0,1fr)_20rem] dark:border-zinc-800">
            <div class="min-w-0">
                <div class="flex items-center gap-3"><flux:avatar name="{{ $service->name }}" size="sm" circle /><div><flux:heading size="lg">{{ $service->name }}</flux:heading><flux:text>Service account · {{ $service->enabled ? 'Enabled' : 'Disabled' }}</flux:text></div></div>
                <p class="mt-4 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $service->description }}</p>
                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-3"><div><dt class="text-zinc-500">Content owner</dt><dd class="mt-1 font-medium">{{ $service->contentUser?->name ?? 'Unavailable' }}</dd></div><div><dt class="text-zinc-500">Token</dt><dd class="mt-1 font-mono text-xs">{{ $token?->token_prefix ?? 'No active token' }}</dd></div><div><dt class="text-zinc-500">Last used</dt><dd class="mt-1 font-medium">{{ $token?->last_used_at?->diffForHumans() ?? 'Never' }}</dd></div></dl>
            </div>
            <div class="border-t border-zinc-200 pt-4 lg:border-s lg:border-t-0 lg:ps-5 lg:pt-0 dark:border-zinc-800"><flux:heading size="sm">Granted scopes</flux:heading><div class="mt-3 flex flex-wrap gap-2">@forelse ($scopes as $scope)<flux:badge color="zinc">{{ $scope }}</flux:badge>@empty<flux:text class="text-sm">No active token scopes.</flux:text>@endforelse</div></div>
        </section>

        <section class="grid gap-4 border-b border-zinc-200 pb-6 sm:grid-cols-2 xl:grid-cols-4 dark:border-zinc-800">
            @foreach (['Drafts' => $metrics['drafts'], 'Awaiting review' => $metrics['pendingReview'], 'Scheduled' => $metrics['scheduled'], 'Published' => $metrics['published']] as $label => $value)
                <div class="border-s border-zinc-200 ps-4 first:border-s-0 first:ps-0 dark:border-zinc-800"><p class="text-sm text-zinc-500">{{ $label }}</p><p class="mt-1 font-display text-2xl font-semibold tracking-tight">{{ $value }}</p></div>
            @endforeach
        </section>
    @endif

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <form wire:submit="savePolicy" class="min-w-0">
            <div class="flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800"><div><flux:heading size="lg">Publishing policy</flux:heading><flux:text class="mt-1">Least privilege is the default. Allow publication only when it is intentional.</flux:text></div><flux:button type="submit" variant="primary" size="sm">Save policy</flux:button></div>
            <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                <div class="py-4"><flux:checkbox wire:model="allow_create_drafts" label="Create drafts" description="OpenClaw may create new articles, always starting as drafts." /></div>
                <div class="py-4"><flux:checkbox wire:model="allow_update_own_drafts" label="Update its own drafts" description="The service cannot update articles it did not originate." /></div>
                <div class="py-4"><flux:checkbox wire:model="allow_upload_media" label="Upload media" description="Applies to the media upload endpoint when its token includes media:upload." /></div>
                <div class="py-4"><flux:checkbox wire:model="allow_request_review" label="Request review" description="Lets the service submit a completed draft for a human decision." /></div>
                <div class="py-4"><flux:checkbox wire:model="allow_schedule" label="Schedule approved articles" description="Requires articles:schedule and, when enabled, human approval." /></div>
                <div class="py-4"><flux:checkbox wire:model="allow_publish" label="Publish approved articles" description="Requires articles:publish; leave disabled for human-only publishing." /></div>
                <div class="py-4"><flux:checkbox wire:model="require_human_review" label="Require human review before scheduling or publishing" description="The recommended guardrail for automated content." /></div>
            </div>
        </form>

        <section class="min-w-0 border-t border-zinc-200 pt-5 xl:border-s xl:border-t-0 xl:ps-6 xl:pt-0 dark:border-zinc-800">
            <flux:heading size="lg">Recent automation activity</flux:heading>
            <flux:text class="mt-1">Only recorded operations are shown.</flux:text>
            <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($activity as $entry)
                    <div class="py-3 first:pt-0"><p class="text-sm font-medium">{{ $entry->actionLabel() }}</p><p class="mt-1 text-xs text-zinc-500">{{ $entry->created_at->diffForHumans() }}@if (data_get($entry->properties, 'title')) · {{ data_get($entry->properties, 'title') }}@endif</p></div>
                @empty
                    <flux:text class="mt-4 text-sm">No OpenClaw operations have been recorded yet.</flux:text>
                @endforelse
            </div>
        </section>
    </div>
</div>
