@props(['paginator'])

@if ($paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'pagination-wrapper']) }}>
        <div class="pagination-info">
            Menampilkan
            <strong>{{ $paginator->firstItem() ?? 0 }}</strong>
            sampai
            <strong>{{ $paginator->lastItem() ?? 0 }}</strong>
            dari
            <strong>{{ $paginator->total() }}</strong>
            data
        </div>

        <div class="pagination">
            {{-- Tombol sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-disabled">‹ Sebelumnya</span>
            @else
                <a href="{{ $paginator->appends(request()->query())->previousPageUrl() }}">
                    ‹ Sebelumnya
                </a>
            @endif

            {{-- Nomor halaman --}}
            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="pagination-active">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->appends(request()->query())->url($page) }}">
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- Tombol selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->appends(request()->query())->nextPageUrl() }}">
                    Selanjutnya ›
                </a>
            @else
                <span class="pagination-disabled">Selanjutnya ›</span>
            @endif
        </div>
    </div>
@endif