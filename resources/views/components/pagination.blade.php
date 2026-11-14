@props(['paginator'])

@if ($paginator->hasPages())
    <div class="pager">
        <div class="pager-info">
            Menampilkan
            <b>{{ $paginator->firstItem() ?? 0 }}</b>&ndash;<b>{{ $paginator->lastItem() ?? 0 }}</b>
            dari <b>{{ number_format($paginator->total(), 0, ',', '.') }}</b> data
        </div>

        <div class="pager-links">
            @if ($paginator->onFirstPage())
                <span class="disabled">&lsaquo;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a>
            @endif

            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a>
            @else
                <span class="disabled">&rsaquo;</span>
            @endif
        </div>
    </div>
@endif
