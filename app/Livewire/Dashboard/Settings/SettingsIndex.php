<?php

namespace App\Livewire\Dashboard\Settings;

use App\Enums\ArticleStatus;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingsIndex extends Component
{
    use WithFileUploads;

    private const BRAND_ASSET_KEYS = [
        'brand_logo' => 'Primary logo',
        'brand_dark_logo' => 'Dark-mode logo',
        'favicon' => 'Favicon',
        'apple_touch_icon' => 'Apple touch icon',
        'default_og_image' => 'Default Open Graph image',
        'author_avatar' => 'Author avatar',
    ];

    #[Url(as: 'tab')]
    public string $activeTab = 'general';

    public string $name = '';

    public string $email = '';

    public mixed $profile_avatar = null;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $timezone = 'Asia/Makassar';

    public string $locale = 'id';

    public string $site_name = '';

    public string $site_description = '';

    public string $public_url = '';

    public string $author_name = '';

    public mixed $brand_logo = null;

    public mixed $brand_dark_logo = null;

    public mixed $favicon = null;

    public mixed $apple_touch_icon = null;

    public mixed $default_og_image = null;

    public mixed $author_avatar = null;

    public string $default_article_status = 'draft';

    public mixed $default_category_id = null;

    public mixed $default_author_id = null;

    public bool $auto_generate_slug = true;

    public string $articles_per_page = '9';

    public string $date_format = 'd M Y';

    public bool $require_category_before_publish = false;

    public bool $require_excerpt_before_publish = false;

    public bool $allow_service_drafts = true;

    public bool $allow_service_publish = false;

    public bool $service_publish_requires_review = true;

    public string $default_meta_title = '';

    public string $title_suffix = '';

    public string $default_meta_description = '';

    public string $canonical_base_url = '';

    public bool $sitemap_enabled = true;

    public bool $robots_indexing = true;

    public function mount(): void
    {
        if (! auth()->user()->isAdministrator() && ! in_array($this->activeTab, ['account', 'security'], true)) {
            $this->activeTab = 'account';
        }

        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
        $this->timezone = SiteSetting::value('timezone', config('app.timezone'));
        $this->locale = SiteSetting::value('locale', config('app.locale'));
        $this->site_name = SiteSetting::value('site_name', config('app.name'));
        $this->site_description = SiteSetting::value('site_description', 'Personal engineering journal.');
        $this->public_url = SiteSetting::value('public_url', config('app.url'));
        $this->author_name = SiteSetting::value('author_name', auth()->user()->name);
        $this->default_article_status = SiteSetting::value('default_article_status', ArticleStatus::Draft->value);
        $this->default_category_id = SiteSetting::value('default_category_id');
        $this->default_author_id = SiteSetting::value('default_author_id', (string) auth()->id());
        $this->auto_generate_slug = $this->settingBool('auto_generate_slug', true);
        $this->articles_per_page = SiteSetting::value('articles_per_page', '9');
        $this->date_format = SiteSetting::value('date_format', 'd M Y');
        $this->require_category_before_publish = $this->settingBool('require_category_before_publish');
        $this->require_excerpt_before_publish = $this->settingBool('require_excerpt_before_publish');
        $this->allow_service_drafts = $this->settingBool('allow_service_drafts', true);
        $this->allow_service_publish = $this->settingBool('allow_service_publish');
        $this->service_publish_requires_review = $this->settingBool('service_publish_requires_review', true);
        $this->default_meta_title = SiteSetting::value('default_meta_title', $this->site_name);
        $this->title_suffix = SiteSetting::value('title_suffix', '· '.$this->site_name);
        $this->default_meta_description = SiteSetting::value('default_meta_description', $this->site_description);
        $this->canonical_base_url = SiteSetting::value('canonical_base_url', $this->public_url);
        $this->sitemap_enabled = $this->settingBool('sitemap_enabled', true);
        $this->robots_indexing = $this->settingBool('robots_indexing', true);
    }

    public function selectTab(string $tab): void
    {
        abort_unless(in_array($tab, ['general', 'branding', 'publishing', 'seo', 'account', 'security', 'system', 'storage'], true), 404);
        abort_if(in_array($tab, ['general', 'branding', 'publishing', 'seo', 'system', 'storage'], true) && ! auth()->user()->isAdministrator(), 403);

        $this->activeTab = $tab;
        $this->resetValidation();
    }

    public function saveGeneral(): void
    {
        $this->ensureAdministrator();
        $data = $this->validate([
            'timezone' => ['required', Rule::in(['Asia/Makassar', 'Asia/Jakarta', 'Asia/Jayapura', 'UTC'])],
            'locale' => ['required', Rule::in(['id', 'en'])],
        ]);
        $this->putSettings($data);
        session()->flash('success', 'General settings saved.');
    }

    public function saveBranding(): void
    {
        $this->ensureAdministrator();
        $data = $this->validate([
            'site_name' => ['required', 'string', 'max:80'],
            'site_description' => ['required', 'string', 'max:180'],
            'public_url' => ['required', 'url', 'max:180'],
            'author_name' => ['required', 'string', 'max:120'],
            'brand_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'brand_dark_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:ico,png', 'max:1024'],
            'apple_touch_icon' => ['nullable', 'image', 'mimes:png', 'max:1024'],
            'default_og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'author_avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        foreach (self::BRAND_ASSET_KEYS as $property => $label) {
            if ($data[$property] ?? null) {
                $data[$property] = $this->replaceBrandAsset($data[$property], $property);
            } else {
                unset($data[$property]);
            }
        }

        $this->putSettings($data);
        $this->reset(array_keys(self::BRAND_ASSET_KEYS));
        session()->flash('success', 'Branding saved.');
    }

    public function removeBrandAsset(string $key): void
    {
        $this->ensureAdministrator();
        abort_unless(array_key_exists($key, self::BRAND_ASSET_KEYS), 404);

        $path = SiteSetting::value($key);
        if ($path && str_starts_with($path, 'branding/')) {
            Storage::disk('public')->delete($path);
        }

        SiteSetting::query()->where('key', $key)->delete();
        AuditLog::record('site_brand_asset.removed', null, ['key' => $key]);
        session()->flash('success', self::BRAND_ASSET_KEYS[$key].' removed.');
    }

    public function saveSiteSettings(): void
    {
        $this->saveBranding();
    }

    public function savePublishing(): void
    {
        $this->ensureAdministrator();
        $data = $this->validate([
            'default_article_status' => ['required', Rule::enum(ArticleStatus::class)],
            'default_category_id' => ['nullable', 'exists:categories,id'],
            'default_author_id' => ['nullable', 'exists:users,id'],
            'auto_generate_slug' => ['boolean'],
            'articles_per_page' => ['required', Rule::in(['6', '9', '12', '18'])],
            'date_format' => ['required', Rule::in(['d M Y', 'd/m/Y', 'M j, Y'])],
            'require_category_before_publish' => ['boolean'],
            'require_excerpt_before_publish' => ['boolean'],
            'allow_service_drafts' => ['boolean'],
            'allow_service_publish' => ['boolean'],
            'service_publish_requires_review' => ['boolean'],
        ]);
        $this->putSettings($data);
        session()->flash('success', 'Publishing defaults saved.');
    }

    public function saveSeo(): void
    {
        $this->ensureAdministrator();
        $data = $this->validate([
            'default_meta_title' => ['required', 'string', 'max:180'],
            'title_suffix' => ['nullable', 'string', 'max:80'],
            'default_meta_description' => ['required', 'string', 'max:300'],
            'canonical_base_url' => ['required', 'url', 'max:180'],
            'sitemap_enabled' => ['boolean'],
            'robots_indexing' => ['boolean'],
        ]);
        $this->putSettings($data);
        session()->flash('success', 'SEO defaults saved.');
    }

    public function saveProfile(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore(auth()->id())],
        ]);
        auth()->user()->update($data);
        AuditLog::record('profile.updated', auth()->user());
        session()->flash('success', 'Account profile saved.');
    }

    public function uploadAvatar(): void
    {
        $this->validate([
            'profile_avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = auth()->user();
        $previousPath = $user->avatar_path;
        $path = $this->profile_avatar->store('avatars/'.$user->id, 'public');

        $user->update(['avatar_path' => $path]);

        if ($user->ownsAvatarPath($previousPath)) {
            Storage::disk('public')->delete($previousPath);
        }

        AuditLog::record('profile.avatar_updated', $user);
        $this->reset('profile_avatar');
        session()->flash('success', 'Profile photo updated.');
    }

    public function removeAvatar(): void
    {
        $user = auth()->user();
        $path = $user->avatar_path;

        if ($user->ownsAvatarPath($path)) {
            Storage::disk('public')->delete($path);
        }

        $user->update(['avatar_path' => null]);
        AuditLog::record('profile.avatar_removed', $user);
        session()->flash('success', 'Profile photo removed.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update(['password' => $this->password]);
        AuditLog::record('password.updated', auth()->user());
        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('success', 'Password updated.');
    }

    public function logoutOtherSessions(): void
    {
        $this->validate(['current_password' => ['required', 'current_password']]);

        Auth::logoutOtherDevices($this->current_password);
        AuditLog::record('sessions.other_logged_out', auth()->user());
        $this->reset('current_password');
        session()->flash('success', 'Other signed-in sessions were logged out.');
    }

    public function render()
    {
        $disk = Storage::disk('public');
        $mediaFiles = collect($disk->allFiles())->filter(fn (string $path) => str_starts_with($path, 'covers/') || str_starts_with($path, 'branding/'));

        return view('livewire.dashboard.settings.index', [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'authors' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'brandAssets' => collect(self::BRAND_ASSET_KEYS)->mapWithKeys(fn (string $label, string $key) => [$key => $this->assetDetails($key)]),
            'mediaSummary' => [
                'disk' => config('filesystems.disks.public.driver'),
                'path' => 'storage/app/public/{covers,branding}',
                'writable' => is_writable(storage_path('app/public')),
                'files' => $mediaFiles->count(),
                'size' => $this->formatBytes($mediaFiles->sum(fn (string $file) => $disk->size($file))),
                'max_upload' => $this->formatBytes(min($this->iniSize((string) ini_get('upload_max_filesize')), 4 * 1024 * 1024)),
            ],
            'runtime' => [
                'environment' => app()->environment(),
                'laravel' => app()->version(),
                'php' => PHP_VERSION,
                'database' => config('database.default'),
                'cache' => config('cache.default'),
                'queue' => config('queue.default'),
                'filesystem' => config('filesystems.default'),
                'mail' => config('mail.default'),
            ],
            'operational' => [
                'database' => $this->status(fn () => DB::select('select 1')),
                'cache' => $this->status(fn () => Cache::put('system:probe', true, now()->addSecond())),
                'storage' => is_writable(storage_path('app/public')) ? 'Healthy' : 'Unavailable',
                'scheduler' => $this->schedulerStatus(),
                'schedulerHeartbeat' => $this->settingDate('scheduler_last_heartbeat_at'),
                'lastPublishRun' => $this->settingDate('scheduler_last_publish_run_at'),
                'lastBackup' => $this->settingDate('backup_last_success_at'),
                'backupFailure' => $this->settingDate('backup_last_failure_at'),
                'backupSize' => $this->formatBytes((int) SiteSetting::value('backup_last_size_bytes', '0')),
                'backupDisk' => config('backup.backup.destination.disks.0', 'local'),
            ],
            'recentActivity' => auth()->user()->isAdministrator() ? AuditLog::query()->with('user')->latest()->limit(8)->get() : collect(),
        ])->layout('layouts.dashboard');
    }

    private function ensureAdministrator(): void
    {
        abort_unless(auth()->user()->isAdministrator(), 403);
    }

    private function putSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $value = in_array($key, ['public_url', 'canonical_base_url'], true) ? rtrim((string) $value, '/') : (is_bool($value) ? ($value ? '1' : '0') : (string) $value);
            SiteSetting::put($key, $value);
        }
        AuditLog::record('site_settings.updated', null, ['keys' => array_keys($settings)]);
    }

    private function replaceBrandAsset(mixed $file, string $key): string
    {
        $oldPath = SiteSetting::value($key);
        if ($oldPath && str_starts_with($oldPath, 'branding/')) {
            Storage::disk('public')->delete($oldPath);
        }

        return $file->store('branding', 'public');
    }

    private function settingBool(string $key, bool $default = false): bool
    {
        return filter_var(SiteSetting::value($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }

    private function assetDetails(string $key): ?array
    {
        $path = SiteSetting::value($key);
        $disk = Storage::disk('public');
        if (! $path || ! $disk->exists($path)) {
            return null;
        }

        $dimensions = null;
        $size = @getimagesize($disk->path($path));
        if ($size) {
            $dimensions = $size[0].' × '.$size[1].' px';
        }

        return ['path' => $path, 'url' => $disk->url($path), 'type' => $disk->mimeType($path) ?: 'unknown', 'dimensions' => $dimensions];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return number_format($bytes / (1024 ** $power), $power === 0 ? 0 : 1).' '.$units[$power];
    }

    private function iniSize(string $value): int
    {
        $number = (int) $value;

        return match (strtolower(substr(trim($value), -1))) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function status(callable $check): string
    {
        try {
            $check();

            return 'Healthy';
        } catch (\Throwable) {
            return 'Unavailable';
        }
    }

    private function schedulerStatus(): string
    {
        $heartbeat = $this->settingDate('scheduler_last_heartbeat_at');

        return ! $heartbeat ? 'Warning' : ($heartbeat->gt(now()->subMinutes(3)) ? 'Healthy' : 'Stale');
    }

    private function settingDate(string $key): ?Carbon
    {
        return ($value = SiteSetting::value($key)) ? Carbon::parse($value) : null;
    }
}
