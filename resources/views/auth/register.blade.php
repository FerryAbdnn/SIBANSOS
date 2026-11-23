<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Warga - SIBANSOS</title>
    <link rel="stylesheet" href="{{ asset('css/auth-register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/brand-logo.css') }}">
</head>
<body class="reg-page">

<div class="reg-layout">

    <main class="reg-main">
        <div class="reg-form-container">

            @include('components.brand-logo', [
                'href' => route('login'),
                'class' => 'reg-brand'
            ])

            <div class="reg-heading">
                <span class="reg-eyebrow">PENDAFTARAN WARGA</span>
                <h1>Buat akun warga</h1>
                <p>
                    Daftarkan identitas Anda untuk mengakses layanan bantuan sosial
                    secara lebih mudah, aman, dan transparan.
                </p>
            </div>

            @if ($errors->any())
                <div class="reg-alert" role="alert">
                    <strong>Periksa kembali data Anda.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="reg-form">
                @csrf

                <section class="reg-section">
                    <div class="reg-section-heading">
                        <span class="reg-section-number">01</span>
                        <div>
                            <h2>Identitas diri</h2>
                            <p>Masukkan data sesuai dokumen kependudukan.</p>
                        </div>
                    </div>

                    <div class="reg-grid reg-grid-2">
                        <div class="reg-field">
                            <label for="nik">NIK <span>*</span></label>
                            <input
                                type="text"
                                id="nik"
                                name="nik"
                                value="{{ old('nik') }}"
                                placeholder="16 digit NIK"
                                inputmode="numeric"
                                maxlength="16"
                                pattern="[0-9]{16}"
                                autocomplete="off"
                                required
                            >
                            <small>Nomor Induk Kependudukan pada KTP.</small>
                        </div>

                        <div class="reg-field">
                            <label for="no_kk">Nomor Kartu Keluarga <span>*</span></label>
                            <input
                                type="text"
                                id="no_kk"
                                name="no_kk"
                                value="{{ old('no_kk') }}"
                                placeholder="16 digit nomor KK"
                                inputmode="numeric"
                                maxlength="16"
                                pattern="[0-9]{16}"
                                autocomplete="off"
                                required
                            >
                        </div>
                    </div>

                    <div class="reg-field">
                        <label for="nama">Nama lengkap <span>*</span></label>
                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            value="{{ old('nama') }}"
                            placeholder="Nama sesuai KTP"
                            maxlength="100"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="reg-grid reg-grid-2">
                        <div class="reg-field">
                            <label for="tanggal_lahir">Tanggal lahir <span>*</span></label>
                            <input
                                type="date"
                                id="tanggal_lahir"
                                name="tanggal_lahir"
                                value="{{ old('tanggal_lahir') }}"
                                max="{{ now()->subDay()->format('Y-m-d') }}"
                                required
                            >
                        </div>

                        <div class="reg-field">
                            <label for="jenis_kelamin">Jenis kelamin <span>*</span></label>
                            <select id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="">Pilih jenis kelamin</option>
                                <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
                                <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="reg-section">
                    <div class="reg-section-heading">
                        <span class="reg-section-number">02</span>
                        <div>
                            <h2>Alamat tempat tinggal</h2>
                            <p>Pilih wilayah sesuai alamat domisili Anda.</p>
                        </div>
                    </div>

                    <div class="reg-field">
                        <label for="alamat">Alamat lengkap <span>*</span></label>
                        <textarea
                            id="alamat"
                            name="alamat"
                            rows="3"
                            placeholder="Nama jalan, nomor rumah, RT/RW, atau keterangan alamat lainnya"
                            required
                        >{{ old('alamat') }}</textarea>
                    </div>

                    <div class="reg-field">
                        <label for="id_desa">Desa / Kelurahan <span>*</span></label>
                        <select id="id_desa" name="id_desa" required>
                            <option value="">Pilih desa / kelurahan</option>
                            @foreach ($desa as $item)
                                <option
                                    value="{{ $item->id_wilayah }}"
                                    @selected((string) old('id_desa') === (string) $item->id_wilayah)
                                >
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="reg-grid reg-grid-2">
                        <div class="reg-field">
                            <label for="id_rw">RW <span>*</span></label>
                            <select id="id_rw" name="id_rw" required disabled>
                                <option value="">Pilih RW</option>
                            </select>
                        </div>

                        <div class="reg-field">
                            <label for="id_wilayah">RT <span>*</span></label>
                            <select id="id_wilayah" name="id_wilayah" required disabled>
                                <option value="">Pilih RT</option>
                            </select>
                        </div>
                    </div>
                    <p class="reg-field-note">
                        Pilihan RW dan RT akan muncul setelah wilayah sebelumnya dipilih.
                    </p>
                </section>

                <section class="reg-section">
                    <div class="reg-section-heading">
                        <span class="reg-section-number">03</span>
                        <div>
                            <h2>Informasi akun</h2>
                            <p>Gunakan nomor dan email yang masih aktif.</p>
                        </div>
                    </div>

                    <div class="reg-grid reg-grid-2">
                        <div class="reg-field">
                            <label for="nomor_whatsapp">Nomor WhatsApp <span>*</span></label>
                            <input
                                type="tel"
                                id="nomor_whatsapp"
                                name="nomor_whatsapp"
                                value="{{ old('nomor_whatsapp') }}"
                                placeholder="08xxxxxxxxxx"
                                maxlength="20"
                                autocomplete="tel"
                                required
                            >
                        </div>

                        <div class="reg-field">
                            <label for="email">Alamat email <span>*</span></label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                maxlength="100"
                                autocomplete="email"
                                required
                            >
                        </div>
                    </div>

                    <div class="reg-grid reg-grid-2">
                        <div class="reg-field">
                            <label for="password">Kata sandi <span>*</span></label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Minimal 8 karakter"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div class="reg-field">
                            <label for="password_confirmation">Ulangi kata sandi <span>*</span></label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Masukkan ulang kata sandi"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >
                        </div>
                    </div>
                </section>

                <label class="reg-consent">
                    <input
                        type="checkbox"
                        name="persetujuan_data"
                        value="1"
                        @checked(old('persetujuan_data'))
                        required
                    >
                    <span>
                        Saya menyetujui penggunaan dan pemrosesan data pribadi
                        untuk keperluan layanan bantuan sosial sesuai
                        <strong>UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi.</strong>
                    </span>
                </label>

                <button type="submit" class="reg-submit">
                    Buat akun warga
                    <span aria-hidden="true">→</span>
                </button>

                <p class="reg-login-link">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}">Masuk di sini</a>
                </p>
            </form>

            <p class="reg-footer">
                SIBANSOS · Sistem Informasi Bantuan Sosial
            </p>
        </div>
    </main>

    <aside class="reg-aside">
        <div class="reg-aside-content">
            <span class="reg-aside-label">SETELAH AKUN AKTIF</span>

            <h2>Tiga hal yang bisa Anda lakukan</h2>

            <p class="reg-aside-intro">
                Akses layanan bantuan sosial desa dalam satu tempat.
            </p>

            <div class="reg-benefits">
                <div class="reg-benefit">
                    <span class="reg-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor"
                                  stroke-width="1.8" stroke-linecap="round"/>
                            <rect x="3" y="3" width="18" height="18" rx="5"
                                  stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    </span>
                    <div>
                        <h3>Ajukan bantuan mandiri</h3>
                        <p>Lengkapi data diri dan dokumen persyaratan untuk mengajukan bantuan.</p>
                    </div>
                </div>

                <div class="reg-benefit">
                    <span class="reg-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" stroke="currentColor"
                                    stroke-width="1.6"/>
                            <path d="M12 7v5l3 2" stroke="currentColor"
                                  stroke-width="1.8" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div>
                        <h3>Pantau status pengajuan</h3>
                        <p>Ketahui perkembangan pengajuan mulai dari diajukan hingga keputusan verifikasi.</p>
                    </div>
                </div>

                <div class="reg-benefit">
                    <span class="reg-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21V5.5Z"
                                  stroke="currentColor" stroke-width="1.6"
                                  stroke-linejoin="round"/>
                            <path d="M4 17.5A2.5 2.5 0 0 1 6.5 15H20"
                                  stroke="currentColor" stroke-width="1.6"/>
                            <path d="M8 7h8M8 10h6" stroke="currentColor"
                                  stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <div>
                        <h3>Lihat riwayat bantuan</h3>
                        <p>Periksa informasi program dan riwayat bantuan yang pernah diterima.</p>
                    </div>
                </div>
            </div>

            <div class="reg-aside-note">
                <strong>Perlindungan data Anda</strong>
                <p>
                    NIK digunakan sebagai identitas unik dalam sistem.
                    Satu NIK hanya dapat digunakan untuk satu akun warga.
                    Jaga kerahasiaan kata sandi Anda.
                </p>
            </div>
        </div>

        <div class="reg-aside-bottom">
            SIBANSOS <span>·</span> Pelayanan sosial yang lebih transparan
        </div>
    </aside>

</div>

<script>
    const wilayahData = @json($wilayahData);

    const desaSelect = document.getElementById('id_desa');
    const rwSelect = document.getElementById('id_rw');
    const rtSelect = document.getElementById('id_wilayah');

    const oldDesa = @json(old('id_desa'));
    const oldRw = @json(old('id_rw'));
    const oldRt = @json(old('id_wilayah'));

    function isiPilihan(select, placeholder, data, selectedValue = '') {
        select.innerHTML = '';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = placeholder;
        select.appendChild(defaultOption);

        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id_wilayah;
            option.textContent = item.nama;

            if (String(item.id_wilayah) === String(selectedValue)) {
                option.selected = true;
            }

            select.appendChild(option);
        });

        select.disabled = data.length === 0;
    }

    function muatRW(idDesa, selectedRW = '') {
        const daftarRW = wilayahData.filter(item =>
            item.tipe === 'rw' &&
            String(item.id_induk) === String(idDesa)
        );

        isiPilihan(rwSelect, 'Pilih RW', daftarRW, selectedRW);
        muatRT(rwSelect.value, '');
    }

    function muatRT(idRW, selectedRT = '') {
        const daftarRT = wilayahData.filter(item =>
            item.tipe === 'rt' &&
            String(item.id_induk) === String(idRW)
        );

        isiPilihan(rtSelect, 'Pilih RT', daftarRT, selectedRT);
    }

    desaSelect.addEventListener('change', function () {
        muatRW(this.value);
    });

    rwSelect.addEventListener('change', function () {
        muatRT(this.value);
    });

    if (oldDesa) {
        muatRW(oldDesa, oldRw);
        if (oldRw) {
            muatRT(oldRw, oldRt);
        }
    }
</script>

</body>
</html>
