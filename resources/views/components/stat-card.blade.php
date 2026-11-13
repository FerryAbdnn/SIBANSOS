@props([
    'title' => 'Statistik',
    'value' => 0,
    'description' => null,
    'icon' => '▦',
])

<div {{ $attributes->merge(['class' => 'stat']) }}>
    <div class="stat-card-top">
        <span class="stat-card-title">{{ $title }}</span>
        <span class="stat-card-icon" aria-hidden="true">{{ $icon }}</span>
    </div>

    <strong>{{ $value }}</strong>

    @if ($description)
        <small>{{ $description }}</small>
    @endif
</div>