<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard · NasLabs' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @fluxAppearance
</head>
<body class="min-h-screen bg-zinc-50 font-body text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50" x-data>
<div class="dashboard-shell min-h-screen p-3">
    <flux:sidebar sticky collapsible persist class="dashboard-sidebar border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 lg:sticky lg:top-3 lg:h-[calc(100dvh-1.5rem)] lg:rounded-xl lg:border lg:shadow-[0_1px_2px_rgb(15_23_42/0.04)]">
        <flux:sidebar.header class="dashboard-sidebar-header">
            <a href="{{ route('dashboard.index') }}" class="dashboard-sidebar-brand flex min-w-0 items-center gap-2.5" aria-label="NasLabs dashboard">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-zinc-950 text-sm font-bold text-white dark:bg-white dark:text-zinc-950">N</span>
                <span class="dashboard-sidebar-expanded min-w-0"><span class="block truncate font-display text-base font-bold">NasLabs</span><span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">Engineering journal</span></span>
            </a>
            <flux:tooltip content="Toggle sidebar" position="right">
                <button type="button" class="dashboard-sidebar-toggle" aria-label="Toggle sidebar" @click="$dispatch('flux-sidebar-toggle')">
                    <flux:icon.chevron-double-left class="dashboard-sidebar-toggle-collapse size-4" />
                    <flux:icon.chevron-double-right class="dashboard-sidebar-toggle-expand size-4" />
                </button>
            </flux:tooltip>
        </flux:sidebar.header>

        <flux:sidebar.nav class="dashboard-sidebar-primary-nav">
            <flux:sidebar.item icon="pencil-square" :href="route('dashboard.articles.create')" class="dashboard-sidebar-primary">Write article</flux:sidebar.item>
        </flux:sidebar.nav>

        <flux:sidebar.nav class="mt-2">
            <p class="dashboard-sidebar-section dashboard-sidebar-expanded">Workspace</p>
            <flux:sidebar.item icon="home" :href="route('dashboard.index')" :current="request()->routeIs('dashboard.index')">Overview</flux:sidebar.item>
            <flux:sidebar.item icon="document-text" :href="route('dashboard.articles.index')" :current="request()->routeIs('dashboard.articles.*')">Articles</flux:sidebar.item>
            <flux:sidebar.item icon="folder" :href="route('dashboard.categories.index')" :current="request()->routeIs('dashboard.categories.*')">Categories</flux:sidebar.item>
            <flux:sidebar.item icon="tag" :href="route('dashboard.tags.index')" :current="request()->routeIs('dashboard.tags.*')">Tags</flux:sidebar.item>
            <flux:sidebar.item icon="photo" :href="route('dashboard.media.index')" :current="request()->routeIs('dashboard.media.*')">Media</flux:sidebar.item>
            <flux:sidebar.item icon="chart-bar" :href="route('dashboard.analytics.index')" :current="request()->routeIs('dashboard.analytics.*')">Analytics</flux:sidebar.item>

            <p class="dashboard-sidebar-section dashboard-sidebar-expanded mt-5">Administration</p>
            @if (auth()->user()->isAdministrator())
                <flux:sidebar.item icon="clock" :href="route('dashboard.activity.index')" :current="request()->routeIs('dashboard.activity.*')">Activity Log</flux:sidebar.item>
            @endif
            @if (auth()->user()->isAdministrator())
                <flux:sidebar.item icon="users" :href="route('dashboard.users.index')" :current="request()->routeIs('dashboard.users.*')">Users &amp; Access</flux:sidebar.item>
                <flux:sidebar.item icon="bolt" :href="route('dashboard.automation.openclaw')" :current="request()->routeIs('dashboard.automation.*')">Automation</flux:sidebar.item>
            @endif
            <flux:sidebar.item icon="cog-6-tooth" :href="route('dashboard.settings.index')" :current="request()->routeIs('dashboard.settings.*')">Settings</flux:sidebar.item>
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <div class="dashboard-sidebar-footer border-t border-zinc-200 pt-3 dark:border-zinc-800">
            <flux:sidebar.item icon="arrow-top-right-on-square" :href="route('home')" target="_blank">View public blog</flux:sidebar.item>
        </div>
    </flux:sidebar>

    <flux:main class="dashboard-workspace min-w-0 px-1 pb-6 pt-2 sm:px-3 sm:pb-8 sm:pt-3 lg:pe-3 lg:ps-6 lg:pt-3 xl:pe-5 xl:ps-8 xl:pt-5">
        <div class="mx-auto w-full max-w-[1600px]">
            <div class="mb-5 flex items-center justify-between lg:hidden">
                <flux:sidebar.toggle icon="bars-3" aria-label="Open navigation" />
                <div class="flex items-center gap-1 rounded-xl border border-zinc-200 bg-white p-1 shadow-[0_1px_2px_rgb(15_23_42/0.06)] dark:border-zinc-800 dark:bg-zinc-900">
                    <flux:tooltip content="Switch theme" position="bottom"><flux:button variant="ghost" size="sm" square aria-label="Switch theme" @click="$flux.dark = !$flux.dark"><flux:icon.sun x-show="$flux.dark" class="size-4" /><flux:icon.moon x-show="!$flux.dark" class="size-4" /></flux:button></flux:tooltip>
                    <flux:dropdown position="bottom" align="end"><flux:button variant="ghost" size="sm" square aria-label="Open account menu"><flux:avatar :name="auth()->user()->name" :src="auth()->user()->displayAvatarUrl()" size="sm" circle /></flux:button><flux:menu class="w-60"><flux:menu.heading>{{ auth()->user()->name }}</flux:menu.heading><flux:menu.heading>{{ auth()->user()->email }}</flux:menu.heading><flux:menu.separator /><flux:menu.item :href="route('dashboard.settings.index', ['tab' => 'account'])" icon="user-circle">Account</flux:menu.item><flux:menu.item :href="route('dashboard.settings.index', ['tab' => 'security'])" icon="shield-check">Security</flux:menu.item><flux:menu.separator /><form method="POST" action="{{ route('logout') }}">@csrf<flux:menu.item type="submit" variant="danger" icon="arrow-left-start-on-rectangle">Logout</flux:menu.item></form></flux:menu></flux:dropdown>
                </div>
            </div>
            <div class="dashboard-utility-bar sticky top-4 z-20 mb-5 ml-auto hidden w-fit items-center gap-1 rounded-xl border border-zinc-200 bg-white p-1 shadow-[0_1px_2px_rgb(15_23_42/0.06)] dark:border-zinc-800 dark:bg-zinc-900 lg:flex">
                <flux:tooltip content="Switch theme" position="bottom"><flux:button variant="ghost" size="sm" square aria-label="Switch theme" @click="$flux.dark = !$flux.dark"><flux:icon.sun x-show="$flux.dark" class="size-4" /><flux:icon.moon x-show="!$flux.dark" class="size-4" /></flux:button></flux:tooltip>
                <flux:dropdown position="bottom" align="end"><flux:button variant="ghost" size="sm" square aria-label="Open account menu"><flux:avatar :name="auth()->user()->name" :src="auth()->user()->displayAvatarUrl()" size="sm" circle /></flux:button><flux:menu class="w-60"><flux:menu.heading>{{ auth()->user()->name }}</flux:menu.heading><flux:menu.heading>{{ auth()->user()->email }}</flux:menu.heading><flux:menu.separator /><flux:menu.item :href="route('dashboard.settings.index', ['tab' => 'account'])" icon="user-circle">Account</flux:menu.item><flux:menu.item :href="route('dashboard.settings.index', ['tab' => 'security'])" icon="shield-check">Security</flux:menu.item><flux:menu.separator /><form method="POST" action="{{ route('logout') }}">@csrf<flux:menu.item type="submit" variant="danger" icon="arrow-left-start-on-rectangle">Logout</flux:menu.item></form></flux:menu></flux:dropdown>
            </div>
            {{ $slot }}
        </div>
    </flux:main>
</div>
@livewireScripts
@fluxScripts
</body>
</html>
