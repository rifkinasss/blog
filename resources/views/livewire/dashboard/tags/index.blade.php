<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><flux:heading size="xl">Tags</flux:heading><flux:subheading>Manage cross-cutting technologies and concepts that connect related notes.</flux:subheading></div>
        <flux:button type="button" wire:click="openCreate" variant="primary" icon="plus">Create tag</flux:button>
    </div>

    @if (session('success')) <flux:callout variant="success">{{ session('success') }}</flux:callout> @endif
    @if (session('error')) <flux:callout variant="danger">{{ session('error') }}</flux:callout> @endif

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900" aria-label="Tag summary">
        <div class="grid divide-y divide-zinc-200 sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-4 dark:divide-zinc-800">
            <div class="px-4 py-3"><flux:text class="text-xs">Total tags</flux:text><flux:heading size="lg" class="mt-1 tabular-nums">{{ $summary['total'] }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Used tags</flux:text><flux:heading size="lg" class="mt-1 tabular-nums text-indigo-700 dark:text-indigo-400">{{ $summary['used'] }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Unused tags</flux:text><flux:heading size="lg" class="mt-1 tabular-nums text-zinc-600 dark:text-zinc-400">{{ $summary['unused'] }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Most used</flux:text><p class="mt-1 truncate text-sm font-semibold">{{ $summary['mostUsed'] ? '#'.$summary['mostUsed']->name : 'No tags yet' }}</p><flux:text class="text-xs">{{ $summary['mostUsed'] ? $summary['mostUsed']->articles_count.' articles' : '' }}</flux:text></div>
        </div>
    </section>

    @if ($distribution->isNotEmpty())
        <section class="border-y border-zinc-200 py-5 dark:border-zinc-800">
            <div class="flex items-center justify-between gap-4"><div><flux:heading size="lg">Top tags</flux:heading><flux:text class="mt-1">Usage ranked against the most referenced tag.</flux:text></div><flux:text class="text-xs">Top {{ $distribution->count() }}</flux:text></div>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($distribution as $tag)
                    <div class="grid grid-cols-[minmax(0,1fr)_4rem] items-center gap-3"><div class="min-w-0"><p class="truncate text-sm font-medium">#{{ $tag->name }}</p><div class="mt-2 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-full rounded-full bg-indigo-600 dark:bg-indigo-400" style="width: {{ ($tag->articles_count / $distributionMax) * 100 }}%"></div></div></div><span class="text-right text-sm tabular-nums text-zinc-500">{{ $tag->articles_count }}</span></div>
                @endforeach
            </div>
        </section>
    @endif

    <flux:card class="overflow-hidden p-0">
        <div class="border-b border-zinc-200 px-4 py-4 dark:border-zinc-800 sm:px-5">
            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,1fr)_11rem_11rem_auto]">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search name or slug" aria-label="Search tags" />
                <flux:select wire:model.live="usage" aria-label="Filter tag usage"><option value="all">All tags</option><option value="used">Used tags</option><option value="unused">Unused tags</option></flux:select>
                <flux:select wire:model.live="sort" aria-label="Sort tags"><option value="usage_desc">Most used</option><option value="alphabetical">Alphabetical</option><option value="last_used_desc">Last used</option></flux:select>
                <flux:button type="button" wire:click="clearFilters" size="sm" variant="ghost" icon="x-mark">Clear</flux:button>
            </div>
        </div>
        <div class="flex items-center justify-between px-4 text-xs text-zinc-500 sm:px-5"><span>{{ $tags->total() }} result{{ $tags->total() === 1 ? '' : 's' }}</span>@if ($search || $usage !== 'all' || $sort !== 'usage_desc')<span>Filtered view</span>@endif</div>

        <flux:table :paginate="$tags" class="dashboard-table">
            <flux:table.columns><flux:table.column>Tag</flux:table.column><flux:table.column class="hidden md:table-cell">Slug</flux:table.column><flux:table.column>Articles</flux:table.column><flux:table.column class="hidden lg:table-cell">Last used</flux:table.column><flux:table.column align="end">Actions</flux:table.column></flux:table.columns>
            <flux:table.rows>
                @forelse ($tags as $tag)
                    <flux:table.row :key="$tag->id"><flux:table.cell variant="strong"><div class="min-w-0"><p class="truncate">#{{ $tag->name }}</p><flux:text class="mt-1 font-mono text-xs md:hidden">/{{ $tag->slug }}</flux:text></div></flux:table.cell><flux:table.cell class="hidden md:table-cell"><flux:text class="font-mono text-xs">/{{ $tag->slug }}</flux:text></flux:table.cell><flux:table.cell><div class="min-w-24"><span class="text-sm tabular-nums">{{ $tag->articles_count }}</span><div class="mt-1 h-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-full rounded-full bg-indigo-600 dark:bg-indigo-400" style="width: {{ ($tag->articles_count / $distributionMax) * 100 }}%"></div></div></div></flux:table.cell><flux:table.cell class="hidden lg:table-cell">{{ $tag->last_used_at ? \Illuminate\Support\Carbon::parse($tag->last_used_at)->diffForHumans() : 'Never used' }}</flux:table.cell><flux:table.cell align="end"><flux:dropdown position="bottom" align="end"><flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" inset="top bottom" aria-label="Actions for {{ $tag->name }}" /><flux:menu><flux:menu.item wire:click="edit({{ $tag->id }})" icon="pencil">Edit</flux:menu.item><flux:menu.item :href="route('dashboard.articles.index', ['tag' => $tag->id])" icon="document-text">View articles</flux:menu.item><flux:menu.separator /><flux:menu.item wire:click="delete({{ $tag->id }})" wire:confirm="Delete this tag?" variant="danger" icon="trash">Delete</flux:menu.item></flux:menu></flux:dropdown></flux:table.cell></flux:table.row>
                @empty
                    <flux:table.row><flux:table.cell colspan="5" class="py-14 text-center">@if ($search || $usage !== 'all')<flux:heading size="sm">No tags match these filters</flux:heading><flux:button type="button" wire:click="clearFilters" class="mt-4" size="sm" variant="outline">Clear filters</flux:button>@else<flux:icon.tag class="mx-auto size-8 text-zinc-400" /><flux:heading size="sm" class="mt-3">No tags yet</flux:heading><flux:text class="mt-1">Create a tag to connect related technical notes.</flux:text>@endif</flux:table.cell></flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="tag-form" class="w-full max-w-md">
        <div class="space-y-1"><flux:heading size="lg">{{ $editingId ? 'Edit tag' : 'Create tag' }}</flux:heading><flux:text>Tags make related ideas easier to discover.</flux:text></div>
        <form wire:submit="save" class="mt-6 space-y-5"><flux:input wire:model="name" label="Name" placeholder="e.g. PostgreSQL" autofocus />@error('name') <flux:error>{{ $message }}</flux:error> @enderror<div class="flex justify-end gap-3 pt-2"><flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary" icon="check">Save tag</flux:button></div></form>
    </flux:modal>
</div>
