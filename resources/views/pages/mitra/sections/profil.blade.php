                    <section class="grid gap-5 lg:grid-cols-[1.25fr_0.75fr]">
                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <h1 class="text-lg font-bold tracking-tight text-black">Pengaturan Profile</h1>
                            <p class="mt-1 text-xs text-neutral-500">Informasi resmi tenant yang tampil pada halaman publik MirrorCam.</p>

                            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">Nama Toko</dt>
                                    <dd class="mt-1.5 text-sm font-semibold text-black">{{ $tenant['nama_toko'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">Penanggung Jawab</dt>
                                    <dd class="mt-1.5 text-sm font-semibold text-black">{{ $tenant['pemilik'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">Email Resmi</dt>
                                    <dd class="mt-1.5 text-sm text-neutral-700">{{ $tenant['email'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">WhatsApp</dt>
                                    <dd class="mt-1.5 text-sm text-neutral-700">{{ $tenant['whatsapp'] }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">Alamat Toko</dt>
                                    <dd class="mt-1.5 text-sm text-neutral-700">{{ $tenant['alamat'] }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                            <h2 class="text-sm font-bold text-black">Status Kemitraan</h2>
                            <div class="mt-4 flex items-center gap-3 rounded-lg bg-[#f1f1f1] p-4">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-black text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-xs font-bold text-black">{{ $tenant['status'] }}</p>
                                    <p class="mt-0.5 text-[10px] text-neutral-500">Tenant aktif di platform MirrorCam</p>
                                </div>
                            </div>
                            <p class="mt-4 text-[11px] leading-relaxed text-neutral-500">Hubungi admin MirrorCam jika ingin memperbarui data legalitas atau rekening settlement tenant.</p>
                        </div>
                    </section>
