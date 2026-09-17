<div class="mx-auto max-w-[1440px] space-y-6">
    <header class="flex flex-col gap-4 border-b border-zinc-200 pb-5 dark:border-zinc-800 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <nav class="flex items-center gap-2 text-xs text-zinc-500" aria-label="Breadcrumb"><a href="{{ route('dashboard.articles.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Articles</a><span>/</span><span>{{ $article ? 'Edit' : 'New article' }}</span></nav>
            <div class="mt-3 flex flex-wrap items-center gap-3"><flux:heading size="xl">{{ $article ? 'Edit article' : 'New article' }}</flux:heading><flux:badge :color="$status === 'published' ? 'green' : ($status === 'draft' ? 'amber' : 'zinc')">{{ ucfirst($status) }}</flux:badge></div>
            <flux:text class="mt-1 text-sm">{{ $article ? 'Last saved '.$article->updated_at->diffForHumans() : 'Not saved yet. Start with a working title and a first paragraph.' }}</flux:text>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($article)
                <flux:button type="button" wire:click="openPreview" size="sm" variant="ghost" icon="arrow-top-right-on-square">Open preview</flux:button>
            @endif
            <flux:button type="button" wire:click="saveAsDraft" wire:loading.attr="disabled" size="sm" variant="outline">Save draft</flux:button>
            <flux:button type="button" wire:click="schedule" wire:loading.attr="disabled" size="sm" variant="outline" icon="calendar-days">Schedule</flux:button>
            <flux:button type="button" wire:click="publish" wire:loading.attr="disabled" size="sm" variant="primary" icon="arrow-up-tray">{{ $status === 'published' ? 'Update article' : 'Publish now' }}</flux:button>
        </div>
    </header>

    @if (session('success'))
        <flux:callout variant="success">{{ session('success') }}</flux:callout>
    @endif
    @error('preview') <flux:callout variant="warning">{{ $message }}</flux:callout> @enderror

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <main class="min-w-0">
            <div class="space-y-6">
                <div>
                    <flux:input wire:model.live="title" aria-label="Article title" placeholder="Untitled article" class="text-2xl font-semibold tracking-tight sm:text-3xl" />
                    @error('title') <flux:error class="mt-2">{{ $message }}</flux:error> @enderror
                </div>

                <div class="border-y border-zinc-200 py-4 dark:border-zinc-800">
                    <div class="grid gap-3 sm:grid-cols-[10rem_minmax(0,1fr)] sm:items-center">
                        <flux:text class="font-mono text-xs text-zinc-500">/articles/</flux:text>
                        <div><flux:input wire:model.live="slug" aria-label="Article URL slug" placeholder="generated-from-title" class="font-mono text-sm" />@if ($slugIssue)<flux:error class="mt-2">{{ $slugIssue }}</flux:error>@else<flux:text class="mt-2 text-xs">Lowercase URL identifier. It stops following the title after you edit it.</flux:text>@endif</div>
                    </div>
                </div>

                <div>
                    <flux:textarea wire:model.live="excerpt" label="Excerpt" rows="3" placeholder="A concise summary for listings, search, and social previews." />
                    @error('excerpt') <flux:error class="mt-2">{{ $message }}</flux:error> @enderror
                </div>

                <section class="border-t border-zinc-200 pt-5 dark:border-zinc-800">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                        <div><flux:heading size="lg">Article body</flux:heading><flux:text class="mt-1">Markdown source with {{ number_format($wordCount) }} words and an estimated {{ $readingTime }} minute read.</flux:text></div>
                        <div class="inline-flex overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800" aria-label="Editor mode"><flux:button type="button" wire:click="$set('preview', false)" :variant="! $preview ? 'primary' : 'ghost'" size="sm">Write</flux:button><flux:button type="button" wire:click="$set('preview', true)" :variant="$preview ? 'primary' : 'ghost'" size="sm">Preview</flux:button></div>
                    </div>

                    @if (! $preview)
                        <div wire:ignore class="mt-5 overflow-hidden rounded-lg border border-zinc-300 bg-white font-mono text-sm focus-within:ring-2 focus-within:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900" x-data x-init="$nextTick(() => window.initMarkdownEditor($refs.editor, $wire))">
                            <textarea x-ref="editor">{{ $content }}</textarea>
                        </div>
                        <flux:text class="mt-2 text-xs">Markdown is stored as source. Use the Preview tab to inspect the safe public rendering.</flux:text>
                        @error('content') <flux:error class="mt-2">{{ $message }}</flux:error> @enderror
                    @else
                        <article class="prose prose-stone mt-5 max-w-none border-y border-zinc-200 py-6 dark:prose-invert dark:border-zinc-800">@markdown($content)</article>
                    @endif
                </section>
            </div>
        </main>

        <aside class="space-y-5 xl:border-s xl:border-zinc-200 xl:ps-6 dark:border-zinc-800">
            @if ($editorialWarnings->isNotEmpty())
                <flux:callout variant="warning" icon="exclamation-triangle">
                    <flux:heading size="sm">Editorial checks</flux:heading>
                    <ul class="mt-2 space-y-1 text-sm">@foreach ($editorialWarnings as $warning)<li>{{ $warning }}</li>@endforeach</ul>
                </flux:callout>
            @endif

            <section class="space-y-4 border-b border-zinc-200 pb-5 dark:border-zinc-800">
                <div><flux:heading size="lg">Publication</flux:heading><flux:text class="mt-1">Control visibility and publishing time.</flux:text></div>
                <flux:select wire:model.live="status" label="Status"><option value="draft">Draft</option><option value="scheduled">Scheduled</option><option value="published">Published</option><option value="archived">Archived</option></flux:select>
                @if (auth()->user()->isAdministrator())
                    <flux:select wire:model="author_id" label="Author"><option value="">Current signed-in user</option>@foreach ($authors as $author)<option value="{{ $author->id }}">{{ $author->name }} · {{ $author->email }}</option>@endforeach</flux:select>
                @endif
                @if ($status === 'scheduled')
                    <div><flux:input wire:model="scheduled_at" type="datetime-local" label="Schedule publication" /><flux:text class="mt-2 text-xs">Timezone: {{ $timezone }}. The article remains private until this time.</flux:text>@error('scheduled_at') <flux:error class="mt-2">{{ $message }}</flux:error> @enderror</div>
                @endif
            </section>

            <section class="space-y-4 border-b border-zinc-200 pb-5 dark:border-zinc-800">
                <div><flux:heading size="lg">Taxonomy</flux:heading><flux:text class="mt-1">Where readers will discover this article.</flux:text></div>
                <flux:select wire:model.live="category_id" label="Category"><option value="">No category</option>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</flux:select>
                <flux:checkbox.group wire:model.live="tag_ids" label="Tags" variant="pills">@foreach ($tags as $tag)<flux:checkbox value="{{ $tag->id }}" label="{{ $tag->name }}" />@endforeach</flux:checkbox.group>
                @error('tag_ids.*') <flux:error>{{ $message }}</flux:error> @enderror
            </section>

            <section class="space-y-4 border-b border-zinc-200 pb-5 dark:border-zinc-800">
                <div><flux:heading size="lg">Discovery metadata</flux:heading><flux:text class="mt-1">Optional information for search and sharing.</flux:text></div>
                <div><flux:input wire:model="meta_title" label="SEO title" /><flux:text class="mt-1 text-xs">{{ strlen($meta_title) }}/180 characters. Falls back to the article title.</flux:text></div>
                <div><flux:textarea wire:model="meta_description" label="Meta description" rows="3" /><flux:text class="mt-1 text-xs">{{ strlen($meta_description) }}/300 characters. Falls back to the excerpt.</flux:text></div>
                <flux:input wire:model="canonical_url" label="Canonical URL" type="url" placeholder="https://example.com/articles/original" />
                @error('canonical_url') <flux:error>{{ $message }}</flux:error> @enderror
                <div class="space-y-3">
                    <flux:select wire:model.live="existing_cover_image" label="Featured image"><option value="">No featured image</option>@foreach ($coverImages as $image)<option value="{{ $image['path'] }}">{{ $image['label'] }}</option>@endforeach</flux:select>
                    @if ($existing_cover_image)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existing_cover_image) }}" alt="" class="aspect-[16/9] w-full rounded object-cover ring-1 ring-zinc-200 dark:ring-zinc-700">
                    @endif
                    <div class="border-t border-zinc-200 pt-3 dark:border-zinc-800"><label class="block text-sm font-medium">Upload a new image<input wire:model="cover_image" type="file" accept="image/*" class="mt-3 block w-full text-sm file:mr-3 file:rounded file:border-0 file:bg-indigo-100 file:px-3 file:py-1.5 file:font-semibold file:text-indigo-700 dark:file:bg-indigo-950 dark:file:text-indigo-300"></label><flux:text class="mt-2 text-xs">Uploading replaces the selected image and adds it to Media.</flux:text>@error('cover_image') <flux:error class="mt-2">{{ $message }}</flux:error> @enderror</div>
                </div>
                <flux:input wire:model="cover_image_alt" label="Featured image alt text" placeholder="Describe the image for screen readers" />
                <flux:select wire:model="og_image" label="Social preview image"><option value="">Use featured image or site default</option>@foreach ($coverImages as $image)<option value="{{ $image['path'] }}">{{ $image['label'] }}</option>@endforeach</flux:select>
                <flux:checkbox wire:model="robots_index" label="Allow search engine indexing" description="Turn this off for a public article that should remain out of search results." />
            </section>

            @if ($article?->originServiceAccount)
                <section class="space-y-4 border-b border-zinc-200 pb-5 dark:border-zinc-800">
                    <div><flux:heading size="lg">Automation review</flux:heading><flux:text class="mt-1">Submitted by {{ $article->originServiceAccount->name }}.</flux:text></div>
                    <div class="flex items-center justify-between gap-3 text-sm"><span class="text-zinc-500">Review status</span><flux:badge :color="$article->review_status?->value === 'approved' ? 'green' : ($article->review_status?->value === 'pending' ? 'amber' : 'zinc')">{{ str($article->review_status?->value ?? 'none')->replace('_', ' ')->headline() }}</flux:badge></div>
                    @if ($article->review_status?->value === 'pending')
                        <div class="flex flex-wrap gap-2"><flux:button type="button" wire:click="approveReview" size="sm" variant="primary">Approve</flux:button><flux:button type="button" wire:click="requestChanges" wire:confirm="Request changes from the automation?" size="sm" variant="outline">Request changes</flux:button></div>
                    @elseif ($article->reviewed_at)
                        <flux:text class="text-xs">{{ str($article->review_status->value)->replace('_', ' ')->headline() }} by {{ $article->reviewer?->name ?? 'a dashboard user' }} {{ $article->reviewed_at->diffForHumans() }}.</flux:text>
                    @endif
                </section>
            @endif

            <section>
                <flux:heading size="lg">Article metadata</flux:heading>
                <dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-zinc-500">Created</dt><dd>{{ $article?->created_at?->format('d M Y, H:i') ?? 'Not saved yet' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-zinc-500">Last updated</dt><dd>{{ $article?->updated_at?->format('d M Y, H:i') ?? 'Not saved yet' }}</dd></div>@if ($article?->scheduled_at || $scheduled_at)<div class="flex justify-between gap-4"><dt class="text-zinc-500">Scheduled</dt><dd>{{ $article?->scheduled_at?->timezone($timezone)->format('d M Y, H:i') ?? $scheduled_at }}</dd></div>@endif<div class="flex justify-between gap-4"><dt class="text-zinc-500">Published</dt><dd>{{ $article?->published_at?->timezone($timezone)->format('d M Y, H:i') ?? 'Not published' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-zinc-500">Read time</dt><dd>{{ $readingTime }} min</dd></div></dl>
                @if ($article && $article->status->value !== 'archived')<flux:button type="button" wire:click="archive" wire:confirm="Archive this article?" size="sm" variant="ghost" icon="archive-box" class="mt-5">Archive article</flux:button>@endif
            </section>
        </aside>
    </div>
</div>
