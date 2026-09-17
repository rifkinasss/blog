<!doctype html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <?php
        $baseTitle = $pageTitle ?? $title ?? $siteMetaTitle;
        $documentTitle = $titleSuffix && ! str_contains($baseTitle, $titleSuffix) ? trim($baseTitle.' '.$titleSuffix) : $baseTitle;
        $canonicalUrl = $canonicalUrl ?? rtrim($canonicalBaseUrl, '/').'/'.ltrim(request()->path(), '/');
    ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($documentTitle); ?></title>
    <meta name="description" content="<?php echo e($pageDescription ?? $siteMetaDescription); ?>">
    <meta name="robots" content="<?php echo e($robotsIndexing ? 'index,follow' : 'noindex,nofollow'); ?>">
    <link rel="canonical" href="<?php echo e($canonicalUrl); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($faviconUrl): ?><link rel="icon" href="<?php echo e($faviconUrl); ?>"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($appleTouchIconUrl): ?><link rel="apple-touch-icon" href="<?php echo e($appleTouchIconUrl); ?>"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($defaultOgImageUrl && ! isset($pageOpenGraphImage)): ?><meta property="og:image" content="<?php echo e($defaultOgImageUrl); ?>"><meta name="twitter:card" content="summary_large_image"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = LaravelLocalization::getSupportedLocales(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $localeCode => $properties): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <link rel="alternate" hreflang="<?php echo e($localeCode); ?>" href="<?php echo e(LaravelLocalization::getLocalizedURL($localeCode, null, [], true)); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    <link rel="alternate" type="application/rss+xml" href="<?php echo e(route('feed')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <script>if (localStorage.theme === 'dark' || (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark')</script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/public.js']); ?>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body class="min-h-screen overflow-x-hidden bg-[#faf8ff] font-body text-[#131b2e] antialiased dark:bg-[#101426] dark:text-[#eef0ff]">
    <header class="public-navbar fixed left-0 top-0 z-50 w-full">
        <div class="mx-auto flex h-16 max-w-7xl min-w-0 items-center justify-between gap-3 px-3 sm:gap-5 md:px-12">
            <a href="<?php echo e(route('home')); ?>" class="flex min-w-0 shrink items-center gap-2.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-[#101426]">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($brandLogoUrl): ?>
                    <img src="<?php echo e($brandLogoUrl); ?>" alt="<?php echo e($siteName); ?>" class="h-8 w-8 shrink-0 object-contain dark:hidden">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($brandDarkLogoUrl): ?><img src="<?php echo e($brandDarkLogoUrl); ?>" alt="<?php echo e($siteName); ?>" class="hidden h-8 w-8 shrink-0 object-contain dark:block"><?php else: ?><img src="<?php echo e($brandLogoUrl); ?>" alt="<?php echo e($siteName); ?>" class="hidden h-8 w-8 shrink-0 object-contain dark:block"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-[#131b2e] text-sm font-bold text-white">N<span class="text-[#bbc3ff]">+</span></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="truncate font-display text-base tracking-tight sm:text-lg"><span class="font-medium text-zinc-500 dark:text-zinc-400">Blog by</span> <span class="font-semibold text-zinc-950 dark:text-zinc-50">NasLabs</span></span>
            </a>

            <nav class="hidden items-center gap-1 text-sm md:flex" aria-label="Primary navigation">
                <a href="<?php echo e(route('articles.index')); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['public-navbar-link', 'public-navbar-link-active' => request()->routeIs('articles.*')]); ?>"><?php echo e(__('blog.articles')); ?></a>
                <a href="https://naslabs.my.id" target="_blank" rel="noopener noreferrer" class="public-navbar-link"><?php echo e(__('blog.projects')); ?></a>
                <a href="<?php echo e(route('about')); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['public-navbar-link', 'public-navbar-link-active' => request()->routeIs('about')]); ?>"><?php echo e(__('blog.about')); ?></a>
            </nav>

            <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                <a href="<?php echo e(route('articles.index')); ?>" aria-label="<?php echo e(__('blog.search')); ?>" class="public-navbar-control public-navbar-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5" aria-hidden="true"><circle cx="11" cy="11" r="6"/><path d="m16 16 4 4"/></svg>
                    <span class="hidden lg:inline"><?php echo e(__('blog.search')); ?></span>
                    <span class="sr-only"><?php echo e(__('blog.search')); ?></span>
                </a>
                <div class="public-navbar-locale hidden md:flex" aria-label="Language">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = LaravelLocalization::getSupportedLocales(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $localeCode => $properties): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="<?php echo e(LaravelLocalization::getLocalizedURL($localeCode)); ?>" hreflang="<?php echo e($localeCode); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['public-navbar-locale-link', 'public-navbar-locale-link-active' => app()->getLocale() === $localeCode]); ?>"><?php echo e(strtoupper($localeCode)); ?></a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
                            <a href="<?php echo e(route('articles.index')); ?>" class="public-navbar-mobile-link"><?php echo e(__('blog.articles')); ?></a>
                            <a href="https://naslabs.my.id" target="_blank" rel="noopener noreferrer" class="public-navbar-mobile-link"><?php echo e(__('blog.projects')); ?></a>
                            <a href="<?php echo e(route('about')); ?>" class="public-navbar-mobile-link"><?php echo e(__('blog.about')); ?></a>
                        </nav>
                        <div class="mt-3 flex border-t border-zinc-200 pt-3 dark:border-zinc-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = LaravelLocalization::getSupportedLocales(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $localeCode => $properties): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <a href="<?php echo e(LaravelLocalization::getLocalizedURL($localeCode)); ?>" hreflang="<?php echo e($localeCode); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['public-navbar-locale-link', 'public-navbar-locale-link-active' => app()->getLocale() === $localeCode]); ?>"><?php echo e(strtoupper($localeCode)); ?></a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <main class="w-full pt-16"><?php echo $__env->yieldContent('content'); ?></main>

    <footer class="public-footer mt-24">
        <div class="mx-auto max-w-7xl px-4 py-10 md:px-12 md:py-12">
            <div class="grid gap-10 border-b pb-10 md:grid-cols-2 md:gap-16">
                <div>
                    <h2 class="public-footer-brand"><span>Blog by</span> <strong>NasLabs</strong></h2>
                    <p class="public-footer-copy mt-4 max-w-md"><?php echo e(__('blog.footer_description')); ?></p>
                    <nav class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm" aria-label="Footer navigation">
                        <a href="<?php echo e(route('articles.index')); ?>" class="public-footer-link"><?php echo e(__('blog.articles')); ?></a>
                        <a href="<?php echo e(route('projects')); ?>" class="public-footer-link"><?php echo e(__('blog.projects')); ?></a>
                        <a href="<?php echo e(route('about')); ?>" class="public-footer-link"><?php echo e(__('blog.about')); ?></a>
                    </nav>
                </div>
                <div class="md:justify-self-end md:pl-8">
                    <h2 class="font-display text-xl font-semibold tracking-tight"><?php echo e(__('blog.footer_archive_title')); ?></h2>
                    <p class="public-footer-copy mt-3 max-w-md"><?php echo e(__('blog.footer_archive_description')); ?></p>
                    <a href="<?php echo e(route('articles.index')); ?>" class="public-footer-cta"><?php echo e(__('blog.browse_articles')); ?></a>
                </div>
            </div>
            <div class="public-footer-meta flex flex-col justify-between gap-3 pt-6 sm:flex-row">
                <p>© <?php echo e(date('Y')); ?> NasLabs. Released under MIT / CC-BY-4.0.</p>
                <p>rev 1.0.0 · public field node</p>
            </div>
        </div>
    </footer>
</body>
</html>
<?php /**PATH /Users/rifkinasss/Documents/workspace/Startup/NasLabs/internal-projects/Blog/resources/views/layouts/blog.blade.php ENDPATH**/ ?>