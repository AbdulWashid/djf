@if ($paginator->hasPages())
    <nav aria-label="Page navigation" class="d-flex flex-column align-items-center gap-3">
        <div class="text-center text-stroke-40 small">
            Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
        </div>

        <ul class="pagination flex-wrap justify-content-center gap-2 mb-0">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span class="page-link rounded-pill px-3">Previous</span>
                @else
                    <button type="button" class="page-link rounded-pill px-3" wire:click="previousPage" rel="prev">
                        Previous
                    </button>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled">
                        <span class="page-link rounded-pill px-3">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                            @if ($page == $paginator->currentPage())
                                <span class="page-link rounded-pill px-3">{{ $page }}</span>
                            @else
                                <button type="button" class="page-link rounded-pill px-3"
                                    wire:click="gotoPage({{ $page }})">
                                    {{ $page }}
                                </button>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
                @if ($paginator->hasMorePages())
                    <button type="button" class="page-link rounded-pill px-3" wire:click="nextPage" rel="next">
                        Next
                    </button>
                @else
                    <span class="page-link rounded-pill px-3">Next</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
