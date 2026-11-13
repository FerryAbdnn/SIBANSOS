@extends('layouts.masyarakat')
@section('title', 'Dashboard Masyarakat')
@section('heading', 'Dashboard Masyarakat')
@section('content')
    <div class="welcome">
        <div><span class="eyebrow light">PORTAL MASYARAKAT</span>
            <h1>Halo, {{ auth()->user()->nama }} 👋</h1>
            <p>Pantau pendataan dan pengajuan bantuan sosial Anda di sini.</p>
        </div><a class="btn white" href="{{ route('masyarakat.pengajuan.create') }}">＋ Ajukan bantuan</a>
    </div>
    <div class="panel">
        <h3>Status pendataan Anda</h3>@if($p)
            <p>Nama: <b>{{ $p->nama }}</b></p>
            <p>Status: <span class="status {{ $p->status }}">{{ ucfirst($p->status) }}</span></p>
        <p>{{ $p->catatan_verifikasi }}</p><a href="{{ route('masyarakat.status') }}">Lihat detail status →</a>@else<p>Anda
            belum memiliki pengajuan bantuan.</p><a class="btn primary"
        href="{{ route('masyarakat.pengajuan.create') }}">Mulai pengajuan</a>@endif
</div>@endsection