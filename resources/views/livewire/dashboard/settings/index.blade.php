<div class="w-full space-y-6">
    <header class="flex flex-col gap-3 border-b border-zinc-200 pb-5 sm:flex-row sm:items-end sm:justify-between dark:border-zinc-800">
        <div>
            <flux:heading size="xl">Settings</flux:heading>
            <flux:subheading>Configuration for the journal, public identity, and workspace access.</flux:subheading>
        </div>
        <div class="flex items-center gap-3">
            <span wire:dirty class="text-sm text-amber-700 dark:text-amber-400">Unsaved changes</span>
            @if (session('success'))<flux:badge color="green" icon="check">{{ session('success') }}</flux:badge>@endif
        </div>
    </header>

    <div class="flex w-full flex-col gap-6 lg:flex-row lg:gap-8">
        <aside class="w-full shrink-0 lg:sticky lg:top-5 lg:w-[16.25rem] lg:self-start" aria-label="Settings navigation">
            <nav class="grid grid-cols-1 gap-1 border-b border-zinc-200 pb-4 sm:grid-cols-2 lg:block lg:border-b-0 lg:border-e lg:pe-5 lg:pb-0 dark:border-zinc-800">
            @php($navigation = ['general' => ['General', 'Workspace language and locale', 'globe-alt'], 'branding' => ['Branding', 'Public identity and assets', 'swatch'], 'publishing' => ['Publishing', 'Article defaults and automation', 'document-text'], 'seo' => ['SEO', 'Search and sharing defaults', 'magnifying-glass'], 'account' => ['Account', 'Your profile and access level', 'user-circle'], 'security' => ['Security', 'Password and signed-in sessions', 'shield-check'], 'system' => ['System', 'Runtime information', 'cpu-chip'], 'storage' => ['Storage', 'Public media disk', 'circle-stack']])
            @foreach ($navigation as $tab => [$label, $description, $icon])
                @if (in_array($tab, ['account', 'security'], true) || auth()->user()->isAdministrator())
                    <flux:button type="button" wire:click="selectTab('{{ $tab }}')" variant="ghost" :icon="$icon" class="settings-nav-item {{ $activeTab === $tab ? 'settings-nav-item-active' : '' }} !mb-0 !h-auto !w-full !min-w-0 !justify-start !whitespace-normal px-3 py-3 text-left lg:mb-1">
                        <span class="min-w-0 text-left">
                            <span class="block text-sm font-medium">{{ $label }}</span>
                            <span class="mt-0.5 block whitespace-normal text-xs font-normal leading-5 text-zinc-500 dark:text-zinc-400">{{ $description }}</span>
                        </span>
                    </flux:button>
                @endif
            @endforeach
            </nav>
        </aside>

        <section class="min-w-0 flex-1" aria-live="polite">
            @if ($activeTab === 'general')
                <section class="w-full">
                    <flux:heading size="lg">General</flux:heading>
                    <flux:text class="mt-1">Preferences used when the application renders dates and public language routes.</flux:text>
                    <form wire:submit="saveGeneral" class="mt-6 grid gap-5 md:grid-cols-2">
                        <flux:select wire:model="timezone" label="Timezone"><option value="Asia/Makassar">Asia/Makassar</option><option value="Asia/Jakarta">Asia/Jakarta</option><option value="Asia/Jayapura">Asia/Jayapura</option><option value="UTC">UTC</option></flux:select>
                        <flux:select wire:model="locale" label="Default locale"><option value="id">Indonesian</option><option value="en">English</option></flux:select>
                        <div class="flex flex-col gap-3 border-t border-zinc-200 pt-5 md:col-span-2 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800"><flux:text class="text-sm">Visitors can still choose a supported public language from the blog navigation.</flux:text><flux:button type="submit" variant="primary" icon="check" class="shrink-0">Save general</flux:button></div>
                    </form>
                </section>
            @elseif ($activeTab === 'branding')
                <div class="space-y-9">
                    <section class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_18rem]">
                        <div>
                            <flux:heading size="lg">Brand identity</flux:heading>
                            <flux:text class="mt-1">These values appear in public page metadata, navigation, and article attribution.</flux:text>
                            <form wire:submit="saveBranding" class="mt-6 space-y-5">
                                <flux:input wire:model="site_name" label="Site name" />@error('site_name')<flux:error>{{ $message }}</flux:error>@enderror
                                <flux:textarea wire:model="site_description" label="Site description" rows="3" />@error('site_description')<flux:error>{{ $message }}</flux:error>@enderror
                                <flux:input wire:model="public_url" type="url" label="Public URL" />@error('public_url')<flux:error>{{ $message }}</flux:error>@enderror
                                <flux:input wire:model="author_name" label="Author name" />@error('author_name')<flux:error>{{ $message }}</flux:error>@enderror

                                <div class="grid gap-5 border-t border-zinc-200 pt-6 sm:grid-cols-2 dark:border-zinc-800">
                                    @foreach (['brand_logo' => ['Primary logo', 'PNG, JPG, or WebP · max 2 MB'], 'brand_dark_logo' => ['Dark-mode logo', 'Optional counterpart for dark public pages'], 'favicon' => ['Favicon', 'ICO or PNG · max 1 MB'], 'apple_touch_icon' => ['Apple touch icon', 'PNG · max 1 MB'], 'default_og_image' => ['Default Open Graph image', 'Used when an article has no cover · max 4 MB'], 'author_avatar' => ['Author avatar', 'Optional public author image · max 2 MB']] as $key => [$label, $help])
                                        @php($asset = $brandAssets[$key])
                                        <div class="border-t border-zinc-200 pt-4 first:border-t-0 first:pt-0 sm:[&:nth-child(2)]:border-t-0 sm:[&:nth-child(2)]:pt-0 dark:border-zinc-800">
                                            <div class="flex min-h-16 items-start gap-3">
                                                <div class="grid size-14 shrink-0 place-items-center overflow-hidden border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900">
                                                    @if ($asset)<img src="{{ $asset['url'] }}" alt="{{ $label }} preview" class="size-full object-contain p-1">@else<flux:icon.photo class="size-5 text-zinc-400" />@endif
                                                </div>
                                                <div class="min-w-0"><p class="text-sm font-medium">{{ $label }}</p><flux:text class="mt-1 text-xs">{{ $asset ? $asset['type'].($asset['dimensions'] ? ' · '.$asset['dimensions'] : '') : $help }}</flux:text>@if ($asset)<flux:button type="button" wire:click="removeBrandAsset('{{ $key }}')" wire:confirm="Remove this {{ strtolower($label) }}?" variant="ghost" size="sm" class="mt-1 px-0 text-red-700 hover:text-red-800 dark:text-red-400">Remove</flux:button>@endif</div>
                                            </div>
                                            <input wire:model="{{ $key }}" type="file" class="mt-3 block w-full text-xs file:mr-3 file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:font-medium file:text-zinc-800 dark:file:bg-zinc-800 dark:file:text-zinc-100" accept="{{ $key === 'favicon' ? '.ico,.png,.svg' : ($key === 'apple_touch_icon' ? 'image/png' : 'image/*,.svg') }}">
                                            @error($key)<flux:error class="mt-2">{{ $message }}</flux:error>@enderror
                                        </div>
                                    @endforeach
                                </div>
                                <div class="sticky bottom-3 z-10 flex justify-end border-t border-zinc-200 bg-zinc-50/95 pt-4 dark:border-zinc-800 dark:bg-zinc-950/95"><flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">Save branding</flux:button></div>
                            </form>
                        </div>
                        <aside class="h-fit space-y-6 border-t border-zinc-200 pt-5 xl:border-s xl:border-t-0 xl:pl-6 xl:pt-0 dark:border-zinc-800">
                            <div><p class="text-sm font-medium">Browser preview</p><div class="mt-3 border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900"><div class="flex items-center gap-2 text-sm"><span class="grid size-5 place-items-center overflow-hidden border border-zinc-200 dark:border-zinc-700">@if($brandAssets['favicon'])<img src="{{ $brandAssets['favicon']['url'] }}" alt="" class="size-full object-contain">@else<flux:icon.globe-alt class="size-3 text-zinc-500" />@endif</span><span class="truncate font-medium">{{ $site_name }}</span></div><p class="mt-3 truncate text-xs text-zinc-500">{{ parse_url($public_url, PHP_URL_HOST) ?: $public_url }}</p></div></div>
                            <div><p class="text-sm font-medium">Social preview</p><div class="mt-3 overflow-hidden border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">@if($brandAssets['default_og_image'])<img src="{{ $brandAssets['default_og_image']['url'] }}" alt="" class="aspect-[1.91/1] w-full object-cover">@else<div class="aspect-[1.91/1] bg-zinc-100 dark:bg-zinc-800"></div>@endif<div class="p-3"><p class="line-clamp-1 text-xs text-zinc-500">{{ parse_url($public_url, PHP_URL_HOST) ?: $public_url }}</p><p class="mt-1 line-clamp-2 text-sm font-medium">{{ $site_name }}</p><p class="mt-1 line-clamp-2 text-xs text-zinc-500">{{ $site_description }}</p></div></div></div>
                        </aside>
                    </section>
                </div>
            @elseif ($activeTab === 'publishing')
                <div class="w-full space-y-8">
                    <section><flux:heading size="lg">Article defaults</flux:heading><flux:text class="mt-1">Applied when a dashboard user starts a new article.</flux:text><form wire:submit="savePublishing" class="mt-6 space-y-7"><div class="grid gap-5 sm:grid-cols-2"><flux:select wire:model="default_article_status" label="Default article status"><option value="draft">Draft</option><option value="published">Published</option><option value="archived">Archived</option></flux:select><flux:select wire:model="default_category_id" label="Default category"><option value="">No default category</option>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</flux:select><flux:select wire:model="default_author_id" label="Default author"><option value="">Current signed-in user</option>@foreach ($authors as $author)<option value="{{ $author->id }}">{{ $author->name }} · {{ $author->email }}</option>@endforeach</flux:select><flux:select wire:model="articles_per_page" label="Posts per page"><option value="6">6 articles</option><option value="9">9 articles</option><option value="12">12 articles</option><option value="18">18 articles</option></flux:select><flux:select wire:model="date_format" label="Public date format"><option value="d M Y">16 Sep 2026</option><option value="d/m/Y">16/09/2026</option><option value="M j, Y">Sep 16, 2026</option></flux:select></div>
                        <div class="grid gap-4 border-y border-zinc-200 py-5 dark:border-zinc-800"><flux:checkbox wire:model="auto_generate_slug" label="Generate a slug from the title" /><flux:checkbox wire:model="require_category_before_publish" label="Require a category before publishing" /><flux:checkbox wire:model="require_excerpt_before_publish" label="Require an excerpt before publishing" /></div>
                        <div><p class="text-sm font-medium">Service account automation</p><flux:text class="mt-1 text-sm">These controls constrain OpenClaw requests in addition to its token scopes.</flux:text><div class="mt-4 grid gap-4"><flux:checkbox wire:model="allow_service_drafts" label="Allow service accounts to create drafts" /><flux:checkbox wire:model="allow_service_publish" label="Allow service accounts to publish" /><flux:checkbox wire:model="service_publish_requires_review" label="Require human review before a service account can publish" /></div></div>
                        <div class="flex justify-end border-t border-zinc-200 pt-5 dark:border-zinc-800"><flux:button type="submit" variant="primary" icon="check">Save publishing</flux:button></div></form></section>
                </div>
            @elseif ($activeTab === 'seo')
                <section class="grid w-full gap-8 xl:grid-cols-[minmax(0,1fr)_18rem]"><div><flux:heading size="lg">Search and sharing</flux:heading><flux:text class="mt-1">Fallback metadata for public pages. Article metadata and cover images take precedence.</flux:text><form wire:submit="saveSeo" class="mt-6 space-y-5"><flux:input wire:model="default_meta_title" label="Default meta title" />@error('default_meta_title')<flux:error>{{ $message }}</flux:error>@enderror<flux:input wire:model="title_suffix" label="Title suffix" placeholder="· NasLabs Journal" /><flux:textarea wire:model="default_meta_description" label="Default meta description" rows="4" />@error('default_meta_description')<flux:error>{{ $message }}</flux:error>@enderror<flux:input wire:model="canonical_base_url" type="url" label="Canonical base URL" />@error('canonical_base_url')<flux:error>{{ $message }}</flux:error>@enderror<div class="grid gap-4 border-y border-zinc-200 py-5 dark:border-zinc-800"><flux:checkbox wire:model="sitemap_enabled" label="Enable sitemap.xml" /><flux:checkbox wire:model="robots_indexing" label="Allow search engine indexing" /></div><div class="flex justify-end"><flux:button type="submit" variant="primary" icon="check">Save SEO</flux:button></div></form></div><aside class="h-fit border-t border-zinc-200 pt-5 xl:border-s xl:border-t-0 xl:pl-6 xl:pt-0 dark:border-zinc-800"><p class="text-sm font-medium">Open Graph image</p><flux:text class="mt-1 text-sm">The default image is managed with other public assets in Branding.</flux:text>@if($brandAssets['default_og_image'])<img src="{{ $brandAssets['default_og_image']['url'] }}" alt="Default Open Graph preview" class="mt-4 aspect-[1.91/1] w-full border border-zinc-200 object-cover dark:border-zinc-800">@endif<flux:button type="button" wire:click="selectTab('branding')" variant="ghost" size="sm" class="mt-4 px-0">Manage branding assets</flux:button></aside></section>
            @elseif ($activeTab === 'account')
                <div class="grid w-full gap-8 xl:grid-cols-[minmax(0,1fr)_18rem]">
                    <section>
                        <flux:heading size="lg">Account</flux:heading>
                        <flux:text class="mt-1">Manage your personal profile information.</flux:text>
                        <section class="mt-6 flex flex-col gap-4 border-y border-zinc-200 py-5 sm:flex-row sm:items-center dark:border-zinc-800">
                            @php($previewAvatar = $profile_avatar && in_array(strtolower($profile_avatar->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'], true) ? $profile_avatar->temporaryUrl() : auth()->user()->displayAvatarUrl())
                            <flux:avatar :name="auth()->user()->name" :src="$previewAvatar" size="xl" circle />
                            <div class="min-w-0 flex-1"><p class="text-sm font-medium">Profile photo</p><flux:text class="mt-1 text-sm">JPG, PNG, or WebP up to 2 MB. It is used only for your dashboard account.</flux:text><form wire:submit="uploadAvatar" class="mt-3 flex flex-wrap items-center gap-3"><input wire:model="profile_avatar" type="file" accept="image/jpeg,image/png,image/webp" class="block max-w-full text-xs file:mr-3 file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:font-medium file:text-zinc-800 dark:file:bg-zinc-800 dark:file:text-zinc-100"><flux:button type="submit" variant="outline" size="sm" icon="arrow-up-tray" wire:loading.attr="disabled" wire:target="profile_avatar,uploadAvatar">Upload photo</flux:button>@if(auth()->user()->avatar_path)<flux:button type="button" wire:click="removeAvatar" wire:confirm="Remove your profile photo?" variant="ghost" size="sm" class="text-red-700 hover:text-red-800 dark:text-red-400">Remove photo</flux:button>@endif</form>@error('profile_avatar')<flux:error class="mt-2">{{ $message }}</flux:error>@enderror<flux:text wire:loading wire:target="profile_avatar,uploadAvatar" class="mt-2 text-xs">Uploading photo…</flux:text></div>
                        </section>
                        <form wire:submit="saveProfile" class="mt-6 grid gap-5 sm:grid-cols-2">
                            <div><flux:input wire:model="name" label="Name" autocomplete="name" />@error('name')<flux:error class="mt-2">{{ $message }}</flux:error>@enderror</div>
                            <div><flux:input wire:model="email" type="email" label="Email" autocomplete="email" />@error('email')<flux:error class="mt-2">{{ $message }}</flux:error>@enderror</div>
                            <div class="flex flex-col gap-3 border-t border-zinc-200 pt-5 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800"><flux:text class="text-sm">Email verification is not enabled for this application.</flux:text><flux:button type="submit" variant="primary" icon="check">Save account</flux:button></div>
                        </form>
                    </section>
                    <aside class="h-fit border-t border-zinc-200 pt-5 xl:border-s xl:border-t-0 xl:pl-6 xl:pt-0 dark:border-zinc-800">
                        <p class="text-sm font-medium">Access level</p>
                        <flux:text class="mt-1 text-sm">{{ auth()->user()->role->label() }}. {{ auth()->user()->isAdministrator() ? 'Administrators manage journal configuration and all content.' : 'Editors create content and update articles they own.' }}</flux:text>
                        <dl class="mt-5 divide-y divide-zinc-200 border-y border-zinc-200 text-sm dark:divide-zinc-800 dark:border-zinc-800"><div class="flex justify-between gap-4 py-3"><dt class="text-zinc-500">Account created</dt><dd>{{ auth()->user()->created_at->format('d M Y') }}</dd></div><div class="flex justify-between gap-4 py-3"><dt class="text-zinc-500">Workspace locale</dt><dd>{{ strtoupper($locale) }}</dd></div><div class="flex justify-between gap-4 py-3"><dt class="text-zinc-500">Workspace timezone</dt><dd>{{ $timezone }}</dd></div></dl>
                        @if(auth()->user()->isAdministrator())<flux:button :href="route('dashboard.users.index')" class="mt-4 px-0" variant="ghost" size="sm" icon="users">Manage users & access</flux:button>@endif
                    </aside>
                </div>
            @elseif ($activeTab === 'security')
                <div class="grid w-full gap-8 xl:grid-cols-[minmax(0,1fr)_18rem]">
                    <div class="space-y-8">
                        <section>
                            <flux:heading size="lg">Security</flux:heading>
                            <flux:text class="mt-1">Update your password and protect your account.</flux:text>
                            <form wire:submit="updatePassword" class="mt-6 grid gap-5 sm:grid-cols-2"><div class="sm:col-span-2"><flux:input wire:model="current_password" type="password" label="Current password" autocomplete="current-password" />@error('current_password')<flux:error class="mt-2">{{ $message }}</flux:error>@enderror</div><div><flux:input wire:model="password" type="password" label="New password" autocomplete="new-password" />@error('password')<flux:error class="mt-2">{{ $message }}</flux:error>@enderror</div><flux:input wire:model="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" /><div class="flex justify-end border-t border-zinc-200 pt-5 sm:col-span-2 dark:border-zinc-800"><flux:button type="submit" variant="primary" icon="key">Update password</flux:button></div></form>
                        </section>
                        <section class="border-t border-zinc-200 pt-7 dark:border-zinc-800">
                            <flux:heading size="lg">Signed-in sessions</flux:heading>
                            <flux:text class="mt-1">The current session remains active. Other sessions are invalidated on their next request.</flux:text>
                            <form wire:submit="logoutOtherSessions" class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end"><div class="min-w-0 flex-1"><flux:input wire:model="current_password" type="password" label="Current password" autocomplete="current-password" /></div><flux:button type="submit" variant="ghost" icon="arrow-right-start-on-rectangle" class="shrink-0">Logout other sessions</flux:button></form>
                        </section>
                    </div>
                    <aside class="h-fit border-t border-zinc-200 pt-5 xl:border-s xl:border-t-0 xl:pl-6 xl:pt-0 dark:border-zinc-800"><p class="text-sm font-medium">Security information</p><dl class="mt-4 divide-y divide-zinc-200 border-y border-zinc-200 text-sm dark:divide-zinc-800 dark:border-zinc-800"><div class="flex justify-between gap-4 py-3"><dt class="text-zinc-500">Account created</dt><dd>{{ auth()->user()->created_at->format('d M Y') }}</dd></div><div class="flex justify-between gap-4 py-3"><dt class="text-zinc-500">Password storage</dt><dd>Hashed</dd></div><div class="flex justify-between gap-4 py-3"><dt class="text-zinc-500">Two-factor authentication</dt><dd>Not configured</dd></div></dl><flux:text class="mt-4 text-xs">Two-factor authentication and login-device history are not installed in this application yet.</flux:text></aside>
                </div>
            @elseif ($activeTab === 'system')
                <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_minmax(18rem,0.7fr)]"><section><flux:heading size="lg">Runtime information</flux:heading><flux:text class="mt-1">Read-only details from the active application environment.</flux:text><dl class="mt-5 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">@foreach (['Laravel' => $runtime['laravel'], 'PHP' => $runtime['php'], 'Environment' => $runtime['environment'], 'Database driver' => $runtime['database'], 'Cache driver' => $runtime['cache'], 'Queue driver' => $runtime['queue'], 'Filesystem driver' => $runtime['filesystem'], 'Mail driver' => $runtime['mail']] as $label => $value)<div class="flex justify-between gap-5 py-3"><dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ $label }}</dt><dd class="text-right text-sm font-medium">{{ $value }}</dd></div>@endforeach</dl></section><section class="border-t border-zinc-200 pt-6 xl:border-s xl:border-t-0 xl:pl-7 xl:pt-0 dark:border-zinc-800"><flux:heading size="lg">Service health</flux:heading><flux:text class="mt-1">Checks are live; missing scheduler or backup runs are shown as warnings.</flux:text><dl class="mt-5 divide-y divide-zinc-200 border-y border-zinc-200 text-sm dark:divide-zinc-800 dark:border-zinc-800">@foreach (['Database' => $operational['database'], 'Cache' => $operational['cache'], 'Storage' => $operational['storage'], 'Scheduler' => $operational['scheduler']] as $label => $value)<div class="flex justify-between gap-5 py-3"><dt class="text-zinc-500">{{ $label }}</dt><dd class="font-medium">{{ $value }}</dd></div>@endforeach</dl><div class="mt-6 border-t border-zinc-200 pt-5 dark:border-zinc-800"><flux:heading size="sm">Operations</flux:heading><dl class="mt-3 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-zinc-500">Last scheduler heartbeat</dt><dd>{{ $operational['schedulerHeartbeat']?->diffForHumans() ?? 'Not recorded' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-zinc-500">Last publish run</dt><dd>{{ $operational['lastPublishRun']?->diffForHumans() ?? 'Not recorded' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-zinc-500">Last backup</dt><dd>{{ $operational['lastBackup']?->diffForHumans() ?? 'Not recorded' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-zinc-500">Backup destination</dt><dd>{{ $operational['backupDisk'] }}</dd></div><div class="flex justify-between gap-4"><dt class="text-zinc-500">Last backup size</dt><dd>{{ $operational['backupSize'] }}</dd></div>@if($operational['backupFailure'])<div class="flex justify-between gap-4"><dt class="text-zinc-500">Last backup failure</dt><dd>{{ $operational['backupFailure']->diffForHumans() }}</dd></div>@endif</dl></div></section></div>
            @else
                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem]"><section><flux:heading size="lg">Public media storage</flux:heading><flux:text class="mt-1">Only the journal's cover and branding directories are summarized here.</flux:text><dl class="mt-5 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">@foreach (['Configured disk' => $mediaSummary['disk'], 'Media path' => $mediaSummary['path'], 'Writable' => $mediaSummary['writable'] ? 'Yes' : 'No', 'Media files' => $mediaSummary['files'], 'Storage usage' => $mediaSummary['size'], 'Maximum upload size' => $mediaSummary['max_upload']] as $label => $value)<div class="flex justify-between gap-5 py-3"><dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ $label }}</dt><dd class="max-w-[60%] text-right text-sm font-medium">{{ $value }}</dd></div>@endforeach</dl></section><aside class="h-fit border-t border-zinc-200 pt-5 lg:border-s lg:border-t-0 lg:pl-6 lg:pt-0 dark:border-zinc-800"><p class="text-sm font-medium">Safe actions</p><flux:text class="mt-1 text-sm">Inspect uploaded editorial media without direct filesystem access.</flux:text><div class="mt-4 grid gap-2"><flux:button :href="route('dashboard.media.index')" variant="ghost" size="sm" icon="photo" class="justify-start">Inspect media library</flux:button><flux:button :href="route('sitemap')" target="_blank" variant="ghost" size="sm" icon="arrow-top-right-on-square" class="justify-start">View sitemap</flux:button></div></aside></div>
            @endif
        </section>
    </div>
</div>
