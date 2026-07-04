<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class MediaManager extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Public Storage';
    protected static ?string $title = 'Public Storage';
    protected static ?string $slug = 'public-storage';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 98;

    protected static string $view = 'filament.pages.media-manager';

    public string $currentPath = '';

    public array $directories = [];

    public array $files = [];

    public mixed $upload = null;

    public ?string $replaceTarget = null;

    public function mount(): void
    {
        $this->loadContents();
    }

    public function refreshBrowser(): void
    {
        $this->loadContents();
    }

    public function breadcrumbs(): array
    {
        $breadcrumbs = [
            ['label' => 'storage/app/public', 'path' => ''],
        ];

        if ($this->currentPath === '') {
            return $breadcrumbs;
        }

        $segments = array_values(array_filter(explode('/', $this->currentPath)));
        $path = '';

        foreach ($segments as $segment) {
            $path = $path === '' ? $segment : $path . '/' . $segment;

            $breadcrumbs[] = [
                'label' => $segment,
                'path' => $path,
            ];
        }

        return $breadcrumbs;
    }

    public function locationLabel(): string
    {
        return $this->currentPath === ''
            ? 'storage/app/public'
            : 'storage/app/public/' . $this->currentPath;
    }

    public function setPath(string $path): void
    {
        $this->currentPath = $this->normalizePath($path);
        $this->replaceTarget = null;
        $this->upload = null;
        $this->loadContents();
    }

    public function goUp(): void
    {
        if ($this->currentPath === '') {
            return;
        }

        $segments = array_values(array_filter(explode('/', $this->currentPath)));
        array_pop($segments);
        $this->currentPath = implode('/', $segments);
        $this->replaceTarget = null;
        $this->upload = null;
        $this->loadContents();
    }

    public function cancelReplace(): void
    {
        $this->replaceTarget = null;
        $this->upload = null;
    }

    public function selectForReplace(string $path): void
    {
        $this->replaceTarget = $this->normalizePath($path);
        $this->upload = null;
    }

    public function loadContents(): void
    {
        $disk = Storage::disk('public');
        $basePath = $this->normalizePath($this->currentPath);

        $this->directories = collect($disk->directories($basePath))
            ->sort()
            ->map(function (string $directory): array {
                return [
                    'name' => basename($directory),
                    'path' => $directory,
                ];
            })
            ->values()
            ->all();

        $this->files = collect($disk->files($basePath))
            ->sort()
            ->map(function (string $file): array {
                return [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => $this->fileSize($file),
                    'modified' => $this->lastModified($file),
                    'mime' => $this->mimeType($file),
                    'previewable' => $this->isPreviewable($file),
                    'url' => $this->openFileUrl($file),
                ];
            })
            ->values()
            ->all();
    }

    public function deleteFile(string $path): void
    {
        $path = $this->normalizePath($path);

        if (! Storage::disk('public')->exists($path)) {
            Notification::make()
                ->title('File not found')
                ->danger()
                ->send();

            return;
        }

        Storage::disk('public')->delete($path);
        $this->refreshBrowser();

        Notification::make()
            ->title('File deleted')
            ->success()
            ->send();
    }

    public function deleteDirectory(string $path): void
    {
        $path = $this->normalizePath($path);

        if ($path === '' || $path === '.') {
            Notification::make()
                ->title('Root folder cannot be deleted')
                ->danger()
                ->send();

            return;
        }

        if (! Storage::disk('public')->exists($path) && ! Storage::disk('public')->exists(rtrim($path, '/'))) {
            Notification::make()
                ->title('Directory not found')
                ->danger()
                ->send();

            return;
        }

        Storage::disk('public')->deleteDirectory($path);
        $this->refreshBrowser();

        Notification::make()
            ->title('Folder deleted')
            ->success()
            ->send();
    }

    public function openFileUrl(string $path): string
    {
        return $this->publicUrl($path) . '?v=' . Storage::disk('public')->lastModified($path);
    }

    public function replaceUpload(): void
    {
        $this->validate([
            'upload' => ['required', 'file', 'max:51200'],
        ]);

        if (! $this->upload) {
            return;
        }

        $targetPath = $this->replaceTarget
            ? $this->normalizePath($this->replaceTarget)
            : $this->normalizePath(trim($this->currentPath . '/' . $this->upload->getClientOriginalName(), '/'));

        $directory = trim((string) dirname($targetPath), '/');
        $fileName = basename($targetPath);
        $disk = Storage::disk('public');

        if ($disk->exists($targetPath)) {
            $disk->delete($targetPath);
        }

        $disk->putFileAs($directory === '.' ? '' : $directory, $this->upload, $fileName);

        $this->upload = null;
        $this->replaceTarget = null;
        $this->loadContents();

        Notification::make()
            ->title('File saved')
            ->success()
            ->send();
    }

    public function fileSize(string $path): string
    {
        $bytes = Storage::disk('public')->size($path);

        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    public function lastModified(string $path): string
    {
        return now()->setTimestamp(Storage::disk('public')->lastModified($path))->format('M d, Y H:i');
    }

    public function directoryLabel(string $path): string
    {
        return $path === '' ? 'storage/app/public' : Str::of($path)->replace('/', ' / ')->toString();
    }

    public function mimeType(string $path): string
    {
        return Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
    }

    public function isPreviewable(string $path): bool
    {
        $mimeType = $this->mimeType($path);

        return str_starts_with($mimeType, 'image/') || $mimeType === 'application/pdf';
    }

    protected function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $segments = array_values(array_filter(explode('/', $path), static fn (string $segment): bool => $segment !== ''));
        $cleanSegments = [];

        foreach ($segments as $segment) {
            if ($segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($cleanSegments);
                continue;
            }

            $cleanSegments[] = $segment;
        }

        return implode('/', $cleanSegments);
    }

    protected function publicUrl(string $path): string
    {
        $path = $this->normalizePath($path);
        $url = Storage::disk('public')->url($path);

        return preg_replace('#(?<!:)//+#', '/', $url) ?? $url;
    }
    public static function canAccess(): bool
    {
        return auth()->user()->can('view_media_manager');
    }
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
