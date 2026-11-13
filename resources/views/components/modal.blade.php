@props([
    'id' => 'confirmation-modal',
    'title' => 'Konfirmasi',
    'size' => 'medium',
])

<dialog id="{{ $id }}" {{ $attributes->merge([
    'class' => 'app-modal modal-' . $size
]) }}>
    <div class="modal-header">
        <h3>{{ $title }}</h3>

        <button type="button" class="modal-close" aria-label="Tutup"
            onclick="document.getElementById('{{ $id }}').close()">
            &times;
        </button>
    </div>

    <div class="modal-body">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="modal-footer">
            {{ $footer }}
        </div>
    @endisset
</dialog>