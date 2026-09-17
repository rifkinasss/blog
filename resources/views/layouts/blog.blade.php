<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    @php
        $baseTitle = $pageTitle ?? $title ?? $siteMetaTitle;
        $documentTitle = $titleSuffix && ! str_contains($baseTitle, $titleSuffix) ? trim($baseTitle.' '.$titleSuffix) : $baseTitle;
        $canonicalUrl = $canonicalUrl ?? rtrim($canonicalBaseUrl, '/').'/'.ltrim(request()->path(), '/');
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documentTitle }}</title>
    <meta name="description" content="{{ $pageDescription ?? $siteMetaDescription }}">
    <meta name="robots" content="{{ $robotsIndexing ? 'index,follow' : 'noindex,nofollow' }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if ($faviconUrl)<link rel="icon" href="{{ $faviconUrl }}">@endif
    @if ($appleTouchIconUrl)<link rel="apple-touch-icon" href="{{ $appleTouchIconUrl }}">@endif
    @if ($defaultOgImageUrl && ! isset($pageOpenGraphImage))<meta property="og:image" content="{{ $defaultOgImageUrl }}"><meta name="twitter:card" content="summary_large_image">@endif
    @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
        <link rel="alternate" hreflang="{{ $localeCode }}" href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
    @endforeach
    <link rel="alternate" type="application/rss+xml" href="{{ route('feed') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <script>if (localStorage.theme === 'dark' || (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark')</script>
    @vite(['resources/css/app.css', 'resources/js/public.js'])
    @stack('head')
</head>
<body class="min-h-screen overflow-x-hidden bg-[#faf8ff] font-body text-[#131b2e] antialiased dark:bg-[#101426] dark:text-[#eef0ff]">
    <header class="public-navbar fixed left-0 top-0 z-50 w-full">
        <div class="mx-auto flex h-16 max-w-7xl min-w-0 items-center justify-between gap-3 px-3 sm:gap-5 md:px-12">
            <a href="{{ route('home') }}" class="flex min-w-0 shrink items-center gap-2.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-[#101426]">
                @if ($brandLogoUrl)
                    <img src="{{ $brandLogoUrl }}" alt="{{ $siteName }}" class="h-8 w-8 shrink-0 object-contain dark:hidden">
                    @if ($brandDarkLogoUrl)<img src="{{ $brandDarkLogoUrl }}" alt="{{ $siteName }}" class="hidden h-8 w-8 shrink-0 object-contain dark:block">@else<img src="{{ $brandLogoUrl }}" alt="{{ $siteName }}" class="hidden h-8 w-8 shrink-0 object-contain dark:block">@endif
                @else
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-[#131b2e] text-sm font-bold text-white">N<span class="text-[#bbc3ff]">+</span></span>
                @endif
                <span class="truncate font-display text-base tracking-tight sm:text-lg"><span class="font-medium text-zinc-500 dark:text-zinc-400">Blog by</span> <span class="font-semibold text-zinc-950 dark:text-zinc-50">NasLabs</span></span>
            </a>

            <nav class="hidden items-center gap-1 text-sm md:flex" aria-label="Primary navigation">
                <a href="{{ route('articles.index') }}" @class(['public-navbar-link', 'public-navbar-link-active' => request()->routeIs('articles.*')])>{{ __('blog.articles') }}</a>
                <a href="https://naslabs.my.id" target="_blank" rel="noopener noreferrer" class="public-navbar-link">{{ __('blog.projects') }}</a>
                <a href="{{ route('about') }}" @class(['public-navbar-link', 'public-navbar-link-active' => request()->routeIs('about')])>{{ __('blog.about') }}</a>
            </nav>

            <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                <a href="{{ route('articles.index') }}" aria-label="{{ __('blog.search') }}" class="public-navbar-control public-navbar-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5" aria-hidden="true"><circle cx="11" cy="11" r="6"/><path d="m16 16 4 4"/></svg>
                    <span class="hidden lg:inline">{{ __('blog.search') }}</span>
                    <span class="sr-only">{{ __('blog.search') }}</span>
                </a>
                <div class="public-navbar-locale hidden md:flex" aria-label="Language">
                    @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <a href="{{ LaravelLocalization::getLocalizedURL($localeCode) }}" hreflang="{{ $localeCode }}" @class(['public-navbar-locale-link', 'public-navbar-locale-link-active' => app()->getLocale() === $localeCode])>{{ strtoupper($localeCode) }}</a>
                    @endforeach
                </div>
                <button type="button" data-theme-toggle aria-label="Toggle dark mode" aria-pressed="false" class="public-navbar-control public-navbar-icon-control">
                    <span data-theme-icon class="relative block size-5 transition-transform duration-300 ease-out">
                        <svg data-theme-moon viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute inset-0 size-5" aria-hidden="true"><path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.7 6.7 0 0 0 21 12.8Z"/></svg>
                        <svg data-theme-sun viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute inset-0 hidden size-5" aria-hidden="true"><circle cx="12" cy="12" r="3.5"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    </span>
                    <span class="sr-only">Toggle dark mode</span>
                </button>
                <details class="public-navbar-mobile-menu relative md:hidden">
                    <summary class="public-navbar-control public-navbar-icon-control list-none" aria-label="Open navigation">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </summary>
                    <div class="public-navbar-mobile-panel">
                        <nav class="grid gap-1" aria-label="Mobile navigation">
                            <a href="{{ route('articles.index') }}" class="public-navbar-mobile-link">{{ __('blog.articles') }}</a>
                            <a href="https://naslabs.my.id" target="_blank" rel="noopener noreferrer" class="public-navbar-mobile-link">{{ __('blog.projects') }}</a>
                            <a href="{{ route('about') }}" class="public-navbar-mobile-link">{{ __('blog.about') }}</a>
                        </nav>
                        <div class="mt-3 flex border-t border-zinc-200 pt-3 dark:border-zinc-800">
                            @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                <a href="{{ LaravelLocalization::getLocalizedURL($localeCode) }}" hreflang="{{ $localeCode }}" @class(['public-navbar-locale-link', 'public-navbar-locale-link-active' => app()->getLocale() === $localeCode])>{{ strtoupper($localeCode) }}</a>
                            @endforeach
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <main class="w-full pt-16">@yield('content')</main>

    <footer class="public-footer mt-24">
        <div class="mx-auto max-w-7xl px-4 py-10 md:px-12 md:py-12">
            <div class="grid gap-10 border-b pb-10 md:grid-cols-2 md:gap-16">
                <div>
                    <h2 class="public-footer-brand"><span>Blog by</span> <strong>NasLabs</strong></h2>
                    <p class="public-footer-copy mt-4 max-w-md">{{ __('blog.footer_description') }}</p>
                    <nav class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm" aria-label="Footer navigation">
                        <a href="{{ route('articles.index') }}" class="public-footer-link">{{ __('blog.articles') }}</a>
                        <a href="{{ route('projects') }}" class="public-footer-link">{{ __('blog.projects') }}</a>
                        <a href="{{ route('about') }}" class="public-footer-link">{{ __('blog.about') }}</a>
                    </nav>
                </div>
                <div class="md:justify-self-end md:pl-8">
                    <h2 class="font-display text-xl font-semibold tracking-tight">{{ __('blog.footer_archive_title') }}</h2>
                    <p class="public-footer-copy mt-3 max-w-md">{{ __('blog.footer_archive_description') }}</p>
                    <a href="{{ route('articles.index') }}" class="public-footer-cta">{{ __('blog.browse_articles') }}</a>
                </div>
            </div>
            <div class="public-footer-meta flex flex-col justify-between gap-3 pt-6 sm:flex-row">
                <p>© {{ date('Y') }} NasLabs. Released under MIT / CC-BY-4.0.</p>
                <p>rev 1.0.0 · public field node</p>
            </div>
        </div>
    </footer>
</body>
</html>
