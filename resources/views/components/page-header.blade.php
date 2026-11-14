@props([
    'title' => 'Halaman',
    'description' => null,
    'eyebrow' => null,
])

<div {{ $attributes->merge(['class' => 'page-head']) }}>
    <div class="page-head-text">
        @if ($eyebrow)
            <span class="eyebrow">{{ $eyebrow }}</span>
        @endif

        <h1>{{ $title }}</h1>

        @if ($description)
            <p>{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="page-head-actions">
            {{ $actions }}
        </div>
    @endisset
</div>
