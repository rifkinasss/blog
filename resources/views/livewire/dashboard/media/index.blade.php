<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><flux:heading size="xl">Media</flux:heading><flux:subheading>Files stored for editorial cover images and public article presentation.</flux:subheading></div>
        <flux:button type="button" wire:click="openUpload" variant="primary" icon="arrow-up-tray">Upload media</flux:button>
    </div>

    @if (session('success')) <flux:callout variant="success">{{ session('success') }}</flux:callout> @endif
    @if (session('error')) <flux:callout variant="danger">{{ session('error') }}</flux:callout> @endif

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900" aria-label="Media summary">
        <div class="grid divide-y divide-zinc-200 sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-4 dark:divide-zinc-800">
            <div class="px-4 py-3"><flux:text class="text-xs">Total files</flux:text><flux:heading size="lg" class="mt-1 tabular-nums">{{ $summary['total'] }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Storage used</flux:text><flux:heading size="lg" class="mt-1">{{ $summary['size'] }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Images</flux:text><flux:heading size="lg" class="mt-1 tabular-nums text-indigo-700 dark:text-indigo-400">{{ $summary['images'] }}</flux:heading></div>
            <div class="px-4 py-3"><flux:text class="text-xs">Other files</flux:text><flux:heading size="lg" class="mt-1 tabular-nums text-zinc-600 dark:text-zinc-400">{{ $summary['other'] }}</flux:heading></div>
        </div>
    </section>

    <div class="border-b border-zinc-200 pb-4 dark:border-zinc-800">
        <div class="grid gap-3 lg:grid-cols-[minmax(16rem,1fr)_11rem_11rem_auto]">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search filename" aria-label="Search media" />
            <flux:select wire:model.live="type" aria-label="Filter file type"><option value="all">All file types</option><option value="images">Images</option><option value="other">Other files</option></flux:select>
            <flux:select wire:model.live="sort" aria-label="Sort media"><option value="newest">Newest</option><option value="oldest">Oldest</option><option value="size_desc">Largest size</option></flux:select>
            <flux:button type="button" wire:click="clearFilters" size="sm" variant="ghost" icon="x-mark">Clear</flux:button>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-zinc-500"><span>{{ $items->count() }} result{{ $items->count() === 1 ? '' : 's' }}</span>@if ($search || $type !== 'all' || $sort !== 'newest')<span>Filtered view</span>@endif</div>
    </div>

    @if ($items->isNotEmpty())
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
            @foreach ($items as $item)
                <button type="button" wire:click="openDetails('{{ $item['path'] }}')" class="group min-w-0 overflow-hidden rounded-lg border border-zinc-200 bg-white text-left transition-colors hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="aspect-[4/3] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                        @if ($item['is_image'])
                            <img src="{{ $item['url'] }}" alt="" class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-[1.02]">
                        @else
                            <div class="grid h-full place-items-center"><flux:icon.document class="size-9 text-zinc-400" /></div>
                        @endif
                    </div>
                    <div class="space-y-1.5 p-3"><p class="truncate text-sm font-medium">{{ $item['filename'] }}</p><div class="flex items-center justify-between gap-3 text-xs text-zinc-500"><span class="truncate">{{ $item['mime'] }}</span><span class="shrink-0">{{ $item['size'] }}</span></div><div class="flex items-center justify-between gap-3 text-xs text-zinc-500"><span>{{ $item['last_modified']->format('d M Y') }}</span><span>{{ $item['usage_count'] }} use{{ $item['usage_count'] === 1 ? '' : 's' }}</span></div></div>
                </button>
            @endforeach
        </div>
    @else
        <div class="border border-dashed border-zinc-300 py-14 text-center dark:border-zinc-700">
            @if ($search || $type !== 'all')
                <flux:heading size="sm">No media matches these filters</flux:heading><flux:text class="mt-1">Try a different filename or file type.</flux:text><flux:button type="button" wire:click="clearFilters" class="mt-4" size="sm" variant="outline">Clear filters</flux:button>
            @else
                <flux:icon.photo class="mx-auto size-8 text-zinc-400" /><flux:heading size="sm" class="mt-3">No media files yet</flux:heading><flux:text class="mt-1">Upload a cover image to use it in an article.</flux:text>
            @endif
        </div>
    @endif

    <flux:modal name="media-upload" class="w-full max-w-lg">
        <div class="space-y-1"><flux:heading size="lg">Upload image</flux:heading><flux:text>JPG, PNG, WebP, or GIF up to 4 MB.</flux:text></div>
        <form wire:submit="upload" class="mt-6 space-y-5"><input wire:model="upload" type="file" accept="image/*" class="block w-full text-sm">@error('upload') <flux:error>{{ $message }}</flux:error> @enderror<div class="flex justify-end gap-3"><flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close><flux:button type="submit" variant="primary" wire:loading.attr="disabled">Upload</flux:button></div></form>
    </flux:modal>

    <flux:modal name="media-details" class="w-full max-w-2xl">
        @if ($selectedMedia)
            <div class="grid gap-6 md:grid-cols-[minmax(0,1fr)_15rem]">
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900">
                    @if ($selectedMedia['is_image'])<img src="{{ $selectedMedia['url'] }}" alt="" class="max-h-[28rem] w-full object-contain">@else<div class="grid min-h-64 place-items-center"><flux:icon.document class="size-10 text-zinc-400" /></div>@endif
                </div>
                <div class="min-w-0"><flux:heading size="lg" class="break-words">{{ $selectedMedia['filename'] }}</flux:heading><dl class="mt-5 space-y-3 text-sm"><div><dt class="text-zinc-500">Type</dt><dd class="mt-1 break-all">{{ $selectedMedia['mime'] }}</dd></div>@if ($selectedMedia['dimensions'])<div><dt class="text-zinc-500">Dimensions</dt><dd class="mt-1">{{ $selectedMedia['dimensions'] }}</dd></div>@endif<div><dt class="text-zinc-500">File size</dt><dd class="mt-1">{{ $selectedMedia['size'] }}</dd></div><div><dt class="text-zinc-500">Last modified</dt><dd class="mt-1">{{ $selectedMedia['last_modified']->format('d M Y, H:i') }}</dd></div><div><dt class="text-zinc-500">Article usage</dt><dd class="mt-1">{{ $selectedMedia['usage_count'] }} article{{ $selectedMedia['usage_count'] === 1 ? '' : 's' }}</dd></div></dl></div>
            </div>
            <div class="mt-6 border-t border-zinc-200 pt-5 dark:border-zinc-800"><flux:text class="text-xs">Public URL</flux:text><div class="mt-2 flex gap-2" x-data="{ copied: false }"><flux:input value="{{ url($selectedMedia['url']) }}" readonly aria-label="Media URL" /><flux:button type="button" size="sm" variant="outline" x-on:click="navigator.clipboard.writeText('{{ url($selectedMedia['url']) }}'); copied = true; setTimeout(() => copied = false, 1500)"><span x-show="!copied">Copy URL</span><span x-show="copied" x-cloak>Copied</span></flux:button></div></div>
            <div class="mt-6 flex justify-end gap-3"><flux:modal.close><flux:button variant="ghost">Close</flux:button></flux:modal.close>@if ($selectedMedia['usage_count'] === 0 && auth()->user()->isAdministrator())<flux:button type="button" wire:click="deleteUnused('{{ $selectedMedia['path'] }}')" wire:confirm="Delete this unused media permanently?" variant="danger" icon="trash">Delete</flux:button>@endif</div>
        @endif
    </flux:modal>
</div>
