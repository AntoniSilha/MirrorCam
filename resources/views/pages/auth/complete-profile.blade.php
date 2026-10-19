<x-layouts::auth>
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm max-w-5xl w-full border border-gray-100">
        <div class="mb-6">
            <h1 class="text-2xl font-bold mb-1 text-gray-900">Lengkapi Identitas Penyewa</h1>
            <p class="text-gray-500 text-sm">Akun Google berhasil terhubung. Lengkapi data diri Anda untuk kemudahan sewa
                kamera dan keamanan transaksi di Mirror Cam.</p>
        </div>

        <form action="{{ route('simpan.profile') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">
                <div class="flex flex-col">
                    <!-- Notifikasi Akun Google -->
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded mb-5 border border-gray-200">
                        <div class="flex items-center">
                            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google"
                                class="h-5 w-5 mr-3">
                            <div>
                                <p class="text-[10px] tracking-wider text-gray-500 font-bold uppercase mb-0.5">AKUN
                                    GOOGLE TERHUBUNG</p>
                                <p class="font-semibold text-sm text-gray-900">
                                    {{ session('google_email', 'dimas.anggaraksa@gmail.com') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center text-green-600">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs font-semibold">Terverifikasi</span>
                        </div>
                    </div>

                    <!-- Input Nama Lengkap -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1.5 text-gray-800">Nama Lengkap (Sesuai KTP) <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                            </div>
                            <input type="text" name="nama_lengkap_ktp"
                                class="w-full border border-gray-300 rounded pl-10 p-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-black @error('nama_lengkap_ktp') border-red-500 @enderror"
                                value="{{ old('nama_lengkap_ktp', session('google_name')) }}"
                                placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>

                    <!-- Input No HP -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1.5 text-gray-800">Nomor Handphone / WhatsApp <span
                                class="text-red-500">*</span></label>
                        <div class="flex">
                            <span
                                class="inline-flex items-center px-3 border border-r-0 border-gray-300 rounded-l bg-gray-50 text-gray-700 text-sm font-semibold">+62</span>
                            <input type="text" name="no_whatsapp" maxlength="13"
                                class="w-full border border-gray-300 rounded-r p-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-black @error('no_whatsapp') border-red-500 @enderror"
                                value="{{ old('no_whatsapp') }}" placeholder="812-3456-7890" required>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Untuk koordinasi serah terima & OTP.
                        </p>
                    </div>

                    <!-- Input NIK -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-gray-800">Nomor Induk Kependudukan (NIK)
                            <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                                </svg>
                            </div>
                            <input type="text" name="nik_ktp" maxlength="16"
                                class="w-full border border-gray-300 rounded pl-10 p-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-black @error('nik_ktp') border-red-500 @enderror"
                                value="{{ old('nik_ktp') }}" placeholder="3174xxxxxxxxxxxx" required>
                        </div>
                    </div>

                </div>

                <!-- Dokumen & Persetujuan -->
                <div class="flex flex-col h-full">
                    <!-- Upload Foto Fisik KTP Asli -->
                    <div class="mb-3">
                        <div class="flex justify-between items-end mb-1.5">
                            <label class="block text-sm font-semibold text-gray-800">Upload Foto Fisik KTP Asli <span
                                    class="text-red-500">*</span></label>
                            <span class="text-[11px] font-semibold text-gray-500">Maksimal 5 MB</span>
                        </div>

                        <!-- Area Dropzone & Preview -->
                        <label
                            class="flex flex-col items-center justify-center w-full h-28 border-2 border-gray-200 border-dashed rounded cursor-pointer bg-white hover:bg-gray-50 transition relative overflow-hidden">

                            <!-- State 1: Default (Sebelum Upload) -->
                            <div id="dropzone-default" class="flex flex-col items-center justify-center pt-4 pb-4">
                                <div class="bg-gray-100 p-2 rounded mb-2">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z">
                                        </path>
                                    </svg>
                                </div>
                                <p class="mb-0.5 text-xs text-gray-700 font-medium"><span
                                        class="font-semibold underline">Klik untuk Telusuri</span> file</p>
                                <p class="text-[10px] text-gray-500">JPG, PNG, atau WEBP</p>
                            </div>

                            <!-- State 2: Preview (Setelah File Dipilih) -->
                            <div id="dropzone-preview"
                                class="hidden flex items-center justify-between w-full h-full p-3 bg-gray-50 border border-green-200">
                                <img id="ktp-preview-img"
                                    class="h-full w-24 object-cover rounded border border-gray-200" src="#"
                                    alt="Preview KTP" />
                                <div class="flex flex-col text-left ml-3 flex-1 overflow-hidden">
                                    <span
                                        class="text-[10px] font-bold text-green-600 uppercase tracking-wider flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        File Berhasil Dipilih
                                    </span>
                                    <p id="ktp-file-name" class="text-xs font-semibold text-gray-800 truncate mt-0.5">
                                    </p>
                                    <p id="ktp-file-size" class="text-[10px] text-gray-500 mt-0.5"></p>
                                </div>
                                <span
                                    class="text-xs text-black font-bold underline hover:text-gray-600 ml-2">Ganti</span>
                            </div>

                            <input type="file" name="foto_ktp" id="foto_ktp_input" class="hidden"
                                accept="image/*" required onchange="previewKTPImage(event)" />
                        </label>
                    </div>

                    <!-- Panduan Pengambilan -->
                    <div class=" bg-gray-50 p-2.5 rounded border border-gray-200 flex items-start">
                        <svg class="w-4 h-4 text-gray-500 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-[11px] text-gray-600 leading-tight">
                            Pastikan seluruh bingkai KTP terlihat jelas, tidak blur, dan tidak silau pantulan cahaya.
                        </p>
                    </div>

                    <!-- Checkbox Persetujuan-->
                    <div class="mt-auto mb-5 flex items-start">
                        <input type="checkbox" id="persetujuan" name="persetujuan"
                            class="mt-0.5 w-4 h-4 text-black border-gray-300 rounded focus:ring-black" required>
                        <label for="persetujuan" class="ml-2.5 text-[11px] text-gray-500 leading-relaxed">
                            Saya menyatakan data identitas dan dokumen yang diunggah adalah asli, sah, dan valid sesuai
                            hukum untuk syarat sewa di MirrorCam.
                        </label>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit"
                        class="w-full bg-black text-white font-semibold text-sm py-3.5 rounded hover:bg-gray-800 transition duration-200 flex justify-center items-center">
                        SIMPAN & LANJUTKAN <span class="ml-2">&rarr;</span>
                    </button>

                    <!-- Dukungan -->
                    <div class="mt-4 text-center">
                        <a href="#"
                            class="inline-flex items-center text-[11px] font-semibold text-gray-500 hover:text-gray-800 transition">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Butuh bantuan? Hubungi Dukungan
                        </a>
                    </div>
                </div>
            </div>
        </form>
        <script>
            function previewKTPImage(event) {
                const input = event.target;
                const defaultView = document.getElementById('dropzone-default');
                const previewView = document.getElementById('dropzone-preview');
                const imgPreview = document.getElementById('ktp-preview-img');
                const fileName = document.getElementById('ktp-file-name');
                const fileSize = document.getElementById('ktp-file-size');

                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        imgPreview.src = e.target.result;
                        fileName.textContent = file.name;
                        fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

                        defaultView.classList.add('hidden');
                        previewView.classList.remove('hidden');
                    };

                    reader.readAsDataURL(file);
                }
            }
        </script>
    </div>
</x-layouts::auth>
