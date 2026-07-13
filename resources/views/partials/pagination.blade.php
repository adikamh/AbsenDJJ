@if ($paginator->hasPages())
    <div class="table-pagination">
        <div class="pagination-info">
            Menampilkan <span>{{ $paginator->firstItem() }}</span> sampai <span>{{ $paginator->lastItem() }}</span> dari <span>{{ $paginator->total() }}</span> entri
        </div>
        <div class="pagination-controls">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <button class="pagination-btn" disabled>&laquo;</button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">&laquo;</a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <button class="pagination-btn" disabled>{{ $element }}</button>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button class="pagination-btn is-active">{{ $page }}</button>
                        @else
                            <a href="{{ $url }}" class="pagination-btn" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">&raquo;</a>
            @else
                <button class="pagination-btn" disabled>&raquo;</button>
            @endif
        </div>
    </div>
@endif
