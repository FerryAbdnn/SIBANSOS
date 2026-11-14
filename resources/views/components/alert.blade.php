{{-- Notifikasi global --}}

@if (session('success'))
    <div class="alert alert-success" role="status">
        <x-icon name="check" :size="16" />
        <div>{{ session('success') }}</div>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning" role="alert">
        <x-icon name="alert" :size="16" />
        <div>{{ session('warning') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger" role="alert">
        <x-icon name="alert" :size="16" />
        <div>{{ session('error') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <x-icon name="alert" :size="16" />

        <div>
            <strong>Periksa kembali data yang Anda masukkan.</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
