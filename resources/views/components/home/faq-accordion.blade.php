<div id="faq" class="space-y-6" x-data="{ openItem: 1 }">
    <div>
        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-widest block mb-1">Pusat Bantuan</span>
        <h2 class="text-xl sm:text-2xl font-bold text-[#000000] tracking-tight">Pertanyaan yang Sering Diajukan</h2>
        <p class="text-xs sm:text-sm text-gray-600 mt-2 leading-relaxed">
            Temukan jawaban cepat seputar tata cara sewa, perlindungan asuransi, dan ketentuan penggunaan platform.
        </p>
    </div>

    <div class="space-y-3">
        <!-- FAQ Item 1 (Default Open) -->
        <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] overflow-hidden shadow-2xs transition">
            <button
                type="button"
                @click="openItem = openItem === 1 ? null : 1"
                class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-sm text-[#000000] hover:bg-[#fafafa] transition cursor-pointer"
            >
                <span>Bagaimana cara menyewa gear dari tenant yang berbeda sekaligus?</span>
                <svg
                    class="w-4 h-4 text-[#000000] shrink-0 transition-transform duration-200"
                    :class="openItem === 1 ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openItem === 1" x-collapse>
                <div class="px-4 pb-4 pt-1 text-xs text-gray-600 leading-relaxed border-t border-[#f0f0f0]">
                    Anda cukup memilih peralatan dari tenant manapun, memasukkannya ke dalam keranjang, dan melakukan checkout sekaligus. Sistem MirrorCam akan memecah pesanan ke masing-masing tenant secara otomatis dengan kode booking terpadu.
                </div>
            </div>
        </div>

        <!-- FAQ Item 2 -->
        <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] overflow-hidden shadow-2xs transition">
            <button
                type="button"
                @click="openItem = openItem === 2 ? null : 2"
                class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-sm text-[#000000] hover:bg-[#fafafa] transition cursor-pointer"
            >
                <span>Bagaimana jika terjadi kerusakan pada gear yang saya sewa?</span>
                <svg
                    class="w-4 h-4 text-[#000000] shrink-0 transition-transform duration-200"
                    :class="openItem === 2 ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openItem === 2" x-collapse>
                <div class="px-4 pb-4 pt-1 text-xs text-gray-600 leading-relaxed border-t border-[#f0f0f0]">
                    Setiap penyewaan dilindungi oleh jaminan dan protokol inspeksi digital. Jika terjadi kerusakan fisik atau kendala teknis non-kelalaian, tim mediasi MirrorCam akan membantu proses penyelesaian sesuai ketentuan garansi platform dan tenant rekanan.
                </div>
            </div>
        </div>

        <!-- FAQ Item 3 -->
        <div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] overflow-hidden shadow-2xs transition">
            <button
                type="button"
                @click="openItem = openItem === 3 ? null : 3"
                class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-sm text-[#000000] hover:bg-[#fafafa] transition cursor-pointer"
            >
                <span>Apakah ada syarat jaminan khusus untuk sewa pertama kali?</span>
                <svg
                    class="w-4 h-4 text-[#000000] shrink-0 transition-transform duration-200"
                    :class="openItem === 3 ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openItem === 3" x-collapse>
                <div class="px-4 pb-4 pt-1 text-xs text-gray-600 leading-relaxed border-t border-[#f0f0f0]">
                    Penyewa baru wajib melakukan verifikasi identitas KTP elektronik serta melampirkan akun sosial media aktif. Untuk unit gear bernilai tinggi (seperti Cinema Camera FX series), deposit saldo atau kartu identitas pendukung mungkin disesuaikan oleh tenant.
                </div>
            </div>
        </div>
    </div>
</div>
