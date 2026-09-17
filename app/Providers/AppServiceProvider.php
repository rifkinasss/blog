<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\SiteSetting;
use App\Policies\ArticlePolicy;
use App\Services\Markdown;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Markdown::class);
    }

    public function boot(): void
    {
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('openclaw-api', fn (Request $request) => Limit::perMinute(60)->by($request->bearerToken() ? hash('sha256', $request->bearerToken()) : $request->ip()));
        RateLimiter::for('openclaw-media', fn (Request $request) => Limit::perMinute(10)->by($request->bearerToken() ? hash('sha256', $request->bearerToken()) : $request->ip()));
        RateLimiter::for('article-preview', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
        Gate::policy(Article::class, ArticlePolicy::class);
        Blade::directive('markdown', fn ($value) => "<?php echo app('".Markdown::class."')->render($value); ?>");
        URL::defaults(['locale' => config('app.locale')]);
        View::composer(['layouts.blog', 'blog.show'], function ($view): void {
            $siteName = SiteSetting::value('site_name', config('app.name'));
            $siteDescription = SiteSetting::value('site_description', 'Personal engineering journal.');
            $publicDisk = Storage::disk('public');
            $assetUrl = fn (string $key): ?string => ($path = SiteSetting::value($key)) && $publicDisk->exists($path) ? $publicDisk->url($path) : null;

            $view
                ->with('siteName', $siteName)
                ->with('siteDescription', $siteDescription)
                ->with('siteMetaTitle', SiteSetting::value('default_meta_title', $siteName))
                ->with('siteMetaDescription', SiteSetting::value('default_meta_description', $siteDescription))
                ->with('titleSuffix', SiteSetting::value('title_suffix', '· '.$siteName))
                ->with('canonicalBaseUrl', SiteSetting::value('canonical_base_url', SiteSetting::value('public_url', config('app.url'))))
                ->with('robotsIndexing', $view->getData()['robotsIndexing'] ?? filter_var(SiteSetting::value('robots_indexing', '1'), FILTER_VALIDATE_BOOLEAN))
                ->with('defaultOgImageUrl', $assetUrl('default_og_image'))
                ->with('brandLogoUrl', $assetUrl('brand_logo'))
                ->with('brandDarkLogoUrl', $assetUrl('brand_dark_logo'))
                ->with('faviconUrl', $assetUrl('favicon'))
                ->with('appleTouchIconUrl', $assetUrl('apple_touch_icon'))
                ->with('siteAuthorName', SiteSetting::value('author_name', $siteName))
                ->with('siteAuthorAvatarUrl', $assetUrl('author_avatar'))
                ->with('dateFormat', SiteSetting::value('date_format', 'd M Y'));
        });
    }
}
