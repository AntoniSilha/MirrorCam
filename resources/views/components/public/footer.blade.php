<footer class="bg-[#ffffff] border-t border-[#e5e5e5] mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
            <!-- Brand Column -->
            <div class="lg:col-span-2 space-y-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 bg-[#000000] text-white rounded-[4px] flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-base font-extrabold tracking-tight text-[#000000]">MIRRORCAM</span>
                </a>
                <p class="text-xs text-gray-600 leading-relaxed max-w-sm">
                    Platform Sewa Kamera, Aksesoris, & Studio Lintas Tenant No. 1 di Indonesia. Nikmati kemudahan eksplorasi peralatan multimedia berkualitas dengan sistem keamanan terpadu dan terpercaya.
                </p>
                <div class="flex items-center gap-3 pt-2 text-gray-500 text-xs">
                    <span class="inline-flex items-center gap-1 font-semibold text-[#16a34a]">
                        <span class="w-2 h-2 rounded-full bg-[#16a34a] animate-pulse"></span>
                        Platform Online & Siap Sewa
                    </span>
                </div>
            </div>

            <!-- Col 2: Kategori Peralatan -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-[#000000] uppercase tracking-wider">Kategori Peralatan</h4>
                <ul class="space-y-2 text-xs text-gray-600">
                    <li><a href="{{ route('home') }}#kategori" class="hover:text-[#000000] transition">Kamera Cinema & Mirrorless</a></li>
                    <li><a href="{{ route('home') }}#kategori" class="hover:text-[#000000] transition">Lensa Prime & Cine</a></li>
                    <li><a href="{{ route('home') }}#kategori" class="hover:text-[#000000] transition">Lighting & Rigging</a></li>
                    <li><a href="{{ route('home') }}#kategori" class="hover:text-[#000000] transition">Audio & Monitor</a></li>
                    <li><a href="{{ route('home') }}#kategori" class="hover:text-[#000000] transition">Drone & Gimbal</a></li>
                    <li><a href="{{ route('home') }}#kategori" class="hover:text-[#000000] transition">Studio Foto & Podcast</a></li>
                </ul>
            </div>

            <!-- Col 3: Tentang MirrorCam -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-[#000000] uppercase tracking-wider">Tentang MirrorCam</h4>
                <ul class="space-y-2 text-xs text-gray-600">
                    <li><a href="{{ route('home') }}#tentang" class="hover:text-[#000000] transition">Tentang Kami</a></li>
                    <li><a href="{{ route('home') }}#cara-kerja" class="hover:text-[#000000] transition">Alur Sewa Terpadu</a></li>
                    <li><a href="{{ route('home') }}#mitra-tenant" class="hover:text-[#000000] transition">Mitra Tenant Rekanan</a></li>
                    <li><a href="{{ route('home') }}#keamanan" class="hover:text-[#000000] transition">Protokol Keamanan</a></li>
                    <li><a href="#" class="hover:text-[#000000] transition">Syarat & Ketentuan</a></li>
                    <li><a href="#" class="hover:text-[#000000] transition">Kebijakan Privasi</a></li>
                </ul>
            </div>

            <!-- Col 4: Bantuan & Kontak -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-[#000000] uppercase tracking-wider">Bantuan & Kontak</h4>
                <ul class="space-y-2.5 text-xs text-gray-600">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>+62 812-3456-7890</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>support@mirrorcam.com</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Setiap Hari (08:00 - 21:00 WIB)</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Copyright -->
        <div class="mt-12 pt-6 border-t border-[#e5e5e5] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
            <p>© 2025 MirrorCam. Hak Cipta Dilindungi Undang-Undang.</p>
            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-[#000000] transition">Privacy Policy</a>
                <a href="#" class="hover:text-[#000000] transition">Terms of Service</a>
                <a href="#" class="hover:text-[#000000] transition">Sitemap</a>
            </div>
        </div>
    </div>
</footer>
