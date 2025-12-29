@if ($paginator->hasPages())
    <nav aria-label="Page navigation" class="d-flex align-items-center gap-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <button type="button" class="btn btn-pagination btn-pagination-disabled" disabled aria-disabled="true">
                <i class="bi bi-chevron-left"></i>
            </button>
        @else
            <button type="button" class="btn btn-pagination" wire:click="gotoPage({{ $paginator->currentPage() - 1 }})" wire:loading.attr="disabled">
                <i class="bi bi-chevron-left"></i>
            </button>
        @endif

        {{-- Page Numbers --}}
        <div class="d-flex gap-1">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-muted small">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button type="button" class="btn btn-pagination btn-pagination-active" disabled>
                                {{ $page }}
                            </button>
                        @else
                            <button type="button" class="btn btn-pagination" wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <button type="button" class="btn btn-pagination" wire:click="gotoPage({{ $paginator->currentPage() + 1 }})" wire:loading.attr="disabled">
                <i class="bi bi-chevron-right"></i>
            </button>
        @else
            <button type="button" class="btn btn-pagination btn-pagination-disabled" disabled aria-disabled="true">
                <i class="bi bi-chevron-right"></i>
            </button>
        @endif
    </nav>
@endif
