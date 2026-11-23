
@php
    $brandHref = $href ?? url('/');
    $brandSubtitle = $subtitle ?? 'Sistem Pendataan Bantuan Sosial';
    $brandClass = $class ?? '';
@endphp

<a href="{{ $brandHref }}" class="brand-logo {{ $brandClass }}">
    <span class="brand-logo__mark" aria-hidden="true">
        <svg
            width="27"
            height="27"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M12 2.5L20 5.5V11.2C20 16.3 16.6 20 12 21.5C7.4 20 4 16.3 4 11.2V5.5L12 2.5Z"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linejoin="round"
            />
            <path
                d="M8.5 11.8L10.8 14.1L15.8 9.1"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
        </svg>
    </span>

    <span class="brand-logo__text">
        <strong>SIBANSOS</strong>
        <small>{{ $brandSubtitle }}</small>
    </span>
</a>
