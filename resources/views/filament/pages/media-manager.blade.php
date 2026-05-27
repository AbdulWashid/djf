<x-filament::page>
    <div class="media-manager">
        <style>
            .media-manager {
                --fm-border: #dee2e6;
                --fm-muted: #6c757d;
                --fm-surface: #ffffff;
                --fm-soft: #f8f9fa;
                --fm-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                --fm-shadow-lg: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.08);
            }

            .media-manager .card {
                background: var(--fm-surface);
                border: 1px solid var(--fm-border);
                border-radius: 1.5rem;
                box-shadow: var(--fm-shadow);
            }

            .media-manager .card-body {
                padding: 1.25rem;
            }

            .media-manager .media-toolbar {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
                align-items: center;
            }

            .media-manager .media-grid {
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            }

            .media-manager .media-item {
                min-width: 0;
                overflow: hidden;
                padding: 1rem;
                border: 1px solid var(--fm-border);
                border-radius: 1rem;
                background: var(--fm-soft);
                transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease,
                    border-color 0.15s ease;
            }

            .media-manager .media-item:hover {
                transform: translateY(-2px);
                background: #fff;
                border-color: #cfd4da;
                box-shadow: var(--fm-shadow-lg);
            }

            .media-manager .media-item--folder {
                background: #fff9eb;
            }

            .media-manager .media-trigger {
                display: flex;
                width: 100%;
                min-width: 0;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
                padding: 0;
                border: 0;
                background: transparent;
                color: inherit;
                text-align: left;
            }

            .media-manager .media-icon,
            .media-manager .media-thumb {
                width: 3.5rem;
                height: 3.5rem;
                flex: 0 0 auto;
                border-radius: 1rem;
                overflow: hidden;
            }

            .media-manager .media-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid transparent;
            }

            .media-manager .media-icon--folder {
                background: #fde68a;
                color: #92400e;
            }

            .media-manager .media-icon--file {
                background: #eff6ff;
                color: #64748b;
            }

            .media-manager .media-thumb {
                border: 1px solid #dbe4f0;
            }

            .media-manager .media-thumb img {
                display: block;
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .media-manager .media-title {
                width: 100%;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: 0.95rem;
                font-weight: 600;
                color: #111827;
            }

            .media-manager .media-meta {
                margin-top: 0.25rem;
                font-size: 0.75rem;
                color: var(--fm-muted);
            }

            .media-manager .media-actions {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 0.5rem;
                margin-top: 1rem;
            }

            .media-manager .media-loader {
                position: absolute;
                inset: 0;
                display: none;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
                border-radius: 1.5rem;
                background: rgba(255, 255, 255, 0.72);
                backdrop-filter: blur(4px);
                z-index: 5;
            }

            .media-manager .media-loader[style*="display: flex"] {
                display: flex !important;
            }

            .media-manager .spinner {
                width: 1.25rem;
                height: 1.25rem;
                border: 2px solid rgba(13, 110, 253, 0.2);
                border-top-color: #0d6efd;
                border-radius: 50%;
                animation: media-spin 0.8s linear infinite;
            }

            @keyframes media-spin {
                to {
                    transform: rotate(360deg);
                }
            }

            .media-manager .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.5rem 0.9rem;
                border-radius: 0.75rem;
                border: 1px solid transparent;
                font-size: 0.875rem;
                font-weight: 600;
                line-height: 1.2;
                text-decoration: none;
                cursor: pointer;
                transition: background-color 0.15s ease, border-color 0.15s ease, transform 0.15s ease;
            }

            .media-manager .btn:hover {
                transform: translateY(-1px);
            }

            .media-manager .btn-secondary {
                background: #f8f9fa;
                border-color: #ced4da;
                color: #212529;
            }

            .media-manager .btn-secondary:hover {
                background: #e9ecef;
            }

            .media-manager .btn-primary {
                background: #0d6efd;
                color: #fff;
            }

            .media-manager .btn-primary:hover {
                background: #0b5ed7;
            }

            .media-manager .btn-danger {
                background: #dc3545;
                color: #fff;
            }

            .media-manager .btn-danger:hover {
                background: #bb2d3b;
            }

            .media-manager .badge {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                padding: 0.35rem 0.65rem;
                font-size: 0.75rem;
                font-weight: 700;
            }

            .media-manager .badge-light {
                background: #f1f5f9;
                color: #475569;
            }

            .media-manager .badge-warning {
                background: #fff3cd;
                color: #8a5a00;
            }

            .media-manager .upload-field {
                width: 100%;
                padding: 1rem 1.1rem;
                border: 1px solid #ced4da;
                border-radius: 1rem;
                background: #f8f9fa;
            }

            .media-manager .empty-state {
                padding: 3rem 1.5rem;
                border: 1px dashed #dee2e6;
                border-radius: 1rem;
                background: #f8f9fa;
                color: #6c757d;
                text-align: center;
            }
        </style>

        <div class="card mb-1">
            <div class="card-body">
                <div class="media-toolbar">
                    <button type="button" class="btn btn-secondary" wire:click="goUp" wire:loading.attr="disabled"
                        wire:target="goUp,refreshBrowser,setPath" @disabled($this->currentPath === '')>
                        Up
                    </button>
                    <button type="button" class="btn btn-secondary" wire:click="refreshBrowser"
                        wire:loading.attr="disabled" wire:target="goUp,refreshBrowser,setPath">
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        @php
            $items = collect($this->directories)
                ->map(function (array $directory): array {
                    return [
                        'kind' => 'directory',
                        'name' => $directory['name'],
                        'path' => $directory['path'],
                    ];
                })
                ->merge(
                    collect($this->files)->map(function (array $file): array {
                        return [
                            'kind' => 'file',
                            'name' => $file['name'],
                            'path' => $file['path'],
                            'size' => $file['size'],
                            'modified' => $file['modified'],
                            'mime' => $file['mime'],
                            'previewable' => $file['previewable'],
                            'url' => $file['url'],
                        ];
                    }),
                )
                ->values();
        @endphp

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="card position-relative">
                <div class="media-loader" wire:loading.flex.delay wire:target="setPath,goUp,refreshBrowser">
                    <span class="spinner" aria-hidden="true"></span>
                    <span class="fw-semibold text-gray-700">Loading folder...</span>
                </div>

                <div class="card-body">
                    @if ($items->isEmpty())
                        <div class="empty-state">
                            Nothing here yet.
                        </div>
                    @else
                        <div class="media-grid">
                            @foreach ($items as $item)
                                @if ($item['kind'] === 'directory')
                                    <article class="media-item media-item--folder">
                                        <button type="button" class="media-trigger" wire:loading.attr="disabled"
                                            wire:target="setPath,goUp,refreshBrowser"
                                            wire:click="setPath('{{ $item['path'] }}')">
                                            <div class="media-icon media-icon--folder">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path
                                                        d="M2 5a2 2 0 012-2h4l2 2h6a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V5z" />
                                                </svg>
                                            </div>

                                            <div class="w-100">
                                                <div class="media-title">{{ $item['name'] }}</div>
                                                <div class="media-meta">Folder</div>
                                            </div>
                                        </button>

                                        <div class="media-actions">
                                            <button type="button" class="btn btn-danger"
                                                wire:click="deleteDirectory('{{ $item['path'] }}')"
                                                onclick="return confirm('Delete directory and all contents?')">
                                                Delete
                                            </button>
                                        </div>
                                    </article>
                                @else
                                    <article class="media-item">
                                        @if ($item['previewable'])
                                            <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer"
                                                class="media-trigger">
                                                <div class="media-thumb">
                                                    <img src="{{ $item['url'] }}" alt="{{ $item['name'] }}" />
                                                </div>

                                                <div class="w-100">
                                                    <div class="media-title">{{ $item['name'] }}</div>
                                                    <div class="media-meta">{{ $item['size'] }} ·
                                                        {{ $item['modified'] }}</div>
                                                </div>
                                            </a>
                                        @else
                                            <a href="{{ $item['url'] }}" download class="media-trigger">
                                                <div class="media-icon media-icon--file">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path
                                                            d="M8 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V7l-5-5H8z" />
                                                    </svg>
                                                </div>

                                                <div class="w-100">
                                                    <div class="media-title">{{ $item['name'] }}</div>
                                                    <div class="media-meta">{{ $item['size'] }} ·
                                                        {{ $item['modified'] }}</div>
                                                </div>
                                            </a>
                                        @endif

                                        <div class="media-actions">
                                            @if ($item['previewable'])
                                                <a class="btn btn-secondary" href="{{ $item['url'] }}" target="_blank"
                                                    rel="noopener noreferrer">
                                                    Open
                                                </a>
                                            @else
                                                <a class="btn btn-secondary" href="{{ $item['url'] }}" download>
                                                    Open
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-primary"
                                                wire:click="selectForReplace('{{ $item['path'] }}')">
                                                Replace
                                            </button>
                                            <button type="button" class="btn btn-danger"
                                                wire:click="deleteFile('{{ $item['path'] }}')"
                                                onclick="return confirm('Delete file?')">
                                                Delete
                                            </button>
                                        </div>
                                    </article>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="card position-relative">
                <div class="media-loader" wire:loading.flex.delay wire:target="upload,replaceUpload">
                    <span class="spinner" aria-hidden="true"></span>
                    <span class="fw-semibold text-gray-700">
                        <span wire:loading wire:target="upload">Uploading...</span>
                        <span wire:loading wire:target="replaceUpload">Replacing file...</span>
                    </span>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="badge badge-light">Upload</div>
                            <p class="mt-2 mb-0 text-sm text-gray-500">
                                @if ($this->replaceTarget)
                                    Replacing <span
                                        class="fw-semibold text-gray-900">{{ basename($this->replaceTarget) }}</span>.
                                @else
                                    Add a file to the current folder.
                                @endif
                            </p>
                        </div>

                        @if ($this->replaceTarget)
                            <span class="badge badge-warning">Replace mode</span>
                        @endif
                    </div>

                    <div class="mt-4">
                        <input type="file" wire:model="upload" class="upload-field">
                    </div>

                    <div class="mt-4 d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" wire:click="replaceUpload"
                            wire:loading.attr="disabled" wire:target="replaceUpload" @disabled(!$upload)>
                            {{ $this->replaceTarget ? 'Replace file' : 'Upload file' }}
                        </button>

                        @if ($this->replaceTarget)
                            <button type="button" class="btn btn-secondary" wire:click="cancelReplace">
                                Cancel replace
                            </button>
                        @endif
                    </div>

                    @error('upload')
                        <p class="mt-3 mb-0 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
