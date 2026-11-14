@props([
    'title' => 'Statistik',
    'value' => 0,
    'description' => null,
    'icon' => 'bar-chart',
    'tone' => 'green',
])

<div {{ $attributes->merge(['class' => 'kpi']) }}>
    <div class="kpi-top">
        <span class="kpi-label">{{ $title }}</span>

        <span class="kpi-icon {{ $tone }}">
            <x-icon :name="$icon" :size="16" />
        </span>
    </div>

    <strong class="kpi-value">{{ $value }}</strong>

    @if ($description)
        <small class="kpi-caption">{{ $description }}</small>
    @endif
</div>
