<?php

namespace App\Livewire\Dashboard\Tags;

use App\Models\Tag;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TagIndex extends Component
{
    use WithPagination;

    public string $name = '';

    public ?int $editingId = null;

    #[Url]
    public string $search = '';

    #[Url]
    public string $usage = 'all';

    #[Url]
    public string $sort = 'usage_desc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUsage(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'editingId']);
        $this->resetValidation();
        Flux::modal('tag-form')->show();
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('tags', 'name')->ignore($this->editingId)],
        ]);

        $tag = $this->editingId ? Tag::findOrFail($this->editingId) : new Tag;
        $tag->name = $data['name'];
        $tag->slug = str($data['name'])->slug();
        $tag->save();

        Flux::modal('tag-form')->close();
        $this->reset(['name', 'editingId']);
        session()->flash('success', 'Tag saved.');
    }

    public function edit(Tag $tag): void
    {
        $this->resetValidation();
        $this->editingId = $tag->id;
        $this->name = $tag->name;
        Flux::modal('tag-form')->show();
    }

    public function delete(Tag $tag): void
    {
        if ($tag->articles()->exists()) {
            session()->flash('error', 'This tag is still assigned to articles.');

            return;
        }

        $tag->delete();
        session()->flash('success', 'Tag deleted.');
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'usage']);
        $this->sort = 'usage_desc';
        $this->resetPage();
    }

    public function render()
    {
        $tags = $this->tagsQuery()->paginate(20);
        $distribution = Tag::query()->withCount('articles')->orderByDesc('articles_count')->limit(10)->get();
        $mostUsed = $distribution->first();

        return view('livewire.dashboard.tags.index', [
            'tags' => $tags,
            'summary' => [
                'total' => Tag::count(),
                'used' => Tag::has('articles')->count(),
                'unused' => Tag::doesntHave('articles')->count(),
                'mostUsed' => $mostUsed,
            ],
            'distribution' => $distribution,
            'distributionMax' => max(1, (int) ($distribution->max('articles_count') ?? 0)),
        ])->layout('layouts.dashboard');
    }

    private function tagsQuery(): Builder
    {
        $query = Tag::query()
            ->withCount('articles')
            ->withMax('articles as last_used_at', 'updated_at')
            ->when($this->search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'ilike', '%'.$this->search.'%')
                ->orWhere('slug', 'ilike', '%'.$this->search.'%')))
            ->when($this->usage === 'used', fn (Builder $query) => $query->has('articles'))
            ->when($this->usage === 'unused', fn (Builder $query) => $query->doesntHave('articles'));

        return match ($this->sort) {
            'alphabetical' => $query->orderBy('name'),
            'last_used_desc' => $query->orderByDesc('last_used_at'),
            default => $query->orderByDesc('articles_count')->orderBy('name'),
        };
    }
}
