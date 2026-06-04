@if (is_array($node))
    <ul class="list-unstyled mb-0 tree-list">
        @foreach ($node as $name => $child)
            <li class="tree-item card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="d-flex align-items-start gap-3 min-w-0">
                            @if (is_array($child) && array_key_exists('__files', $child))
                                <div class="tree-icon tree-icon--folder">F</div>
                                <div class="min-w-0">
                                    <div class="tree-title text-truncate">{{ $name }}</div>
                                    <div class="tree-meta">Folder</div>
                                </div>
                            @else
                                <div class="tree-icon tree-icon--file">F</div>
                                <div class="min-w-0">
                                    <div class="tree-title text-truncate">{{ $name }}</div>
                                    <div class="tree-meta">File</div>
                                </div>
                            @endif
                        </div>

                        <div class="flex-shrink-0">
                            @if (is_array($child))
                                <button type="button" class="btn btn-danger btn-sm"
                                    wire:click="deleteDirectory('{{ $child['__path'] ?? '' }}')"
                                    onclick="return confirm('Delete directory and all contents?')">
                                    Delete
                                </button>
                            @endif
                        </div>
                    </div>

                    @if (is_array($child) && array_key_exists('__files', $child))
                        <div class="mt-3 d-grid gap-2">
                            @foreach ($child['__files'] as $file)
                                <div class="tree-file card">
                                    <div class="card-body py-2 px-3">
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div class="d-flex align-items-center gap-3 min-w-0">
                                                <div class="tree-icon tree-icon--file tree-icon--small">F</div>
                                                @if ($file['previewable'])
                                                    <a href="{{ $file['url'] }}" target="_blank"
                                                        rel="nofollow noopener noreferrer"
                                                        class="tree-file-name text-truncate">
                                                        {{ $file['name'] }}
                                                    </a>
                                                @else
                                                    <a href="{{ $file['url'] }}" download
                                                        class="tree-file-name text-truncate">
                                                        {{ $file['name'] }}
                                                    </a>
                                                @endif
                                                <span class="tree-meta text-nowrap">{{ $file['size'] }} ·
                                                    {{ $file['modified'] }}</span>
                                            </div>

                                            <button type="button" class="btn btn-danger btn-sm"
                                                wire:click="deleteFile('{{ $file['path'] }}')"
                                                onclick="return confirm('Delete file?')">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (is_array($child))
                        <div class="tree-children mt-3">
                            @foreach ($child as $k => $v)
                                @if ($k === '__files' || $k === '__path')
                                    @continue
                                @endif

                                @include('filament.pages._media_tree', ['node' => [$k => $v]])
                            @endforeach
                        </div>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
@endif
