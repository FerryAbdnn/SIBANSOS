@extends(
    auth()->user()->peran === 'admin'
    ? 'layouts.admin'
    : 'layouts.masyarakat'
)

@section('title', 'Profil')

@section('heading', 'Profil Pengguna')

@section('content')

    <div class="panel">

        <h3>Informasi akun</h3>

        <div class="detail-grid">

            <div>
                <small>Nama</small>
                <b>{{ auth()->user()->nama }}</b>
            </div>

            <div>
                <small>Email</small>
                <b>{{ auth()->user()->email }}</b>
            </div>

            <div>
                <small>Peran</small>
                <b>{{ ucfirst(auth()->user()->peran) }}</b>
            </div>

            <div>
                <small>Status akun</small>
                <b>
                    {{ auth()->user()->aktif ? 'Aktif' : 'Nonaktif' }}
                </b>
            </div>

        </div>

    </div>

@endsection