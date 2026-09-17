@extends('layouts.blog')

@section('content')
    <main class="mx-auto w-full max-w-7xl px-4 pb-16 pt-12 md:px-12 md:pt-16">
        <section class="max-w-3xl">
            <h1 class="font-display text-4xl font-bold tracking-tight md:text-5xl">{{ __('blog.about_heading') }}</h1>
            <p class="mt-3 text-sm text-[#757686] md:text-base">{{ __('blog.about_subtitle') }}</p>
            <p class="mt-6 text-base leading-8 text-[#444655] dark:text-[#c5c5d7] md:text-lg">{{ __('blog.about_intro') }}</p>
            <p class="mt-4 text-base leading-8 text-[#757686]">{{ __('blog.about_intro_supporting') }}</p>
        </section>

        <section class="mt-14 border-t border-[#c5c5d7]/30 pt-10 md:mt-16 md:pt-12">
            <h2 class="font-display text-2xl font-semibold tracking-tight">{{ __('blog.about_focus_heading') }}</h2>
            <div class="mt-6 grid gap-x-10 md:grid-cols-2">
                @foreach ([
                    ['terminal', 'about_focus_software_title', 'about_focus_software_description'],
                    ['dns', 'about_focus_homelab_title', 'about_focus_homelab_description'],
                    ['database', 'about_focus_data_title', 'about_focus_data_description'],
                    ['menu_book', 'about_focus_notes_title', 'about_focus_notes_description'],
                ] as [$icon, $title, $description])
                    <div class="flex gap-3 border-t border-[#c5c5d7]/30 py-5 first:border-t-0 md:first:border-t md:[&:nth-child(2)]:border-t">
                        <span class="material-symbols-outlined mt-0.5 text-[19px] text-[#1a35ca]" aria-hidden="true">{{ $icon }}</span>
                        <div>
                            <h3 class="font-display text-base font-semibold">{{ __('blog.'.$title) }}</h3>
                            <p class="mt-1 text-sm leading-6 text-[#757686]">{{ __('blog.'.$description) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mt-14 border-t border-[#c5c5d7]/30 pt-10 md:mt-16 md:pt-12">
            <h2 class="font-display text-2xl font-semibold tracking-tight">{{ __('blog.about_journey_heading') }}</h2>
            <div class="mt-7 max-w-3xl border-s border-[#c5c5d7]/50 ps-6">
                @foreach ([
                    ['2024', 'about_journey_2024_title', 'about_journey_2024_description'],
                    ['2025', 'about_journey_2025_title', 'about_journey_2025_description'],
                    ['2026', 'about_journey_2026_title', 'about_journey_2026_description'],
                ] as [$year, $title, $description])
                    <article class="relative pb-9 last:pb-0">
                        <span class="absolute -start-[1.92rem] top-1.5 size-2.5 rounded-full border-2 border-[#faf8ff] bg-[#1a35ca] dark:border-[#101426]"></span>
                        <p class="font-mono text-xs font-semibold text-[#1a35ca]">{{ $year }}</p>
                        <h3 class="mt-2 font-display text-lg font-semibold">{{ __('blog.'.$title) }}</h3>
                        <p class="mt-2 text-sm leading-6 text-[#757686]">{{ __('blog.'.$description) }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-14 flex flex-col gap-6 border-t border-[#c5c5d7]/30 pt-10 md:mt-16 md:flex-row md:items-end md:justify-between md:pt-12">
            <div class="max-w-2xl">
                <h2 class="font-display text-2xl font-semibold tracking-tight">{{ __('blog.about_contact_heading') }}</h2>
                <p class="mt-3 text-sm leading-6 text-[#757686]">{{ __('blog.about_contact_description') }}</p>
            </div>
            <a href="mailto:hello@naslabs.my.id" class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded bg-[#1a35ca] px-4 text-sm font-semibold text-white transition hover:bg-[#3b52e2] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1a35ca] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-[#101426]">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">mail</span>
                {{ __('blog.about_contact_action') }}
            </a>
        </section>
    </main>
@endsection
