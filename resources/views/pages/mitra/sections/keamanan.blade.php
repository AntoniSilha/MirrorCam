                    <section class="grid gap-5 lg:grid-cols-[1.25fr_0.75fr]">
                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <h1 class="text-lg font-bold tracking-tight text-black">Keamanan &amp; Password</h1>
                            <p class="mt-1 text-xs text-neutral-500">Akun tenant Anda terhubung melalui autentikasi Google.</p>

                            <div class="mt-6 flex items-start gap-4 rounded-lg border border-neutral-200 p-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#f1f1f1]">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 3v6c0 4.2-2.9 7.4-7 9-4.1-1.6-7-4.8-7-9V6l7-3z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-bold text-black">Login dengan Google</p>
                                    <p class="mt-1 text-xs leading-relaxed text-neutral-500">Password dan verifikasi dua langkah dikelola langsung oleh akun Google {{ $tenant['email'] }}.</p>
                                </div>
                            </div>

                            <a href="https://myaccount.google.com/security" target="_blank" rel="noopener" class="mt-5 inline-flex items-center gap-2 rounded-md bg-black px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-neutral-800">
                                Kelola Keamanan Google
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 5h5v5M19 5l-8 8M19 14v5H5V5h5" />
                                </svg>
                            </a>
                        </div>

                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <h2 class="text-sm font-bold text-black">Sesi Aktif</h2>
                            <div class="mt-4 rounded-lg bg-[#f1f1f1] p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-black">Perangkat ini</span>
                                    <span class="rounded-full bg-[#dcfce7] px-2 py-0.5 text-[9px] font-bold text-[#15803d]">Aktif</span>
                                </div>
                                <p class="mt-2 text-[10px] text-neutral-500">Sesi login saat ini tersinkronisasi secara real-time.</p>
                            </div>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
    @csrf
    <button type="submit" class="text-xs text-gray-600 hover:text-black font-semibold cursor-pointer">
        Keluar
    </button>
</form>
                        </div>
                    </section>
