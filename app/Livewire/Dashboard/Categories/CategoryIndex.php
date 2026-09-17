<?php

namespace App\Livewire\Dashboard\Categories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryIndex extends Component
{
    use WithPagination;

    public string $name = '';

    public string $description = '';

    public ?int $editingId = null;

    #[Url]
    public string $search = '';

    #[Url]
    public string $coverage = 'all';

    #[Url]
    public string $sort = 'articles_desc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCoverage(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->resetValidation();
        Flux::modal('category-form')->show();
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('categories', 'name')->ignore($this->editingId)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category = $this->editingId ? Category::findOrFail($this->editingId) : new Category;
        $category->fill($data);
        $category->slug = str($data['name'])->slug();
        $category->save();

        Flux::modal('category-form')->close();
        $this->resetForm();
        session()->flash('success', 'Category saved.');
    }

    public function edit(Category $category): void
    {
        $this->resetValidation();
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        Flux::modal('category-form')->show();
    }

    public function delete(Category $category): void
    {
        if ($category->articles()->exists()) {
            session()->flash('error', 'This category is still assigned to articles.');

            return;
        }

        $category->delete();
        session()->flash('success', 'Category deleted.');
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'coverage']);
        $this->sort = 'articles_desc';
        $this->resetPage();
    }

    public function render()
    {
        $categories = $this->categoriesQuery()->paginate(20);
        $largestCategory = Category::query()->withCount('articles')->orderByDesc('articles_count')->first();
        $distribution = Category::query()->withCount('articles')->orderByDesc('articles_count')->limit(10)->get();

        return view('livewire.dashboard.categories.index', [
            'categories' => $categories,
            'summary' => [
                'total' => Category::count(),
                'withArticles' => Category::has('articles')->count(),
                'empty' => Category::doesntHave('articles')->count(),
                'largest' => $largestCategory,
            ],
            'distribution' => $distribution,
            'distributionMax' => max(1, (int) ($distribution->max('articles_count') ?? 0)),
            'totalArticles' => max(1, Article::count()),
        ])->layout('layouts.dashboard');
    }

    private function categoriesQuery(): Builder
    {
        $query = Category::query()
            ->withCount('articles')
            ->withCount(['articles as published_articles_count' => fn (Builder $query) => $query->published()])
            ->withCount(['articles as draft_articles_count' => fn (Builder $query) => $query->where('status', ArticleStatus::Draft)])
            ->withMax(['articles as last_published_at' => fn (Builder $query) => $query->published()], 'published_at')
            ->when($this->search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'ilike', '%'.$this->search.'%')
                ->orWhere('slug', 'ilike', '%'.$this->search.'%')
                ->orWhere('description', 'ilike', '%'.$this->search.'%')))
            ->when($this->coverage === 'empty', fn (Builder $query) => $query->doesntHave('articles'))
            ->when($this->coverage === 'assigned', fn (Builder $query) => $query->has('articles'));

        return match ($this->sort) {
            'alphabetical' => $query->orderBy('name'),
            'updated_desc' => $query->latest(),
            'last_published_desc' => $query->orderByDesc('last_published_at'),
            default => $query->orderByDesc('articles_count')->orderBy('name'),
        };
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'description', 'editingId']);
    }
}
