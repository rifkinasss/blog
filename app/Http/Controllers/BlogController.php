<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Services\Markdown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function home(string $locale)
    {
        $categories = Category::query()
            ->whereHas('articles', fn ($query) => $query->published())
            ->withCount(['articles' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();

        return view('blog.home', [
            'featured' => Article::published()->with('category')->latest('published_at')->first(),
            'articles' => Article::published()->with('category')->latest('published_at')->get(),
            'categories' => $categories,
            'popularTags' => Tag::withCount(['articles' => fn ($query) => $query->published()])->orderByDesc('articles_count')->limit(6)->get(),
            'publishedCount' => Article::published()->count(),
            'categoryCount' => $categories->count(),
        ]);
    }

    public function index(string $locale, Request $request)
    {
        $query = Article::published()->with('category')->latest('published_at');
        $search = trim((string) $request->input('q'));
        if ($search) {
            $query->where(fn ($q) => $q->where('title', 'ilike', "%{$search}%")->orWhere('excerpt', 'ilike', "%{$search}%"));
        } $articles = $query->paginate($this->articlesPerPage())->withQueryString();

        return view('blog.index', ['articles' => $articles, 'posts' => $articles, 'search' => $search]);
    }

    public function show(string $locale, Article $article, Markdown $markdown)
    {
        abort_unless($article->status->value === 'published' && $article->published_at?->isPast(), 404);

        $article->load(['author', 'category', 'tags']);
        $rendered = $markdown->renderWithTableOfContents($article->content);

        $relatedArticles = Article::published()
            ->with('category')
            ->whereKeyNot($article)
            ->where(function ($query) use ($article) {
                $query->when(
                    $article->category_id,
                    fn ($query) => $query->where('category_id', $article->category_id)
                )->orWhereHas('tags', fn ($query) => $query->whereIn('tags.id', $article->tags->modelKeys()));
            })
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', [
            'article' => $article,
            'post' => $article,
            'articleHtml' => $rendered['html'],
            'headings' => $rendered['headings'],
            'relatedArticles' => $relatedArticles,
            'previousArticle' => Article::published()->where('published_at', '<', $article->published_at)->latest('published_at')->first(),
            'nextArticle' => Article::published()->where('published_at', '>', $article->published_at)->oldest('published_at')->first(),
            'pageTitle' => $article->meta_title ?: $article->title,
            'pageDescription' => $this->articleDescription($article),
            'pageOpenGraphImage' => $this->articleOpenGraphImage($article),
            'canonicalUrl' => $article->canonical_url ?: route('articles.show', $article),
            'robotsIndexing' => $article->robots_index,
        ]);
    }

    public function preview(Article $article, Markdown $markdown)
    {
        $article->load(['author', 'category', 'tags']);
        $rendered = $markdown->renderWithTableOfContents($article->content);

        return view('blog.show', [
            'article' => $article,
            'post' => $article,
            'articleHtml' => $rendered['html'],
            'headings' => $rendered['headings'],
            'relatedArticles' => collect(),
            'previousArticle' => null,
            'nextArticle' => null,
            'pageTitle' => 'Preview · '.$article->title,
            'pageDescription' => $this->articleDescription($article),
            'pageOpenGraphImage' => $this->articleOpenGraphImage($article),
            'canonicalUrl' => $article->canonical_url ?: route('articles.show', $article),
            'robotsIndexing' => false,
            'isPreview' => true,
        ]);
    }

    public function category(string $locale, Category $category)
    {
        $articles = $category->articles()->published()->with('category')->latest('published_at')->paginate($this->articlesPerPage());

        return view('blog.index', ['articles' => $articles, 'posts' => $articles, 'search' => $category->name]);
    }

    public function tag(string $locale, Tag $tag)
    {
        $articles = $tag->articles()->published()->with('category')->latest('published_at')->paginate($this->articlesPerPage());

        return view('blog.index', ['articles' => $articles, 'posts' => $articles, 'search' => '#'.$tag->name]);
    }

    public function about(string $locale)
    {
        return view('blog.about', [
            'pageTitle' => __('blog.about_seo_title'),
            'pageDescription' => __('blog.about_seo_description'),
            'titleSuffix' => null,
        ]);
    }

    public function rss()
    {
        $articles = Article::published()->latest('published_at')->limit(20)->get();

        return response()->view('blog.rss', ['articles' => $articles, 'posts' => $articles])->header('Content-Type', 'application/rss+xml');
    }

    public function sitemap()
    {
        abort_unless(filter_var(SiteSetting::value('sitemap_enabled', '1'), FILTER_VALIDATE_BOOLEAN), 404);

        $articles = Article::published()->get();

        return response()->view('blog.sitemap', ['articles' => $articles, 'posts' => $articles])->header('Content-Type', 'application/xml');
    }

    private function articlesPerPage(): int
    {
        $value = (int) SiteSetting::value('articles_per_page', '9');

        return in_array($value, [6, 9, 12, 18], true) ? $value : 9;
    }

    private function defaultOpenGraphImage(): ?string
    {
        $path = SiteSetting::value('default_og_image');

        return $path && Storage::disk('public')->exists($path) ? Storage::disk('public')->url($path) : null;
    }

    private function articleOpenGraphImage(Article $article): ?string
    {
        $path = $article->og_image ?: $article->cover_image;

        return $path && Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : $this->defaultOpenGraphImage();
    }

    private function articleDescription(Article $article): string
    {
        if (filled($article->meta_description)) {
            return $article->meta_description;
        }

        if (filled($article->excerpt)) {
            return $article->excerpt;
        }

        return Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags($article->content))), 160);
    }
}
