<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><flux:heading size="xl">Categories</flux:heading><flux:subheading>Track how content is distributed across durable engineering topics.</flux:subheading></div>
        <flux:button type="button" wire:click="openCreate" variant="primary" icon="plus">Create category</flux:button>
    </div>

    @if (session('success')) <flux:callout variant="success">{{ session('success') }}</flux:callout> @endif
    @if (session('error')) <flux:callout variant="danger">{{ session('error') }}</flux:callout> @endif

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900" aria-label="Category summary">
        <div class="grid divide-y divide-zinc-200 sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-4 dark:divide-zinc-800">
            <div class="px-4 py-3"><flux:text class="text-xs">Total categories</flux:text><flux:heading size="lg" class="mt-1 tabular-nums">{{ $summary['total'] }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">With articles</flux:text><flux:heading size="lg" class="mt-1 tabular-nums text-indigo-700 dark:text-indigo-400">{{ $summary['withArticles'] }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Empty categories</flux:text><flux:heading size="lg" class="mt-1 tabular-nums text-zinc-600 dark:text-zinc-400">{{ $summary['empty'] }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Largest category</flux:text><p class="mt-1 truncate text-sm font-semibold">{{ $summary['largest']?->name ?? 'No categories yet' }}</p><flux:text class="text-xs">{{ $summary['largest'] ? $summary['largest']->articles_count.' articles' : '' }}</flux:text></div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-12">
        <flux:card class="xl:col-span-8">
            <div class="flex items-center justify-between gap-4"><div><flux:heading size="lg">Category distribution</flux:heading><flux:text class="mt-1">Ranked by article count across the journal.</flux:text></div><flux:text class="text-xs">Top {{ $distribution->count() }}</flux:text></div>
            <div class="mt-5 space-y-3">
                @forelse ($distribution as $category)
                    <div class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_5rem_4rem] sm:items-center"><span class="truncate text-sm font-medium">{{ $category->name }}</span><span class="text-sm tabular-nums text-zinc-500">{{ $category->articles_count }} article{{ $category->articles_count === 1 ? '' : 's' }}</span><div class="h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-full rounded-full bg-indigo-600 dark:bg-indigo-400" style="width: {{ ($category->articles_count / $distributionMax) * 100 }}%"></div></div></div>
                @empty
                    <div class="py-8 text-center"><flux:text>Create categories to see their content distribution.</flux:text></div>
                @endforelse
            </div>
        </flux:card>

        <section class="border-t border-zinc-200 pt-5 xl:col-span-4 xl:border-s xl:border-t-0 xl:ps-6 xl:pt-0 dark:border-zinc-800">
            <flux:heading size="lg">Distribution notes</flux:heading>
            <div class="mt-4 space-y-3 text-sm"><p class="text-zinc-600 dark:text-zinc-300">Bars compare categories against the largest category in the current journal.</p><p class="text-zinc-600 dark:text-zinc-300">The table below separates published and draft work so taxonomy gaps are visible before publication.</p></div>
        </section>
    </div>

    <flux:card class="overflow-hidden p-0">
        <div class="border-b border-zinc-200 px-4 py-4 dark:border-zinc-800 sm:px-5">
            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,1fr)_11rem_11rem_auto]">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search name, slug, or description" aria-label="Search categories" />
                <flux:select wire:model.live="coverage" aria-label="Filter category coverage"><option value="all">All categories</option><option value="assigned">With articles</option><option value="empty">Empty only</option></flux:select>
                <flux:select wire:model.live="sort" aria-label="Sort categories"><option value="articles_desc">Most articles</option><option value="alphabetical">Alphabetical</option><option value="last_published_desc">Last published</option><option value="updated_desc">Recently updated</option></flux:select>
                <flux:button type="button" wire:click="clearFilters" size="sm" variant="ghost" icon="x-mark">Clear</flux:button>
            </div>
        </div>
        <div class="flex items-center justify-between px-4 text-xs text-zinc-500 sm:px-5"><span>{{ $categories->total() }} result{{ $categories->total() === 1 ? '' : 's' }}</span>@if ($search || $coverage !== 'all' || $sort !== 'articles_desc')<span>Filtered view</span>@endif</div>

        <flux:table :paginate="$categories" class="dashboard-table">
            <flux:table.columns><flux:table.column>Category</flux:table.column><flux:table.column class="hidden lg:table-cell">Slug</flux:table.column><flux:table.column>Articles</flux:table.column><flux:table.column class="hidden md:table-cell">Published</flux:table.column><flux:table.column class="hidden md:table-cell">Drafts</flux:table.column><flux:table.column class="hidden xl:table-cell">Last published</flux:table.column><flux:table.column align="end">Actions</flux:table.column></flux:table.columns>
            <flux:table.rows>
                @forelse ($categories as $category)
                    <flux:table.row :key="$category->id">
                        <flux:table.cell variant="strong"><div class="min-w-0"><p class="truncate">{{ $category->name }}</p><flux:text class="mt-1 line-clamp-1 text-xs">{{ $category->description ?: 'No description' }}</flux:text><flux:text class="mt-1 font-mono text-xs lg:hidden">/{{ $category->slug }}</flux:text></div></flux:table.cell>
                        <flux:table.cell class="hidden lg:table-cell"><flux:text class="font-mono text-xs">/{{ $category->slug }}</flux:text></flux:table.cell>
                        <flux:table.cell><div class="min-w-24"><span class="text-sm tabular-nums">{{ $category->articles_count }}</span><div class="mt-1 h-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-full rounded-full bg-indigo-600 dark:bg-indigo-400" style="width: {{ ($category->articles_count / $totalArticles) * 100 }}%"></div></div></div></flux:table.cell>
                        <flux:table.cell class="hidden md:table-cell">{{ $category->published_articles_count }}</flux:table.cell>
                        <flux:table.cell class="hidden md:table-cell">{{ $category->draft_articles_count }}</flux:table.cell>
                        <flux:table.cell class="hidden xl:table-cell">{{ $category->last_published_at ? \Illuminate\Support\Carbon::parse($category->last_published_at)->format('d M Y') : 'Not published' }}</flux:table.cell>
                        <flux:table.cell align="end"><flux:dropdown position="bottom" align="end"><flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" inset="top bottom" aria-label="Actions for {{ $category->name }}" /><flux:menu><flux:menu.item wire:click="edit({{ $category->id }})" icon="pencil">Edit</flux:menu.item><flux:menu.item :href="route('dashboard.articles.index', ['category' => $category->id])" icon="document-text">View articles</flux:menu.item><flux:menu.separator /><flux:menu.item wire:click="delete({{ $category->id }})" wire:confirm="Delete this category?" variant="danger" icon="trash">Delete</flux:menu.item></flux:menu></flux:dropdown></flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row><flux:table.cell colspan="7" class="py-14 text-center">@if ($search || $coverage !== 'all')<flux:heading size="sm">No categories match these filters</flux:heading><flux:button type="button" wire:click="clearFilters" class="mt-4" size="sm" variant="outline">Clear filters</flux:button>@else<flux:icon.folder-plus class="mx-auto size-8 text-zinc-400" /><flux:heading size="sm" class="mt-3">No categories yet</flux:heading><flux:text class="mt-1">Create a topic to organize your engineering notes.</flux:text>@endif</flux:table.cell></flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="category-form" class="w-full max-w-lg">
        <div class="space-y-1"><flux:heading size="lg">{{ $editingId ? 'Edit category' : 'Create category' }}</flux:heading><flux:text>{{ $editingId ? 'Update this topic and its public description.' : 'Create a durable topic for your engineering notes.' }}</flux:text></div>
        <form wire:submit="save" class="mt-6 space-y-5"><flux:input wire:model="name" label="Name" placeholder="e.g. Homelab" autofocus />@error('name') <flux:error>{{ $message }}</flux:error> @enderror<flux:textarea wire:model="description" label="Description" rows="4" placeholder="A short public explanation of this topic." />@error('description') <flux:error>{{ $message }}</flux:error> @enderror<div class="flex justify-end gap-3 pt-2"><flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary" icon="check">Save category</flux:button></div></form>
    </flux:modal>
</div>
