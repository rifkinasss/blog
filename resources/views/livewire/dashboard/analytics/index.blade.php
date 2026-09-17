<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <flux:heading size="xl">Analytics</flux:heading>
            <flux:subheading>Publication behaviour, taxonomy coverage, and content health for this journal.</flux:subheading>
        </div>
        <div class="inline-flex overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800" aria-label="Publishing trend date range">
            @foreach (['30d' => '30 days', '6m' => '6 months', '12m' => '12 months', 'all' => 'All time'] as $value => $label)
                <flux:button type="button" wire:click="selectRange('{{ $value }}')" :variant="$range === $value ? 'primary' : 'ghost'" size="sm">{{ $label }}</flux:button>
            @endforeach
        </div>
    </div>

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900" aria-label="Editorial metrics">
        <div class="grid divide-y divide-zinc-200 sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-5 dark:divide-zinc-800">
            <div class="px-4 py-3"><flux:text class="text-xs">Total articles</flux:text><flux:heading size="lg" class="mt-1 tabular-nums">{{ number_format($totalArticles) }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Published</flux:text><flux:heading size="lg" class="mt-1 tabular-nums text-indigo-700 dark:text-indigo-400">{{ number_format($publishedArticles) }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Drafts</flux:text><flux:heading size="lg" class="mt-1 tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($draftArticles) }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Active publishing months</flux:text><flux:heading size="lg" class="mt-1 tabular-nums">{{ number_format($activePublishingMonths) }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Average articles per month</flux:text><flux:heading size="lg" class="mt-1 tabular-nums">{{ $averageArticlesPerMonth === null ? 'Not available' : number_format($averageArticlesPerMonth, 1) }}</flux:heading></div>
        </div>
    </section>

    <section class="border-y border-zinc-200 py-5 dark:border-zinc-800" aria-labelledby="publishing-trend-heading">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
            <div><flux:heading id="publishing-trend-heading" size="lg">Articles published over time</flux:heading><flux:text class="mt-1">{{ $range === '30d' ? 'Daily publication count for the last 30 days.' : ($range === 'all' ? 'Publication count grouped by year.' : 'Monthly publication count for the selected period.') }}</flux:text></div>
            <span class="text-sm text-zinc-500">{{ $activity->sum('count') }} published in this range</span>
        </div>
        <div class="mt-5 overflow-x-auto pb-1">
            <div class="grid h-52 min-w-[42rem] items-end gap-2 border-b border-zinc-200 px-1 dark:border-zinc-800" style="grid-template-columns: repeat({{ $activity->count() }}, minmax(2rem, 1fr));" role="img" aria-label="Published articles by selected period">
                @foreach ($activity as $point)
                    <div class="flex h-full min-w-0 flex-col items-center justify-end gap-2"><span class="text-[11px] tabular-nums text-zinc-500">{{ $point['count'] }}</span><div class="h-[calc(100%-2.5rem)] w-full rounded-t-sm bg-indigo-600 dark:bg-indigo-400" style="height: {{ $point['height'] }}%"></div><span class="whitespace-nowrap text-[11px] text-zinc-500">{{ $point['label'] }}</span></div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="xl:col-span-7" aria-labelledby="category-distribution-heading">
            <div class="flex items-center justify-between gap-4"><div><flux:heading id="category-distribution-heading" size="lg">Content distribution by category</flux:heading><flux:text class="mt-1">Categories ranked by all assigned articles, with published coverage shown separately.</flux:text></div><flux:button :href="route('dashboard.categories.index')" size="sm" variant="ghost">Manage categories</flux:button></div>
            <div class="mt-5 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
                @forelse ($categoryDistribution as $category)
                    <div class="grid gap-2 py-3 sm:grid-cols-[minmax(0,1fr)_6rem_7rem] sm:items-center"><div class="min-w-0"><p class="truncate text-sm font-medium">{{ $category->name }}</p><flux:text class="text-xs">{{ $category->published_articles_count }} published</flux:text></div><span class="text-sm tabular-nums text-zinc-500">{{ $category->articles_count }} total</span><div class="h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-full rounded-full bg-indigo-600 dark:bg-indigo-400" style="width: {{ ($category->articles_count / $categoryDistributionMax) * 100 }}%"></div></div></div>
                @empty
                    <div class="py-8 text-center"><flux:text>No categories exist yet. Create a category to measure topic coverage.</flux:text></div>
                @endforelse
            </div>
        </section>

        <section class="xl:col-span-5" aria-labelledby="status-distribution-heading">
            <flux:heading id="status-distribution-heading" size="lg">Content distribution by status</flux:heading><flux:text class="mt-1">Current editorial pipeline across all articles.</flux:text>
            <div class="mt-5 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
                @foreach ($statusDistribution as $status)
                    <div class="grid grid-cols-[minmax(0,1fr)_4rem] items-center gap-4 py-3"><div><div class="flex items-center justify-between gap-4"><span class="text-sm font-medium">{{ $status['label'] }}</span><span class="text-xs tabular-nums text-zinc-500">{{ $totalArticles ? round(($status['count'] / $totalArticles) * 100) : 0 }}%</span></div><div class="mt-2 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-full rounded-full {{ $status['tone'] === 'indigo' ? 'bg-indigo-600 dark:bg-indigo-400' : ($status['tone'] === 'amber' ? 'bg-amber-500' : 'bg-zinc-400') }}" style="width: {{ $totalArticles ? round(($status['count'] / $totalArticles) * 100) : 0 }}%"></div></div></div><span class="text-right text-sm font-semibold tabular-nums">{{ $status['count'] }}</span></div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="border-y border-zinc-200 py-5 dark:border-zinc-800" aria-labelledby="top-tags-heading">
        <div class="flex items-center justify-between gap-4"><div><flux:heading id="top-tags-heading" size="lg">Top tags</flux:heading><flux:text class="mt-1">The most reused concepts in the current article set.</flux:text></div><flux:button :href="route('dashboard.tags.index')" size="sm" variant="ghost">Manage tags</flux:button></div>
        <div class="mt-5 grid gap-x-8 gap-y-3 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($topTags as $tag)
                <div class="grid grid-cols-[minmax(0,1fr)_3rem] items-center gap-3"><div class="min-w-0"><p class="truncate text-sm font-medium">#{{ $tag->name }}</p><div class="mt-2 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-full rounded-full bg-indigo-600 dark:bg-indigo-400" style="width: {{ ($tag->articles_count / $topTagsMax) * 100 }}%"></div></div></div><span class="text-right text-sm tabular-nums text-zinc-500">{{ $tag->articles_count }}</span></div>
            @empty
                <div class="py-8 text-center md:col-span-2 xl:col-span-4"><flux:text>No tags exist yet. Add tags to connect related articles.</flux:text></div>
            @endforelse
        </div>
    </section>

    <section aria-labelledby="editorial-health-heading">
        <div><flux:heading id="editorial-health-heading" size="lg">Editorial health</flux:heading><flux:text class="mt-1">Content checks derived from article and taxonomy records.</flux:text></div>
        <div class="mt-5 overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"><div class="divide-y divide-zinc-200 dark:divide-zinc-800">
            @foreach ($health as $item)
                <a href="{{ $item['href'] }}" wire:navigate class="grid gap-2 px-4 py-3 transition-colors hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-indigo-600 dark:hover:bg-zinc-800/50 dark:focus-visible:outline-indigo-400 sm:grid-cols-[minmax(0,1fr)_8rem_12rem] sm:items-center"><div class="min-w-0"><p class="text-sm font-medium">{{ $item['label'] }}</p><flux:text class="text-xs">{{ $item['detail'] }}</flux:text></div><span class="text-sm font-semibold tabular-nums">{{ $item['count'] }}</span><span class="text-sm text-indigo-700 dark:text-indigo-400">Review records</span></a>
            @endforeach
        </div></div>
    </section>
</div>
