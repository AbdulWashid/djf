@if ($paginator->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination">
            {{-- Loop for Numbers 1 to 10 --}}
            @foreach (range(1, min(10, $paginator->lastPage())) as $page)
                <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                    @if ($page == $paginator->currentPage())
                        <span class="page-link">{{ $page }}</span>
                    @else
                        <button type="button" class="page-link" wire:click="gotoPage({{ $page }})">
                            {{ $page }}
                        </button>
                    @endif
                </li>
            @endforeach

            {{-- Next Button --}}
            <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
                @if ($paginator->hasMorePages())
                    <button type="button" class="page-link" wire:click="nextPage" rel="next">
                        Next &raquo;
                    </button>
                @else
                    <span class="page-link">Next &raquo;</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
