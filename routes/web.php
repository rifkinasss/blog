<?php

use App\Http\Controllers\AdminPostController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\HealthController;
use App\Livewire\Dashboard\Activity\ActivityLogIndex;
use App\Livewire\Dashboard\Analytics\AnalyticsIndex;
use App\Livewire\Dashboard\Articles\ArticleForm;
use App\Livewire\Dashboard\Articles\ArticleIndex;
use App\Livewire\Dashboard\Automation\OpenClawIndex;
use App\Livewire\Dashboard\Categories\CategoryIndex;
use App\Livewire\Dashboard\DashboardOverview;
use App\Livewire\Dashboard\Media\MediaIndex;
use App\Livewire\Dashboard\Settings\SettingsIndex;
use App\Livewire\Dashboard\Tags\TagIndex;
use App\Livewire\Dashboard\Users\UserIndex;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Route;

// A signed preview is a short-lived private capability, not an indexable public URL.
Route::get('/health', HealthController::class)->name('health');
Route::get('/preview/{article}', [BlogController::class, 'preview'])->middleware(['signed', 'throttle:article-preview'])->name('articles.preview');

// Preserve old public URLs while ensuring each reader and crawler lands on one canonical locale URL.
Route::get('/', fn () => redirect('/'.config('app.locale')));
Route::get('/articles', fn () => redirect('/'.config('app.locale').'/articles'));
Route::get('/articles/{article:slug}', fn (Article $article) => redirect('/'.config('app.locale').'/articles/'.$article->slug));
Route::get('/categories/{category:slug}', fn (Category $category) => redirect('/'.config('app.locale').'/categories/'.$category->slug));
Route::get('/tags/{tag:slug}', fn (Tag $tag) => redirect('/'.config('app.locale').'/tags/'.$tag->slug));
Route::get('/projects', fn () => redirect('/'.config('app.locale').'/projects'));
Route::get('/about', fn () => redirect('/'.config('app.locale').'/about'));

Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => implode('|', array_keys(config('laravellocalization.supportedLocales')))],
    'middleware' => ['set-localized-locale', 'localize', 'localeViewPath'],
], function (): void {
    Route::get('/', [BlogController::class, 'home'])->name('home');
    Route::get('/articles', [BlogController::class, 'index'])->name('articles.index');
    Route::get('/articles/{article:slug}', [BlogController::class, 'show'])->name('articles.show');
    Route::get('/categories/{category:slug}', [BlogController::class, 'category'])->name('categories.show');
    Route::get('/tags/{tag:slug}', [BlogController::class, 'tag'])->name('tags.show');
    Route::view('/projects', 'blog.projects')->name('projects');
    Route::get('/about', [BlogController::class, 'about'])->name('about');
});
Route::get('/rss.xml', [BlogController::class, 'rss'])->name('feed');
Route::get('/sitemap.xml', [BlogController::class, 'sitemap'])->name('sitemap');
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', DashboardOverview::class)->name('index');
    Route::get('/articles', ArticleIndex::class)->name('articles.index');
    Route::get('/articles/create', ArticleForm::class)->name('articles.create');
    Route::get('/articles/{article}/edit', ArticleForm::class)->name('articles.edit');
    Route::get('/categories', CategoryIndex::class)->name('categories.index');
    Route::get('/tags', TagIndex::class)->name('tags.index');
    Route::get('/media', MediaIndex::class)->name('media.index');
    Route::get('/analytics', AnalyticsIndex::class)->name('analytics.index');
    Route::get('/activity', ActivityLogIndex::class)->name('activity.index');
    Route::get('/automation/openclaw', OpenClawIndex::class)->name('automation.openclaw');
    Route::get('/users', UserIndex::class)->name('users.index');
    Route::get('/settings', SettingsIndex::class)->name('settings.index');
});
Route::view('/login', 'auth.login')->middleware('guest')->name('login');
Route::post('/login', [AdminPostController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('login.store');
Route::post('/logout', [AdminPostController::class, 'logout'])->middleware('auth')->name('logout');
