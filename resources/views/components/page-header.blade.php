@props([
    'title' => 'Halaman',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div class="page-header-text">
        <h1>{{ $title }}</h1>

        @if ($description)
            <p>{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
    <div class="page-header-actions">
        {{ $actions }}
    </div>
    @endif
</div>