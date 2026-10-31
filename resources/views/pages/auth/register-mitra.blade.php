@php
    // Peta field -> langkah, supaya bila validasi server gagal, wizard langsung membuka langkah yang bermasalah
    $stepFields = [
        2 => ['nama_toko', 'alamat_lengkap', 'kota_operasional', 'whatsapp_toko', 'hari_operasional', 'jam_buka', 'jam_tutup'],
        3 => ['nama_pengelola', 'nik', 'jabatan_pengelola', 'foto_ktp'],
        4 => ['bank_name', 'nomor_rekening', 'nama_pemilik_rekening', 'terms'],
    ];
    $startStep = 1;
    if ($errors->any()) {
        foreach ($stepFields as $stepNo => $fields) {
            foreach ($fields as $f) {
                if ($errors->has($f)) { $startStep = $stepNo; break 2; }
            }
        }
    }
    $bankSelected = old('bank_name', 'BCA');
    $hariSelected = old('hari_operasional', 'Senin - Minggu (Setiap Hari)');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Menjadi Mitra Rental - MirrorCam</title>

    <!-- Google Font Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* =====================================================
           ATURAN STYLING MIRRORCAM
           Palet utama   : #ffffff, #f9f9f9, #000000
           Tombol        : bg #d9d9d9, teks #000000
           Status aktif  : bg #dcfce7, teks #16a34a
           Selesai       : bg #fff700, teks #000000
           Terlambat     : bg #ffdad6, teks #ba1a1a
           Tenggat/warn  : teks #B45309
           Font          : Montserrat | Radius : 4px
           ===================================================== */

        /* Semua border default memakai #d9d9d9 (warna palet tombol) */
        *, ::before, ::after { border-color: #d9d9d9; }

        body, input, textarea, select, button {
            font-family: 'Montserrat', sans-serif !important;
        }
        .custom-rounded { border-radius: 4px !important; }

        /* Tombol */
        .btn-default {
            background-color: #d9d9d9 !important;
            color: #000000 !important;
            border-radius: 4px !important;
        }
        .btn-default:hover { background-color: #c5c5c5 !important; }

        /* Status */
        .status-aktif {
            background-color: #dcfce7 !important;
            color: #16a34a !important;
            border-radius: 4px !important;
        }
        .status-selesai {
            background-color: #fff700 !important;
            color: #000000 !important;
            border-radius: 4px !important;
        }
        .status-terlambat {
            background-color: #ffdad6 !important;
            color: #ba1a1a !important;
            border-radius: 4px !important;
        }
        .text-warning-custom { color: #B45309 !important; }

        /* Teks pendukung = hitam transparan (turunan #000000) */
        .muted { color: rgba(0, 0, 0, .55); }
        .req { color: #ba1a1a; }
        .err-text { color: #ba1a1a; }

        /* Notifikasi */
        .alert-success {
            background-color: #dcfce7; color: #16a34a;
            border: 1px solid #16a34a; border-radius: 4px;
        }
        .alert-error {
            background-color: #ffdad6; color: #ba1a1a;
            border: 1px solid #ba1a1a; border-radius: 4px;
        }

        /* ---------- Stepper bulat + garis hubung ---------- */
        .step-item { display: flex; flex-direction: column; align-items: center; gap: 6px; min-width: 72px; }
        .step-item.is-clickable { cursor: pointer; }
        .step-dot {
            width: 26px; height: 26px; border-radius: 9999px;
            background: #d9d9d9; color: #000000;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
            transition: all .3s;
        }
        .step-dot.is-active { background: #000000; color: #ffffff; }
        .step-label {
            font-size: 10px; font-weight: 700; letter-spacing: .06em;
            text-transform: uppercase; color: rgba(0, 0, 0, .4);
            transition: color .3s;
        }
        .step-label.is-active { color: #000000; }
        .step-line {
            width: 44px; height: 2px; margin: 12px -23px 0;
            background: #d9d9d9; transition: background .3s;
        }
        .step-line.is-active { background: #000000; }

        /* ---------- Card & panel langkah ---------- */
        .card-main {
            background: #ffffff;
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            padding: 28px 32px;
        }
        .step-panel { display: none; }
        .step-panel.is-current { display: block; animation: stepIn .25s ease; }
        @keyframes stepIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: none; }
        }

        /* ---------- Komponen form ---------- */
        .field {
            width: 100%;
            height: 40px;
            padding: 0 14px;
            font-size: 13px;
            color: #000000;
            border: 1px solid #d9d9d9;
            background-color: #f9f9f9;
            border-radius: 4px;
            transition: all .15s;
        }
        textarea.field { height: 92px; padding: 10px 14px; resize: none; line-height: 1.5; }
        .field:focus { outline: none; background-color: #ffffff; border-color: #000000; }
        .field::placeholder { color: rgba(0, 0, 0, .4); }
        .field.is-invalid, .is-invalid > .field { border-color: #ba1a1a; }
        .dropzone.is-invalid { border-color: #ba1a1a; }

        .lbl-row {
            display: flex; align-items: center; justify-content: space-between;
            height: 20px; margin-bottom: 6px;
        }
        .lbl { font-size: 12.5px; font-weight: 600; color: #000000; }
        .hint { font-size: 11px; color: rgba(0, 0, 0, .55); margin-top: 5px; line-height: 1.4; }
        .js-err { font-size: 11px; margin-top: 4px; color: #ba1a1a; font-weight: 600; }

        .step-eyebrow { font-size: 10.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: rgba(0, 0, 0, .55); }
        .step-heading { font-size: 20px; font-weight: 800; color: #000000; letter-spacing: -.01em; margin-top: 4px; }
        .step-desc { font-size: 12.5px; color: rgba(0, 0, 0, .55); margin-top: 4px; }

        .info-box {
            background: #f9f9f9; border: 1px solid #d9d9d9; border-radius: 4px;
            padding: 14px 16px; font-size: 12px; line-height: 1.5; color: rgba(0, 0, 0, .65);
        }

        /* Pilihan bank: semua tombol #d9d9d9, yang terpilih diberi border hitam */
        .bank-btn {
            padding: 10px 0;
            font-size: 12.5px; font-weight: 700; text-align: center;
            background-color: #d9d9d9; color: #000000;
            border: 2px solid transparent; border-radius: 4px;
            transition: all .15s; cursor: pointer;
        }
        .bank-btn:hover { background-color: #c5c5c5; }
        .bank-btn.is-selected { border-color: #000000; }

        input[type="checkbox"] { accent-color: #000000; border-radius: 4px; }
    </style>
</head>
<body class="bg-[#f9f9f9] min-h-screen text-[#000000] antialiased flex flex-col">

    <!-- Form Logout Terpisah di Luar Form Utama -->
    @if(Route::has('logout'))
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    @endif

    <!-- NAVBAR ATAS -->
    <header class="w-full bg-[#ffffff] border-b px-6 sm:px-12 py-3 flex justify-between items-center shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-[#000000] text-[#ffffff] custom-rounded flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 stroke-current fill-none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                    <circle cx="12" cy="13" r="3"/>
                </svg>
            </div>
            <div>
                <h1 class="font-extrabold text-sm tracking-tight leading-none text-[#000000]">MIRRORCAM</h1>
                <p class="text-[9px] muted font-semibold tracking-wider uppercase mt-0.5">Multi-Tenant Rental</p>
            </div>
        </div>

        <a href="/" class="text-xs font-semibold text-[#000000] hover:underline flex items-center gap-1">
            Kembali ke Beranda &rarr;
        </a>
    </header>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 w-full px-4 sm:px-6 py-8 flex">
        <div class="w-full max-w-[860px] m-auto">

            <!-- Notifikasi Berhasil / Error -->
            @if (session('success'))
                <div class="mb-4 p-3 text-xs alert-success font-bold">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 text-xs alert-error">
                    <span class="font-bold">Gagal menyimpan data:</span>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Judul (tengah) + Stepper di bawahnya -->
            <div class="text-center space-y-2 pb-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000000] tracking-tight">Daftar Menjadi Mitra Rental MirrorCam</h1>
                <p class="text-xs muted max-w-2xl mx-auto">Kembangkan bisnis rental kamera dan peralatan sinematografi bersama ribuan penyewa terverifikasi di seluruh Indonesia.</p>

                <!-- Stepper: bulat + garis hubung -->
                <div class="flex items-start justify-center pt-3">
                    <div class="step-item" data-step="1">
                        <span id="step-1" class="step-dot is-active">1</span>
                        <span id="step-1-label" class="step-label is-active">Google</span>
                    </div>
                    <span id="line-1" class="step-line"></span>
                    <div class="step-item" data-step="2">
                        <span id="step-2" class="step-dot">2</span>
                        <span id="step-2-label" class="step-label">Diri</span>
                    </div>
                    <span id="line-2" class="step-line"></span>
                    <div class="step-item" data-step="3">
                        <span id="step-3" class="step-dot">3</span>
                        <span id="step-3-label" class="step-label">Legalitas</span>
                    </div>
                    <span id="line-3" class="step-line"></span>
                    <div class="step-item" data-step="4">
                        <span id="step-4" class="step-dot">4</span>
                        <span id="step-4-label" class="step-label">Payout</span>
                    </div>
                </div>
            </div>

            <!-- Form Utama: 1 form, 4 langkah -->
            <form id="mitra-form" action="{{ route('register.mitra.store') }}" method="POST" enctype="multipart/form-data" novalidate class="card-main">
                @csrf

                <!-- ================= LANGKAH 1 — Google ================= -->
                <div class="step-panel is-current" data-step="1">
                    <p class="step-eyebrow">Langkah 1 dari 4</p>
                    <h2 class="step-heading">Autentikasi Akun Mitra</h2>
                    <p class="step-desc">Akun Google berhasil terhubung. Lengkapi data toko Anda pada langkah berikutnya.</p>

                    <div class="mt-6 p-4 bg-[#f9f9f9] custom-rounded border flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#ffffff] custom-rounded border flex items-center justify-center font-bold text-base text-[#000000]">G</div>
                            <div>
                                <p class="step-eyebrow" style="font-size:9.5px;">Akun Google Terhubung</p>
                                <p class="font-bold text-sm text-[#000000] mt-0.5">{{ auth()->user()->email ?? 'rental.galery@gmail.com' }}</p>
                            </div>
                        </div>
                        <span class="status-aktif inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            Terverifikasi
                        </span>
                    </div>

                    <p class="hint" style="font-size:11.5px; margin-top:12px;">ⓘ Akun Google Anda dipakai sebagai email login utama dashboard pemilik tenant, akses API integrasi inventaris, dan penerimaan laporan mutasi penyewaan harian.</p>

                    <p class="step-eyebrow mt-6 mb-2">Yang perlu disiapkan</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="info-box"><span class="font-bold text-[#000000] block">Data toko</span>Nama, alamat, kota, WhatsApp, dan jam operasional.</div>
                        <div class="info-box"><span class="font-bold text-[#000000] block">Identitas pengelola</span>NIK dan foto fisik KTP yang jelas.</div>
                        <div class="info-box"><span class="font-bold text-[#000000] block">Rekening bank</span>Untuk pencairan dana hasil sewa.</div>
                    </div>
                </div>

                <!-- ================= LANGKAH 2 — Diri (Toko & Workshop) ================= -->
                <div class="step-panel" data-step="2">
                    <p class="step-eyebrow">Langkah 2 dari 4</p>
                    <h2 class="step-heading">Informasi Toko & Workshop</h2>
                    <p class="step-desc">Profil operasional unit penyewaan kamera.</p>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
                        <div data-field>
                            <div class="lbl-row"><label class="lbl">Nama Toko / Rental Resmi <span class="req">*</span></label></div>
                            <input type="text" name="nama_toko" value="{{ old('nama_toko') }}" required data-required data-msg="Nama toko wajib diisi" placeholder="misal: Jakarta Lens & Cine Gear" class="field">
                            @error('nama_toko') <span class="err-text text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div data-field>
                            <div class="lbl-row"><label class="lbl">Kota Operasional <span class="req">*</span></label></div>
                            <input type="text" name="kota_operasional" value="{{ old('kota_operasional') }}" required data-required data-msg="Kota operasional wajib diisi" placeholder="Jakarta Selatan (DKI Jakarta)" class="field">
                        </div>

                        <div class="sm:col-span-2" data-field>
                            <div class="lbl-row"><label class="lbl">Alamat Toko / Workshop Lengkap <span class="req">*</span></label></div>
                            <textarea name="alamat_lengkap" rows="3" required data-required data-msg="Alamat toko wajib diisi" placeholder="Alamat fisik titik temu / serah terima unit (Jalan, No. Gedung, RT/RW, Kelurahan, Kecamatan, Kota, Kode Pos)" class="field">{{ old('alamat_lengkap') }}</textarea>
                            <p class="hint">Dipakai kurir MirrorCam SafeDrop dan penyewa untuk self-pickup unit.</p>
                        </div>

                        <div data-field>
                            <div class="lbl-row"><label class="lbl">Nomor WhatsApp Resmi Toko <span class="req">*</span></label></div>
                            <div class="flex">
                                <span class="inline-flex items-center px-3.5 h-[40px] text-[#000000] bg-[#d9d9d9] border border-r-0 rounded-l-[4px] font-medium text-xs">+62</span>
                                <input type="text" name="whatsapp_toko" value="{{ old('whatsapp_toko') }}" required data-required data-msg="Nomor WhatsApp wajib diisi" placeholder="812-3456-7890" class="field" style="border-top-left-radius:0;border-bottom-left-radius:0;">
                            </div>
                            <p class="hint">Notifikasi booking instan & integrasi dispatch armada.</p>
                        </div>

                        <div data-field>
                            <div class="lbl-row"><label class="lbl">Hari Operasional Toko <span class="req">*</span></label></div>
                            <select name="hari_operasional" class="field">
                                <option value="Senin - Minggu (Setiap Hari)" {{ $hariSelected === 'Senin - Minggu (Setiap Hari)' ? 'selected' : '' }}>Senin - Minggu (Setiap Hari)</option>
                                <option value="Senin - Sabtu" {{ $hariSelected === 'Senin - Sabtu' ? 'selected' : '' }}>Senin - Sabtu</option>
                                <option value="Senin - Jumat" {{ $hariSelected === 'Senin - Jumat' ? 'selected' : '' }}>Senin - Jumat</option>
                            </select>
                        </div>

                        <div data-field>
                            <div class="lbl-row"><label class="lbl">Jam Buka Toko <span class="req">*</span></label></div>
                            <input type="time" name="jam_buka" id="jam_buka" value="{{ old('jam_buka', '08:00') }}" data-required data-msg="Jam buka wajib diisi" class="field">
                        </div>

                        <div data-field>
                            <div class="lbl-row">
                                <label class="lbl">Jam Tutup Toko <span class="req">*</span></label>
                                <label class="inline-flex items-center text-[11px] text-[#000000] gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="buka_24_jam" value="1" id="buka_24_jam" onchange="toggle24Jam(this)" class="text-[#000000] focus:ring-[#000000]"> Buka 24 Jam
                                </label>
                            </div>
                            <input type="time" name="jam_tutup" id="jam_tutup" value="{{ old('jam_tutup', '21:00') }}" data-required data-msg="Jam tutup wajib diisi" class="field">
                        </div>

                        <p class="hint sm:col-span-2" style="margin-top:-4px;">ⓘ Jadwal ini dipakai penyewa dan kurir MirrorCam SafeDrop untuk pickup & drop-off peralatan kamera.</p>
                    </div>
                </div>

                <!-- ================= LANGKAH 3 — Legalitas ================= -->
                <div class="step-panel" data-step="3">
                    <p class="step-eyebrow">Langkah 3 dari 4</p>
                    <h2 class="step-heading">Data Pengelola & Legalitas PIC</h2>
                    <p class="step-desc">Verifikasi identitas penanggung jawab operasional.</p>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <!-- Kiri: field teks -->
                        <div class="space-y-4">
                            <div data-field>
                                <div class="lbl-row"><label class="lbl">Nama Pengelola Sesuai KTP <span class="req">*</span></label></div>
                                <input type="text" name="nama_pengelola" value="{{ old('nama_pengelola') }}" required data-required data-msg="Nama pengelola wajib diisi" placeholder="Nama Lengkap Penanggung Jawab" class="field">
                            </div>

                            <div data-field>
                                <div class="lbl-row"><label class="lbl">Nomor Induk Kependudukan (NIK) <span class="req">*</span></label></div>
                                <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16" required data-required data-msg="NIK wajib diisi" placeholder="3171xxxxxxxxxxxx" class="field">
                            </div>

                            <div data-field>
                                <div class="lbl-row"><label class="lbl">Jabatan Pengelola Terdaftar <span class="req">*</span></label></div>
                                <input type="text" name="jabatan_pengelola" value="{{ old('jabatan_pengelola', 'Pemilik Usaha / Owner / Founder') }}" required data-required data-msg="Jabatan wajib diisi" placeholder="Pemilik Usaha / Owner / Founder" class="field">
                            </div>
                        </div>

                        <!-- Kanan: Upload Foto KTP (tinggi mengikuti kolom kiri) -->
                        <div class="flex flex-col" data-field>
                            <div class="lbl-row">
                                <label class="lbl">Upload Foto Fisik KTP Pengelola <span class="req">*</span></label>
                                <span class="text-[10.5px] muted">Maksimal 5MB</span>
                            </div>
                            <div class="dropzone flex-1 min-h-[190px] border-2 border-dashed custom-rounded px-4 py-4 text-center hover:bg-[#ffffff] transition cursor-pointer bg-[#f9f9f9] relative overflow-hidden flex items-center justify-center" id="ktp-dropzone">
                                <input type="file" name="foto_ktp" id="foto_ktp" accept="image/*" onchange="previewKTP(this)" data-required data-msg="Foto KTP wajib diunggah" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                                <div class="space-y-2 pointer-events-none relative z-0" id="ktp-placeholder">
                                    <div class="w-10 h-10 bg-[#d9d9d9] text-[#000000] custom-rounded flex items-center justify-center mx-auto text-lg">📤</div>
                                    <div class="text-xs font-semibold text-[#000000] leading-snug">
                                        Tarik & letakkan foto KTP di sini,<br>atau <span class="underline">Pilih Dokumen</span>
                                    </div>
                                    <p class="text-[10.5px] muted">JPG, PNG, atau WEBP</p>
                                </div>

                                <div class="hidden space-y-1.5 pointer-events-none relative z-0" id="ktp-preview-container">
                                    <img id="ktp-preview" class="max-h-32 mx-auto custom-rounded border">
                                    <p class="text-xs font-semibold break-all" style="color:#16a34a;" id="ktp-filename">Dokumen terpilih</p>
                                </div>
                            </div>
                            <p class="hint">🔒 Pastikan NIK 16-digit, pasfoto, dan tanda tangan terlihat jelas dan tidak buram.</p>
                        </div>
                    </div>
                </div>

                <!-- ================= LANGKAH 4 — Payout ================= -->
                <div class="step-panel" data-step="4">
                    <p class="step-eyebrow">Langkah 4 dari 4</p>
                    <h2 class="step-heading">Rekening Settlement & Payout</h2>
                    <p class="step-desc">Pencairan dana hasil sewa.</p>

                    <div class="mt-6 space-y-4">
                        <div>
                            <div class="lbl-row"><label class="lbl">Pilih Bank Settlement Mitra <span class="req">*</span></label></div>
                            <input type="hidden" name="bank_name" id="bank_name" value="{{ $bankSelected }}">
                            <div class="grid grid-cols-5 gap-2">
                                @foreach (['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI'] as $bank)
                                    <button type="button" onclick="selectBank('{{ $bank }}', this)" class="bank-btn {{ $bankSelected === $bank ? 'is-selected' : '' }}">{{ $bank }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
                            <div data-field>
                                <div class="lbl-row"><label class="lbl">Nomor Rekening Bank <span class="req">*</span></label></div>
                                <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening') }}" required data-required data-msg="Nomor rekening wajib diisi" placeholder="Contoh: 8820192839" class="field">
                            </div>
                            <div data-field>
                                <div class="lbl-row"><label class="lbl">Nama Lengkap Pemilik Rekening <span class="req">*</span></label></div>
                                <input type="text" name="nama_pemilik_rekening" value="{{ old('nama_pemilik_rekening') }}" required data-required data-msg="Nama pemilik rekening wajib diisi" placeholder="Harus sesuai KTP Mendaftar" class="field">
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="font-bold text-[#000000]">Mekanisme Otomatis Pencairan Dana (Payout)</span><br>
                            Dana sewa akan langsung ditransfer otomatis ke rekening ini setelah pengembalian unit selesai dan terverifikasi kedua belah pihak di sistem.
                        </div>

                        <div data-field>
                            <div class="flex items-start gap-3">
                                <input type="checkbox" name="terms" value="1" required data-required data-msg="Centang pernyataan ini untuk melanjutkan" id="terms" class="mt-0.5 text-[#000000] focus:ring-[#000000]">
                                <label for="terms" class="text-[11.5px] text-[#000000] leading-relaxed">
                                    Saya menyatakan bahwa seluruh data profil toko, kelengkapan identitas pengelola, dan rekening bank yang didaftarkan adalah akurat, sah, dan dapat dipertanggungjawabkan secara hukum. Dengan melanjutkan, saya menyetujui seluruh ketentuan untuk <a href="#" class="font-bold text-[#000000] underline">Perjanjian Kerja Sama Kemitraan PT. Mirror Cam Indonesia</a>.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= NAVIGASI LANGKAH ================= -->
                <div class="mt-7 pt-5 border-t flex items-center justify-between gap-4">
                    <button type="button" id="btn-back" class="text-xs font-semibold text-[#000000] hover:underline invisible">&larr; Kembali</button>

                    <div class="flex items-center gap-4">
                        <p id="help-text" class="text-[11px] muted hidden sm:block">🛡 Butuh bantuan verifikasi? Hubungi Hubungan Mitra Cam</p>

                        <button type="button" id="btn-next" class="btn-default px-8 py-3 font-bold text-sm transition cursor-pointer">
                            Lanjutkan &rarr;
                        </button>
                        <button type="submit" id="btn-submit" class="btn-default px-8 py-3 font-bold text-sm transition cursor-pointer hidden">
                            Kirim Pendaftaran Mitra &rarr;
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </main>

    <!-- Script JS -->
    <script>
        const TOTAL_STEPS = 4;
        const START_STEP = {{ (int) $startStep }};
        let currentStep = 1;

        function selectBank(bankName, element) {
            document.getElementById('bank_name').value = bankName;
            document.querySelectorAll('.bank-btn').forEach(btn => btn.classList.remove('is-selected'));
            element.classList.add('is-selected');
        }

        function previewKTP(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('ktp-preview').src = e.target.result;
                    document.getElementById('ktp-filename').textContent = '✅ ' + input.files[0].name;
                    document.getElementById('ktp-placeholder').classList.add('hidden');
                    document.getElementById('ktp-preview-container').classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function toggle24Jam(checkbox) {
            const jamBuka = document.getElementById('jam_buka');
            const jamTutup = document.getElementById('jam_tutup');
            if (checkbox.checked) {
                jamBuka.disabled = true;
                jamTutup.disabled = true;
                jamBuka.classList.add('opacity-50');
                jamTutup.classList.add('opacity-50');
            } else {
                jamBuka.disabled = false;
                jamTutup.disabled = false;
                jamBuka.classList.remove('opacity-50');
                jamTutup.classList.remove('opacity-50');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('mitra-form');
            const btnBack = document.getElementById('btn-back');
            const btnNext = document.getElementById('btn-next');
            const btnSubmit = document.getElementById('btn-submit');

            // ---------- Validasi per langkah ----------
            function setInvalid(el, invalid) {
                const wrap = el.closest('[data-field]');
                if (!wrap) return;
                const target = el.type === 'file' ? wrap.querySelector('.dropzone') : el;
                if (target) target.classList.toggle('is-invalid', invalid);
                let msg = wrap.querySelector('.js-err');
                if (invalid) {
                    if (!msg) {
                        msg = document.createElement('p');
                        msg.className = 'js-err';
                        wrap.appendChild(msg);
                    }
                    msg.textContent = el.dataset.msg || 'Wajib diisi';
                } else if (msg) {
                    msg.remove();
                }
            }

            function isEmpty(el) {
                if (el.disabled) return false;
                if (el.type === 'file') return !(el.files && el.files.length);
                if (el.type === 'checkbox') return !el.checked;
                return el.value.trim() === '';
            }

            function validateStep(n) {
                const panel = document.querySelector('.step-panel[data-step="' + n + '"]');
                let firstInvalid = null;
                panel.querySelectorAll('[data-required]').forEach(function(el) {
                    const invalid = isEmpty(el);
                    setInvalid(el, invalid);
                    if (invalid && !firstInvalid) firstInvalid = el;
                });
                if (firstInvalid && firstInvalid.type !== 'file') firstInvalid.focus();
                return !firstInvalid;
            }

            // Hapus tanda error begitu pengguna memperbaiki isian
            document.querySelectorAll('[data-required]').forEach(function(el) {
                ['input', 'change'].forEach(function(evt) {
                    el.addEventListener(evt, function() {
                        if (!isEmpty(el)) setInvalid(el, false);
                    });
                });
            });

            // ---------- Tampilan langkah + stepper ----------
            function showStep(n) {
                currentStep = n;

                document.querySelectorAll('.step-panel').forEach(function(p) {
                    p.classList.toggle('is-current', Number(p.dataset.step) === n);
                });

                for (let i = 1; i <= TOTAL_STEPS; i++) {
                    const dot = document.getElementById('step-' + i);
                    const label = document.getElementById('step-' + i + '-label');
                    const line = document.getElementById('line-' + (i - 1));
                    const item = document.querySelector('.step-item[data-step="' + i + '"]');
                    if (dot) dot.classList.toggle('is-active', i <= n);
                    if (label) label.classList.toggle('is-active', i <= n);
                    if (line) line.classList.toggle('is-active', i <= n);
                    if (item) item.classList.toggle('is-clickable', i < n);
                }

                btnBack.classList.toggle('invisible', n === 1);
                btnNext.classList.toggle('hidden', n === TOTAL_STEPS);
                btnSubmit.classList.toggle('hidden', n !== TOTAL_STEPS);
            }

            function goNext() {
                if (currentStep >= TOTAL_STEPS) return;
                if (!validateStep(currentStep)) return;
                showStep(currentStep + 1);
            }

            btnNext.addEventListener('click', goNext);
            btnBack.addEventListener('click', function() {
                if (currentStep > 1) showStep(currentStep - 1);
            });

            // Klik bulatan langkah yang sudah dilewati untuk kembali
            document.querySelectorAll('.step-item').forEach(function(item) {
                item.addEventListener('click', function() {
                    const n = Number(item.dataset.step);
                    if (n < currentStep) showStep(n);
                });
            });

            // Enter di kolom isian = Lanjutkan (bukan kirim form lebih awal)
            form.addEventListener('keydown', function(e) {
                if (e.key !== 'Enter') return;
                const tag = e.target.tagName;
                if (tag === 'TEXTAREA' || tag === 'BUTTON') return;
                if (currentStep < TOTAL_STEPS) {
                    e.preventDefault();
                    goNext();
                }
            });

            // Pengaman saat submit
            form.addEventListener('submit', function(e) {
                if (currentStep < TOTAL_STEPS) {
                    e.preventDefault();
                    goNext();
                    return;
                }
                if (!validateStep(TOTAL_STEPS)) e.preventDefault();
            });

            // Buka langkah awal (langkah yang bermasalah bila validasi server gagal)
            showStep(START_STEP);
        });
    </script>
</body>
</html>
