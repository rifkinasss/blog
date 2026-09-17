<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <flux:heading size="xl">Articles</flux:heading>
            <flux:subheading>{{ $summary['all'] }} {{ str('article')->plural($summary['all']) }} in the editorial workspace.</flux:subheading>
        </div>
        <flux:button :href="route('dashboard.articles.create')" variant="primary" icon="pencil-square">New article</flux:button>
    </div>

    @if (session('success'))
        <flux:callout variant="success">{{ session('success') }}</flux:callout>
    @endif

    <div class="flex flex-wrap items-center gap-x-1 gap-y-2 border-b border-zinc-200 pb-3 dark:border-zinc-800" aria-label="Article status summary">
        @foreach (['all' => 'All', 'published' => 'Published', 'draft' => 'Drafts', 'scheduled' => 'Scheduled', 'needs_review' => 'Needs review', 'incomplete' => 'Incomplete', 'archived' => 'Archived', 'trash' => 'Trash'] as $value => $label)
            <flux:button type="button" wire:click="$set('status', '{{ $value }}')" :variant="$status === $value ? 'primary' : 'ghost'" size="sm">{{ $label }} <span class="ms-1 tabular-nums opacity-70">{{ $summary[$value] }}</span></flux:button>
        @endforeach
    </div>

    <flux:card class="space-y-4 overflow-hidden p-0">
        <div class="border-b border-zinc-200 px-4 py-4 dark:border-zinc-800 sm:px-5">
            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,1fr)_10rem_10rem_10rem_10rem_auto]">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search title, slug, or excerpt" aria-label="Search articles" />
                <flux:select wire:model.live="status" aria-label="Filter status"><option value="all">All statuses</option><option value="published">Published</option><option value="draft">Draft</option><option value="scheduled">Scheduled</option><option value="needs_review">Needs review</option><option value="incomplete">Incomplete</option><option value="archived">Archived</option><option value="trash">Trash</option></flux:select>
                <flux:select wire:model.live="category" aria-label="Filter category"><option value="all">All categories</option>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</flux:select>
                <flux:select wire:model.live="tag" aria-label="Filter tag"><option value="all">All tags</option>@foreach ($tags as $tag)<option value="{{ $tag->id }}">#{{ $tag->name }}</option>@endforeach</flux:select>
                <flux:select wire:model.live="sort" aria-label="Sort articles"><option value="updated_desc">Recently updated</option><option value="published_desc">Recently published</option><option value="created_desc">Recently created</option><option value="title_asc">Title A–Z</option></flux:select>
                <div class="flex items-center gap-2"><flux:select wire:model.live="dateRange" aria-label="Filter update date"><option value="all">Any time</option><option value="7">Updated 7d</option><option value="30">Updated 30d</option><option value="90">Updated 90d</option></flux:select><flux:button type="button" wire:click="clearFilters" size="sm" variant="ghost" icon="x-mark" aria-label="Clear filters"><span class="hidden xl:inline">Clear</span></flux:button></div>
            </div>
        </div>

        @if (count($selected))
            <div class="flex flex-col gap-3 border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <flux:text><span class="font-semibold tabular-nums">{{ count($selected) }}</span> selected</flux:text>
                <div class="flex flex-wrap gap-2">
                    <flux:button type="button" wire:click="bulkPublish" wire:confirm="Publish selected draft articles?" size="sm" variant="ghost" icon="arrow-up-tray">Publish</flux:button>
                    <flux:button type="button" wire:click="bulkArchive" wire:confirm="Archive selected articles?" size="sm" variant="ghost" icon="archive-box">Archive</flux:button>
                    <flux:button type="button" wire:click="bulkDelete" wire:confirm="Delete selected articles permanently? This cannot be undone." size="sm" variant="danger" icon="trash">Delete</flux:button>
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between px-4 text-xs text-zinc-500 sm:px-5"><span>{{ $articles->total() }} result{{ $articles->total() === 1 ? '' : 's' }}</span>@if ($search || $status !== 'all' || $category !== 'all' || $tag !== 'all' || $dateRange !== 'all' || $sort !== 'updated_desc')<span>Filtered view</span>@endif</div>

        <flux:table :paginate="$articles" class="dashboard-table">
            <flux:table.columns>
                <flux:table.column class="w-10"><flux:checkbox wire:model.live="selectPage" aria-label="Select this page" /></flux:table.column>
                <flux:table.column>Article</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">Status</flux:table.column>
                <flux:table.column class="hidden xl:table-cell">Category</flux:table.column>
                <flux:table.column class="hidden 2xl:table-cell">Tags</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">Published</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">Updated</flux:table.column>
                <flux:table.column class="hidden 2xl:table-cell">Author</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($articles as $article)
                    <flux:table.row :key="$article->id">
                        <flux:table.cell><flux:checkbox wire:model.live="selected" value="{{ $article->id }}" aria-label="Select {{ $article->title }}" /></flux:table.cell>
                        <flux:table.cell variant="strong">
                            <div class="flex min-w-0 items-start gap-3">
                                @if ($article->cover_image)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($article->cover_image) }}" alt="" class="mt-0.5 size-10 shrink-0 rounded object-cover ring-1 ring-zinc-200 dark:ring-zinc-700">
                                @else
                                    <span class="mt-0.5 grid size-10 shrink-0 place-items-center rounded border border-dashed border-zinc-300 text-zinc-400 dark:border-zinc-700"><flux:icon.photo class="size-4" /></span>
                                @endif
                                <div class="min-w-0"><p class="truncate">{{ $article->title }}</p><flux:text class="mt-1 truncate text-xs">/{{ $article->slug }}@if (! $article->category_id || $article->tags->isEmpty()) <span class="text-amber-700 dark:text-amber-400"> · {{ collect([! $article->category_id ? 'no category' : null, $article->tags->isEmpty() ? 'no tags' : null])->filter()->join(', ') }}</span>@endif</flux:text><div class="mt-1 flex flex-wrap gap-x-2 text-xs lg:hidden"><span class="{{ $article->isScheduled() ? 'text-indigo-700 dark:text-indigo-400' : ($article->status->value === 'published' ? 'text-emerald-700 dark:text-emerald-400' : ($article->status->value === 'draft' ? 'text-amber-700 dark:text-amber-400' : 'text-zinc-500')) }}">{{ $article->review_status?->value === 'pending' ? 'Needs review' : ucfirst($article->status->value) }}</span><span class="text-zinc-500">{{ $article->category?->name ?? 'Uncategorized' }}</span></div></div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="hidden lg:table-cell"><flux:badge :color="$article->review_status?->value === 'pending' ? 'amber' : ($article->status->value === 'scheduled' ? 'indigo' : ($article->status->value === 'published' ? 'green' : ($article->status->value === 'draft' ? 'amber' : 'zinc')))">{{ $article->review_status?->value === 'pending' ? 'needs review' : $article->status->value }}</flux:badge></flux:table.cell>
                        <flux:table.cell class="hidden xl:table-cell">{{ $article->category?->name ?? 'Uncategorized' }}</flux:table.cell>
                        <flux:table.cell class="hidden 2xl:table-cell"><div class="flex max-w-48 flex-wrap gap-1">@forelse ($article->tags->take(3) as $tag)<span class="text-xs text-zinc-600 dark:text-zinc-300">#{{ $tag->name }}</span>@empty<span class="text-xs text-zinc-500">None</span>@endforelse@if ($article->tags->count() > 3)<span class="text-xs text-zinc-500">+{{ $article->tags->count() - 3 }}</span>@endif</div></flux:table.cell>
                        <flux:table.cell class="hidden lg:table-cell">@if ($article->isScheduled())<span class="text-indigo-700 dark:text-indigo-400">Scheduled</span><br><span class="text-xs text-zinc-500">{{ $article->scheduled_at?->timezone(config('app.timezone'))->format('d M Y · H:i') }}</span>@else{{ $article->published_at?->timezone(config('app.timezone'))->format('d M Y') ?? 'Not published' }}@endif</flux:table.cell>
                        <flux:table.cell class="hidden lg:table-cell">{{ $article->updated_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell class="hidden 2xl:table-cell">{{ $article->author?->name ?? 'Unknown' }}</flux:table.cell>
                        <flux:table.cell align="end"><flux:dropdown position="bottom" align="end"><flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" inset="top bottom" aria-label="Actions for {{ $article->title }}" /><flux:menu>@if ($article->trashed())<flux:menu.item wire:click="restore({{ $article->id }})" icon="arrow-uturn-left">Restore</flux:menu.item>@if(auth()->user()->isAdministrator())<flux:menu.separator /><flux:menu.item wire:click="forceDelete({{ $article->id }})" wire:confirm="Permanently delete this article? This cannot be undone." variant="danger" icon="trash">Permanently delete</flux:menu.item>@endif @else <flux:menu.item :href="route('dashboard.articles.edit', $article)" icon="pencil">{{ $article->isScheduled() ? 'Reschedule' : 'Edit' }}</flux:menu.item><flux:menu.item wire:click="openPreview({{ $article->id }})" icon="eye">Preview</flux:menu.item>@if ($article->status->value === 'draft')<flux:menu.item wire:click="publish({{ $article->id }})" wire:confirm="Publish this article now?" icon="arrow-up-tray">Publish</flux:menu.item>@elseif ($article->status->value === 'scheduled')<flux:menu.item wire:click="cancelSchedule({{ $article->id }})" wire:confirm="Cancel this publication schedule?" icon="calendar-days">Cancel schedule</flux:menu.item>@elseif ($article->status->value === 'published')<flux:menu.item :href="route('articles.show', $article->slug)" target="_blank" icon="arrow-top-right-on-square">View public page</flux:menu.item><flux:menu.item wire:click="unpublish({{ $article->id }})" wire:confirm="Move this article back to draft?" icon="arrow-down-tray">Unpublish</flux:menu.item>@endif@if ($article->status->value !== 'archived')<flux:menu.separator /><flux:menu.item wire:click="archive({{ $article->id }})" wire:confirm="Archive this article?" icon="archive-box">Archive</flux:menu.item>@endif<flux:menu.separator /><flux:menu.item wire:click="delete({{ $article->id }})" wire:confirm="Move this article to Trash?" variant="danger" icon="trash">Move to Trash</flux:menu.item>@endif</flux:menu></flux:dropdown></flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="py-14 text-center">
                            @if ($search || $status !== 'all' || $category !== 'all' || $tag !== 'all' || $dateRange !== 'all')
                                <flux:heading size="sm">No articles match these filters</flux:heading>
                                <flux:text class="mt-1">Clear filters or broaden the search to continue.</flux:text>
                                <flux:button type="button" wire:click="clearFilters" size="sm" variant="outline" class="mt-4">Clear filters</flux:button>
                            @else
                                <flux:icon.document-plus class="mx-auto size-8 text-zinc-400" />
                                <flux:heading size="sm" class="mt-3">No articles yet</flux:heading>
                                <flux:text class="mt-1">Start the journal with your first engineering note.</flux:text>
                                <flux:button :href="route('dashboard.articles.create')" size="sm" variant="primary" class="mt-4">New article</flux:button>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
