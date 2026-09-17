@extends('layouts.blog')

@php
    $articleUrl = $canonicalUrl ?? route('articles.show', $article);
    $coverUrl = $article->cover_image ? asset('storage/'.$article->cover_image) : null;
    $readingTime = $article->readingTime();
    $wordCount = $article->wordCount();
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $article->title,
        'description' => $pageDescription,
        'datePublished' => ($article->published_at ?? $article->created_at)->toAtomString(),
        'dateModified' => $article->updated_at->toAtomString(),
        'mainEntityOfPage' => $articleUrl,
        'wordCount' => $wordCount,
        'timeRequired' => 'PT'.$readingTime.'M',
        'author' => ['@type' => 'Person', 'name' => $article->author?->name ?? ($siteAuthorName ?? 'NasLabs')],
        'image' => $pageOpenGraphImage ?? $coverUrl,
    ];
@endphp

@push('head')
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $articleUrl }}">
    <meta property="article:published_time" content="{{ ($article->published_at ?? $article->created_at)->toAtomString() }}">
    <meta property="article:modified_time" content="{{ $article->updated_at->toAtomString() }}">
    @foreach ($article->tags as $tag)<meta property="article:tag" content="{{ $tag->name }}">@endforeach
    @if ($pageOpenGraphImage ?? false)<meta property="og:image" content="{{ $pageOpenGraphImage }}">@endif
    <meta name="twitter:card" content="{{ ($pageOpenGraphImage ?? $coverUrl) ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <script type="application/ld+json">@json($structuredData)</script>
@endpush

@section('content')
    @if ($isPreview ?? false)
        <div class="border-b border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm font-medium text-amber-900 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-200">Preview mode — this article is not publicly accessible.</div>
    @endif
    <div data-reading-root class="mx-auto max-w-7xl px-4 pb-20 pt-10 md:px-12 md:pt-14">
        <header class="border-b border-[#c5c5d7]/30 pb-8">
            <nav class="flex flex-wrap items-center gap-2 font-mono text-[10px] text-[#757686]" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-[#1a35ca]">Naslabs index</a><span>/</span>
                <a href="{{ route('articles.index') }}" class="hover:text-[#1a35ca]">Articles</a><span>/</span>
                @if ($article->category)<a href="{{ route('categories.show', $article->category) }}" class="hover:text-[#1a35ca]">{{ $article->category->name }}</a><span>/</span>@endif
                <span class="text-[#1a35ca]">{{ $article->slug }}</span>
            </nav>

            <div class="mt-5 flex flex-wrap items-center gap-2 text-[10px] font-semibold uppercase tracking-wider text-[#1a35ca]">
                <span class="rounded bg-[#dfe0ff] px-2 py-1">{{ $article->category?->name ?? 'Research' }}</span>
                @foreach ($article->tags as $tag)
                    <a href="{{ route('tags.show', $tag) }}" class="rounded bg-[#eaedff] px-2 py-1 transition-colors hover:bg-[#dfe0ff] dark:bg-[#202947]">#{{ $tag->name }}</a>
                @endforeach
            </div>

            <h1 class="mt-5 max-w-5xl font-display text-4xl font-bold leading-tight tracking-tight md:text-5xl">{{ $article->title }}</h1>
            @if ($article->excerpt)
                <p class="mt-5 max-w-4xl text-base leading-7 text-[#444655] dark:text-[#c5c5d7]">{{ $article->excerpt }}</p>
            @endif

            <div class="mt-6 flex flex-wrap items-center gap-3 font-mono text-[10px] text-[#757686]">
                <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">calendar_today</span>{{ ($article->published_at ?? $article->created_at)->format($dateFormat ?? 'd M Y') }}</span>
                <span>•</span>
                <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">timer</span>{{ $readingTime }} min read</span>
                <span>•</span>
                <span>{{ number_format($wordCount) }} words</span>
                <span>•</span>
                <span>Monograph #{{ str($article->id)->padLeft(4, '0') }}</span>
            </div>

            <div class="mt-6 flex max-w-4xl flex-wrap items-center gap-3">
                <div class="inline-flex items-center gap-2 rounded bg-[#eef0ff] px-3 py-2 font-mono text-[10px] text-[#444655] dark:bg-[#202947]">
                    <span class="font-semibold uppercase tracking-wider">Reading mode</span>
                    <span>{{ $readingTime }} min</span>
                </div>
                <button type="button" data-copy-link class="inline-flex cursor-pointer items-center gap-2 rounded border border-[#c5c5d7]/50 bg-white px-3 py-2 text-xs font-medium text-[#444655] transition-colors hover:border-[#1a35ca] hover:text-[#1a35ca] dark:bg-[#171d34] dark:text-[#c5c5d7]">
                    <span class="material-symbols-outlined text-[16px]">content_copy</span><span data-copy-label>Copy article link</span>
                </button>
                <span data-copy-status class="text-xs font-medium text-emerald-600" role="status" aria-live="polite"></span>
            </div>
        </header>

        <div class="mt-10 grid gap-10 lg:grid-cols-12">
            <aside class="hidden lg:col-span-3 lg:block">
                <div class="sticky top-24 space-y-6">
                    <div class="border-l-2 border-[#1a35ca] pl-4">
                        <p class="font-mono text-[10px] uppercase tracking-widest text-[#757686]">Reading progress</p>
                        <p class="mt-2 font-display text-2xl font-semibold"><span data-progress-number>00</span><span class="font-body text-xs font-normal text-[#757686]">%</span></p>
                        <div class="mt-3 h-1 overflow-hidden rounded-full bg-[#e2e7ff]"><div data-progress-bar class="h-full w-0 rounded-full bg-[#1a35ca] transition-[width] duration-150"></div></div>
                    </div>

                    @if (count($headings))
                        <div>
                            <p class="font-mono text-[10px] uppercase tracking-widest text-[#757686]">On this page</p>
                            <nav class="mt-3 space-y-2 border-l border-[#c5c5d7]/40 pl-3 text-xs text-[#757686]" aria-label="Table of contents">
                                @foreach ($headings as $heading)
                                    <a href="#{{ $heading['id'] }}" class="{{ $heading['level'] === 3 ? 'ml-3' : '' }} block transition-colors hover:text-[#1a35ca]">{{ $heading['text'] }}</a>
                                @endforeach
                            </nav>
                        </div>
                    @endif

                    <div class="rounded-lg bg-[#eef0ff] p-4 dark:bg-[#202947]">
                        <span class="material-symbols-outlined text-[18px] text-[#1a35ca]">verified</span>
                        <p class="mt-2 text-xs font-semibold">Public field note</p>
                        <p class="mt-1 text-xs leading-5 text-[#757686]">Published openly from the Naslabs bench.</p>
                    </div>
                </div>
            </aside>

            <article class="min-w-0 lg:col-span-6" id="article-body">
                @if ($article->cover_image)
                    <figure class="mb-8 overflow-hidden rounded-lg border border-[#c5c5d7]/30 bg-white p-2 shadow-sm dark:bg-[#171d34]">
                        <img src="{{ $coverUrl }}" alt="{{ $article->cover_image_alt ?: 'Cover image for '.$article->title }}" class="max-h-[30rem] w-full rounded object-cover">
                        @if ($article->cover_image_alt)
                            <figcaption class="px-2 pb-1 pt-3 text-center text-xs leading-5 text-[#757686]">{{ $article->cover_image_alt }}</figcaption>
                        @endif
                    </figure>
                @endif

                @if ($article->excerpt)
                    <section id="abstract" class="mb-8 rounded-lg border-l-2 border-[#1a35ca] bg-[#eef0ff] p-5 dark:bg-[#202947]">
                        <p class="font-mono text-[10px] uppercase tracking-widest text-[#1a35ca]">Abstract</p>
                        <p class="mt-3 text-sm leading-6 text-[#444655] dark:text-[#c5c5d7]">{{ $article->excerpt }}</p>
                    </section>
                @endif

                <div class="prose prose-slate max-w-none text-[#131b2e] dark:prose-invert prose-headings:font-display prose-headings:scroll-mt-24 prose-headings:tracking-tight prose-a:text-[#1a35ca] prose-code:rounded prose-code:bg-[#eef0ff] prose-code:px-1 prose-code:py-0.5 prose-code:text-[#1a35ca] prose-pre:rounded-lg prose-pre:border prose-pre:border-[#c5c5d7]/30 prose-pre:bg-[#131b2e] prose-img:rounded-lg">
                    {!! $articleHtml !!}
                </div>

                <section class="mt-12 border-t border-[#c5c5d7]/30 pt-6">
                    <h2 class="font-display text-xl font-semibold">About this note</h2>
                    <p class="mt-3 text-sm leading-6 text-[#757686]">Written by {{ $article->author?->name ?? ($siteAuthorName ?? 'NasLabs') }} for the NasLabs public engineering journal. Sources and implementation details are linked in the article body.</p>
                </section>

                @if ($previousArticle || $nextArticle)
                    <nav class="mt-10 grid gap-4 border-t border-[#c5c5d7]/30 pt-6 sm:grid-cols-2" aria-label="Article navigation">
                        @if ($previousArticle)
                            <a href="{{ route('articles.show', $previousArticle) }}" class="rounded-lg border border-[#c5c5d7]/30 bg-white p-4 transition-colors hover:border-[#1a35ca] dark:bg-[#171d34]">
                                <span class="font-mono text-[10px] uppercase tracking-wider text-[#757686]">← Previous article</span>
                                <span class="mt-2 block font-display text-sm font-semibold">{{ $previousArticle->title }}</span>
                            </a>
                        @else
                            <div></div>
                        @endif
                        @if ($nextArticle)
                            <a href="{{ route('articles.show', $nextArticle) }}" class="rounded-lg border border-[#c5c5d7]/30 bg-white p-4 text-right transition-colors hover:border-[#1a35ca] dark:bg-[#171d34]">
                                <span class="font-mono text-[10px] uppercase tracking-wider text-[#757686]">Next article →</span>
                                <span class="mt-2 block font-display text-sm font-semibold">{{ $nextArticle->title }}</span>
                            </a>
                        @endif
                    </nav>
                @endif
            </article>

            <aside class="lg:col-span-3">
                <div class="sticky top-24 space-y-5">
                    <div class="rounded-lg border border-[#c5c5d7]/30 bg-white p-5 dark:bg-[#171d34]">
                        <div class="flex items-center gap-3">
                            @if ($siteAuthorAvatarUrl ?? false)<img src="{{ $siteAuthorAvatarUrl }}" alt="{{ $article->author?->name ?? $siteAuthorName }}" class="h-10 w-10 rounded-full object-cover">@else<span class="grid h-10 w-10 place-items-center rounded-full bg-[#dfe0ff] font-display font-semibold text-[#1a35ca]">{{ str($article->author?->name ?? ($siteAuthorName ?? 'Nas'))->substr(0, 1)->upper() }}</span>@endif
                            <div><p class="font-display font-semibold">{{ $article->author?->name ?? ($siteAuthorName ?? 'NasLabs') }}</p><p class="text-xs text-[#757686]">Public author</p></div>
                        </div>
                        <p class="mt-4 text-xs leading-5 text-[#757686]">Personal engineering notes on software craftsmanship, homelabs, data, and resilient systems.</p>
                    </div>

                    @if ($relatedArticles->isNotEmpty())
                        <div class="rounded-lg border border-[#c5c5d7]/30 bg-white p-5 dark:bg-[#171d34]">
                            <p class="font-mono text-[10px] uppercase tracking-widest text-[#757686]">Related articles</p>
                            <div class="mt-4 divide-y divide-[#c5c5d7]/30">
                                @foreach ($relatedArticles as $related)
                                    <a href="{{ route('articles.show', $related) }}" class="block py-3 first:pt-0 last:pb-0">
                                        <p class="font-display text-sm font-semibold transition-colors hover:text-[#1a35ca]">{{ $related->title }}</p>
                                        <p class="mt-1 text-[11px] text-[#757686]">{{ $related->category?->name ?? 'Research' }} · {{ $related->published_at->format($dateFormat ?? 'd M Y') }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <a href="{{ route('articles.index') }}" class="flex items-center justify-between rounded-lg border border-[#c5c5d7]/30 bg-white p-4 text-xs font-semibold text-[#1a35ca] dark:bg-[#171d34]">Browse archive <span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
                </div>
            </aside>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-reading-root]');
            const progressBar = document.querySelector('[data-progress-bar]');
            const progressNumber = document.querySelector('[data-progress-number]');
            const body = document.querySelector('#article-body');
            const updateProgress = () => {
                if (!root || !body) return;
                const start = body.offsetTop;
                const distance = Math.max(1, body.offsetHeight - window.innerHeight * 0.55);
                const progress = Math.min(100, Math.max(0, ((window.scrollY - start + window.innerHeight * 0.35) / distance) * 100));
                if (progressBar) progressBar.style.width = progress + '%';
                if (progressNumber) progressNumber.textContent = String(Math.round(progress)).padStart(2, '0');
            };
            window.addEventListener('scroll', updateProgress, { passive: true });
            updateProgress();

            const copyText = async (value) => {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(value);
                    return;
                }

                const textarea = document.createElement('textarea');
                textarea.value = value;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                const copied = document.execCommand('copy');
                textarea.remove();

                if (!copied) throw new Error('Clipboard is unavailable');
            };

            document.querySelector('[data-copy-link]')?.addEventListener('click', async (event) => {
                const status = document.querySelector('[data-copy-status]');
                try {
                    await copyText(window.location.href);
                    event.currentTarget.querySelector('[data-copy-label]').textContent = 'Copied!';
                    status.textContent = 'Article link copied';
                } catch {
                    status.textContent = 'Unable to copy link. Please copy it from the address bar.';
                    return;
                }

                window.setTimeout(() => {
                    event.currentTarget.querySelector('[data-copy-label]').textContent = 'Copy article link';
                    status.textContent = '';
                }, 2200);
            });
        })();
    </script>
@endsection
