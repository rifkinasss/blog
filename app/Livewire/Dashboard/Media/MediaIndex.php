<?php

namespace App\Livewire\Dashboard\Media;

use App\Models\Article;
use App\Models\AuditLog;
use App\Models\SiteSetting;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class MediaIndex extends Component
{
    use WithFileUploads;

    public mixed $upload = null;

    public ?string $selectedPath = null;

    #[Url]
    public string $search = '';

    #[Url]
    public string $type = 'all';

    #[Url]
    public string $sort = 'newest';

    public function openUpload(): void
    {
        $this->reset('upload');
        $this->resetValidation();
        Flux::modal('media-upload')->show();
    }

    public function upload(): void
    {
        $this->authorize('create', Article::class);
        $this->validate(['upload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']]);
        $path = $this->upload->store('covers', 'public');
        AuditLog::record('media.uploaded', null, ['path' => $path]);
        Flux::modal('media-upload')->close();
        $this->reset('upload');
        session()->flash('success', 'Image uploaded. Use its URL or select it from the article editor.');
    }

    public function openDetails(string $path): void
    {
        abort_unless($this->mediaItems()->contains('path', $path), 404);

        $this->selectedPath = $path;
        Flux::modal('media-details')->show();
    }

    public function deleteUnused(string $path): void
    {
        abort_unless(auth()->user()->isAdministrator(), 403);

        if ($this->referencedPaths()->contains($path)) {
            session()->flash('error', 'This file is still used by an article. Remove the reference before deleting it.');

            return;
        }

        Storage::disk('public')->delete($path);
        AuditLog::record('media.deleted', null, ['path' => $path]);
        $this->selectedPath = null;
        Flux::modal('media-details')->close();
        session()->flash('success', 'Unused media deleted.');
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'type']);
        $this->sort = 'newest';
    }

    public function render()
    {
        $allItems = $this->mediaItems();
        $items = $allItems
            ->when($this->search, fn (Collection $items) => $items->filter(fn (array $item) => str_contains(strtolower($item['filename']), strtolower($this->search))))
            ->when($this->type === 'images', fn (Collection $items) => $items->where('is_image', true))
            ->when($this->type === 'other', fn (Collection $items) => $items->where('is_image', false));

        $items = (match ($this->sort) {
            'oldest' => $items->sortBy('last_modified_timestamp'),
            'size_desc' => $items->sortByDesc('size_bytes'),
            default => $items->sortByDesc('last_modified_timestamp'),
        })->values();

        return view('livewire.dashboard.media.index', [
            'items' => $items,
            'selectedMedia' => $this->selectedPath ? $allItems->firstWhere('path', $this->selectedPath) : null,
            'summary' => [
                'total' => $allItems->count(),
                'size' => $this->formatBytes($allItems->sum('size_bytes')),
                'images' => $allItems->where('is_image', true)->count(),
                'other' => $allItems->where('is_image', false)->count(),
            ],
        ])->layout('layouts.dashboard');
    }

    /** @return Collection<int, array<string, mixed>> */
    private function mediaItems(): Collection
    {
        $disk = Storage::disk('public');
        $usage = Article::query()->whereNotNull('cover_image')->pluck('cover_image')
            ->merge(Article::query()->whereNotNull('og_image')->pluck('og_image'))
            ->countBy();

        return collect($disk->files('covers'))->map(function (string $path) use ($disk, $usage): array {
            $mime = $disk->mimeType($path) ?: 'application/octet-stream';
            $isImage = str_starts_with($mime, 'image/');
            $dimensions = null;

            if ($isImage && is_file($disk->path($path))) {
                $imageSize = @getimagesize($disk->path($path));
                $dimensions = $imageSize ? $imageSize[0].' × '.$imageSize[1].' px' : null;
            }

            $lastModified = $disk->lastModified($path);

            return [
                'path' => $path,
                'filename' => basename($path),
                'mime' => $mime,
                'is_image' => $isImage,
                'size_bytes' => $disk->size($path),
                'size' => $this->formatBytes($disk->size($path)),
                'last_modified' => Carbon::createFromTimestamp($lastModified),
                'last_modified_timestamp' => $lastModified,
                'dimensions' => $dimensions,
                'url' => $disk->url($path),
                'usage_count' => (int) ($usage[$path] ?? 0),
            ];
        });
    }

    /** @return Collection<int, string> */
    private function referencedPaths(): Collection
    {
        return Article::withTrashed()->whereNotNull('cover_image')->pluck('cover_image')
            ->merge(Article::withTrashed()->whereNotNull('og_image')->pluck('og_image'))
            ->merge(User::query()->whereNotNull('avatar_path')->pluck('avatar_path'))
            ->merge(SiteSetting::query()->whereIn('key', ['brand_logo', 'brand_dark_logo', 'favicon', 'apple_touch_icon', 'default_og_image', 'author_avatar'])->pluck('value'));
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
}
