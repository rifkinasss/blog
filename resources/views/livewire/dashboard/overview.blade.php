<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <flux:heading size="xl">Overview</flux:heading>
            <flux:subheading>Editorial state, publication momentum, and content that needs attention.</flux:subheading>
        </div>
        <div class="inline-flex overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800"
            aria-label="Publication activity date range">
            @foreach (['6' => '6 months', '12' => '12 months'] as $value => $label)
                <flux:button type="button" wire:click="selectRange('{{ $value }}')"
                    :variant="$range === $value ? 'primary' : 'ghost'" size="sm">{{ $label }}</flux:button>
            @endforeach
        </div>
    </div>

    @if (session('success'))
        <flux:callout variant="success">{{ session('success') }}</flux:callout>
    @endif

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
        aria-label="Editorial summary">
        <div
            class="grid divide-y divide-zinc-200 sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-7 dark:divide-zinc-800">
            <div class="px-4 py-3">
                <flux:text class="text-xs">All articles</flux:text>
                <flux:heading size="lg" class="mt-1 tabular-nums">{{ number_format($totalArticles) }}
                </flux:heading>
            </div>
            <div class="px-4 py-3">
                <flux:text class="text-xs">Published</flux:text>
                <flux:heading size="lg" class="mt-1 tabular-nums text-emerald-700 dark:text-emerald-400">
                    {{ number_format($publishedArticles) }}</flux:heading>
                <flux:text class="mt-1 text-xs">{{ $publishedThisMonth }} this month</flux:text>
            </div>
            <div class="px-4 py-3">
                <flux:text class="text-xs">Drafts</flux:text>
                <flux:heading size="lg" class="mt-1 tabular-nums text-amber-700 dark:text-amber-400">
                    {{ number_format($draftArticles) }}</flux:heading>
                <flux:text class="mt-1 text-xs">{{ $updatedThisWeek }} updated this week</flux:text>
            </div>
            <div class="px-4 py-3">
                <flux:text class="text-xs">Scheduled</flux:text>
                <flux:heading size="lg" class="mt-1 tabular-nums text-indigo-700 dark:text-indigo-400">
                    {{ number_format($scheduledArticles) }}</flux:heading>
                <flux:text class="mt-1 text-xs">{{ $scheduledThisWeek }} this week</flux:text>
            </div>
            <div class="px-4 py-3">
                <flux:text class="text-xs">Archived</flux:text>
                <flux:heading size="lg" class="mt-1 tabular-nums text-zinc-600 dark:text-zinc-400">
                    {{ number_format($archivedArticles) }}</flux:heading>
            </div>
            <div class="px-4 py-3">
                <flux:text class="text-xs">Categories</flux:text>
                <flux:heading size="lg" class="mt-1 tabular-nums">{{ number_format($categoryCount) }}
                </flux:heading>
            </div>
            <div class="px-4 py-3">
                <flux:text class="text-xs">Tags</flux:text>
                <flux:heading size="lg" class="mt-1 tabular-nums">{{ number_format($tagCount) }}</flux:heading>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-12">
        <flux:card class="xl:col-span-8">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                <div>
                    <flux:heading size="lg">Publishing activity</flux:heading>
                    <flux:text class="mt-1">Created and published articles per month.</flux:text>
                </div>
                <div class="flex items-center gap-3 text-xs text-zinc-500"><span
                        class="inline-flex items-center gap-1.5"><span
                            class="size-2 rounded-sm bg-zinc-300 dark:bg-zinc-600"></span>Created</span><span
                        class="inline-flex items-center gap-1.5"><span
                            class="size-2 rounded-sm bg-indigo-600 dark:bg-indigo-400"></span>Published</span></div>
            </div>
            <div class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                @if ($mostActiveMonth && $mostActiveMonth['published'] > 0)
                    {{ $mostActiveMonth['published'] }} article{{ $mostActiveMonth['published'] === 1 ? '' : 's' }}
                    published in {{ $mostActiveMonth['label'] }}, the most active month in this range.
                @else
                    No articles were published in this range.
                @endif
            </div>
            <div class="mt-6 grid h-48 items-end gap-2 border-b border-zinc-200 px-1 {{ $range === '12' ? 'grid-cols-12' : 'grid-cols-6' }} dark:border-zinc-800"
                role="img" aria-label="Created and published article counts by month">
                @foreach ($activity as $month)
                    <div class="flex h-full min-w-0 flex-col items-center justify-end gap-2">
                        <span
                            class="text-[11px] tabular-nums text-zinc-500">{{ $month['published'] }}/{{ $month['created'] }}</span>
                        <div class="flex h-[calc(100%-2.5rem)] w-full max-w-10 items-end gap-1">
                            <div class="flex-1 rounded-t-sm bg-zinc-300 dark:bg-zinc-600"
                                style="height: {{ $month['createdHeight'] }}%"></div>
                            <div class="flex-1 rounded-t-sm bg-indigo-600 dark:bg-indigo-400"
                                style="height: {{ $month['publishedHeight'] }}%"></div>
                        </div>
                        <span class="text-[11px] text-zinc-500">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </flux:card>

        <flux:card class="xl:col-span-4">
            <flux:heading size="lg">Editorial pipeline</flux:heading>
            <flux:text class="mt-1">Current distribution by publication state.</flux:text>
            <div class="mt-5 space-y-4">
                @foreach ($pipeline as $stage)
                    <div>
                        <div class="flex items-center justify-between gap-4 text-sm"><span
                                class="font-medium">{{ ucfirst($stage['status']) }}</span><span
                                class="tabular-nums text-zinc-500">{{ $stage['count'] }} ·
                                {{ $stage['percentage'] }}%</span></div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div class="h-full rounded-full {{ $stage['status'] === 'published' ? 'bg-emerald-600 dark:bg-emerald-400' : ($stage['status'] === 'scheduled' ? 'bg-indigo-600 dark:bg-indigo-400' : ($stage['status'] === 'draft' ? 'bg-amber-500' : 'bg-zinc-400')) }}"
                                style="width: {{ $stage['percentage'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:card>
    </div>

    <div class="grid gap-6 xl:grid-cols-12">
        <flux:card class="xl:col-span-7">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="lg">Category performance</flux:heading>
                    <flux:text class="mt-1">Article concentration and the most recent public publication.</flux:text>
                </div>
                <flux:button :href="route('dashboard.categories.index')" size="sm" variant="ghost">Manage
                </flux:button>
            </div>
            <div class="mt-5 divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($topCategories as $category)
                    <div
                        class="grid gap-2 py-3 first:pt-0 last:pb-0 sm:grid-cols-[minmax(0,1fr)_6rem_10rem] sm:items-center">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ $category->name }}</p>
                            <flux:text class="text-xs">
                                {{ $category->last_published_at ? 'Last published ' . \Illuminate\Support\Carbon::parse($category->last_published_at)->format('d M Y') : 'No published articles yet' }}
                            </flux:text>
                        </div><span class="text-sm tabular-nums text-zinc-500">{{ $category->articles_count }}
                            article{{ $category->articles_count === 1 ? '' : 's' }}</span>
                        <div class="h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div class="h-full rounded-full bg-indigo-600 dark:bg-indigo-400"
                                style="width: {{ $totalArticles ? round(($category->articles_count / $totalArticles) * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <flux:text>Create categories to track topic performance.</flux:text>
                    </div>
                @endforelse
            </div>
        </flux:card>

        <flux:card class="xl:col-span-5">
            <flux:heading size="lg">Quick insights</flux:heading>
            <flux:text class="mt-1">Derived from the current journal data.</flux:text>
            <div class="mt-5 divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($insights as $insight)
                    <div class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="text-sm font-medium">{{ $insight['label'] }}</p>
                            <flux:text class="text-xs">{{ $insight['detail'] }}</flux:text>
                        </div><span class="shrink-0 text-sm font-semibold tabular-nums">{{ $insight['value'] }}</span>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <flux:text>No editorial issues detected from the available data.</flux:text>
                    </div>
                @endforelse
            </div>
        </flux:card>
    </div>

    <div class="grid gap-6 xl:grid-cols-12">
        <flux:card class="xl:col-span-4">
            <flux:heading size="lg">Recent activity</flux:heading>
            <flux:text class="mt-1">
                {{ auth()->user()->isAdministrator() ? 'Latest meaningful changes in this workspace.' : 'Your latest changes.' }}
            </flux:text>
            <div class="mt-5 divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($recentActivity as $activity)
                    <div class="flex items-start gap-3 py-3 first:pt-0 last:pb-0"><span
                            class="mt-1.5 size-1.5 shrink-0 rounded-full bg-zinc-400 dark:bg-zinc-500"
                            aria-hidden="true"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">{{ $activity->actionLabel() }}</p>
                            <flux:text class="text-xs">
                                {{ $activity->actor_name ?? ($activity->user?->name ?? 'System') }} ·
                                {{ $activity->created_at->diffForHumans() }}</flux:text>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <flux:text>Activity will appear after article changes are recorded.</flux:text>
                    </div>
                @endforelse
            </div>
            @if (auth()->user()->isAdministrator())
                <flux:button :href="route('dashboard.activity.index')" size="sm" variant="ghost" class="mt-4">
                    View activity log</flux:button>
            @endif
        </flux:card>

        <flux:card class="xl:col-span-4">
            <flux:heading size="lg">Upcoming publishing</flux:heading>
            <flux:text class="mt-1">Articles waiting for their scheduled publication time.</flux:text>
            @if ($nextScheduledArticle)
                <div class="mt-5 border-s-2 border-indigo-600 ps-4 dark:border-indigo-400">
                    <p class="text-sm font-medium">{{ $nextScheduledArticle->title }}</p>
                    <flux:text class="mt-1 text-xs">
                        {{ $nextScheduledArticle->scheduled_at?->timezone(config('app.timezone'))->format('d M Y · H:i') }}
                        · {{ config('app.timezone') }}</flux:text>
                </div>
                @if ($scheduledArticles > 1)
                    <flux:text class="mt-4 text-sm">{{ $scheduledArticles - 1 }} more scheduled article{{ $scheduledArticles === 2 ? '' : 's' }}.</flux:text>
                @endif
            @else
                <div class="py-8 text-center">
                    <flux:text>No articles scheduled.</flux:text>
                </div>
            @endif
        </flux:card>

        <flux:card class="overflow-hidden p-0 xl:col-span-4">
            <div
                class="flex items-center justify-between gap-4 border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                <div>
                    <flux:heading size="lg">Recent articles</flux:heading>
                    <flux:text class="mt-1">Latest records in the editorial workspace.</flux:text>
                </div>
                <flux:button :href="route('dashboard.articles.index')" size="sm" variant="ghost">All articles
                </flux:button>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Article</flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">Category</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Published</flux:table.column>
                    <flux:table.column>Updated</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($recentArticles as $article)
                        <flux:table.row :key="$article->id">
                            <flux:table.cell variant="strong">
                                <div class="min-w-0">
                                    <p class="truncate">{{ $article->title }}</p>
                                    <flux:text class="text-xs sm:hidden">
                                        {{ $article->category?->name ?? 'Uncategorized' }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="hidden sm:table-cell">
                                {{ $article->category?->name ?? 'Uncategorized' }}</flux:table.cell>
                            <flux:table.cell><span
                                    class="text-sm {{ $article->status->value === 'published' ? 'text-emerald-700 dark:text-emerald-400' : ($article->status->value === 'draft' ? 'text-amber-700 dark:text-amber-400' : 'text-zinc-500') }}">{{ ucfirst($article->status->value) }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $article->published_at?->format('d M Y') ?? 'Not published' }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $article->updated_at->diffForHumans() }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal"
                                        inset="top bottom" aria-label="Article actions" />
                                    <flux:menu>
                                        <flux:menu.item :href="route('dashboard.articles.edit', $article)"
                                            icon="pencil">Edit</flux:menu.item>
                                        <flux:menu.item wire:click="openPreview({{ $article->id }})" icon="eye">
                                            Preview</flux:menu.item>
                                        @if ($article->status->value === 'draft')
                                            <flux:menu.item wire:click="publish({{ $article->id }})"
                                                wire:confirm="Publish this article now?" icon="arrow-up-tray">Publish
                                            </flux:menu.item>
                                        @elseif ($article->status->value === 'published')
                                            <flux:menu.item :href="route('articles.show', $article->slug)"
                                                target="_blank" icon="arrow-top-right-on-square">View public page
                                            </flux:menu.item>
                                            <flux:menu.item wire:click="unpublish({{ $article->id }})"
                                                wire:confirm="Move this article back to draft?"
                                                icon="arrow-down-tray">Unpublish</flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="py-12 text-center">No articles yet.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
