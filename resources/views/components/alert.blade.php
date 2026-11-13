@if(session('success'))
    <div class="alert alert-success" role="alert">
        <span>✓</span>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" role="alert">
        <span>!</span>
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Periksa kembali data yang kamu masukkan:</strong>

        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif