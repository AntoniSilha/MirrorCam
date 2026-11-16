@extends('layouts.landing')

@section('title', 'Katalog Produk — MirrorCam')

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8 font-['Montserrat']">
    <!-- Header Judul -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#000000] tracking-tight">Katalog Produk</h1>
        <p class="text-xs sm:text-sm text-black/60 mt-1.5 max-w-2xl leading-relaxed">
            Eksplorasi ribuan kamera bioskop, seri lensa anamorphic, lighting kit, dan rig profesional dari {{ $totalMitraAktif > 0 ? $totalMitraAktif : '148+' }} mitra rental terverifikasi di seluruh Indonesia.
        </p>
    </div>
        <!-- Layout Utama: Sidebar Filter (Kiri) + Grid Produk (Kanan) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Sidebar Filter Pencarian -->
           <aside class="lg:col-span-3 bg-[#ffffff] border border-black/10 rounded-[4px] p-5 space-y-6">
                <div class="flex items-center justify-between pb-3.5 border-b border-black/10">
                    <span class="inline-flex items-center gap-2 text-base font-bold text-[#000000]">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="6" y2="6"/><line x1="7" x2="17" y1="12" y2="12"/><line x1="10" x2="14" y1="18" y2="18"/></svg>
                        Filter Pencarian
                    </span>
                    <a href="{{ route('katalog.index') }}" class="text-xs font-medium text-black/50 hover:text-[#000000] underline">
                        Reset Filter
                    </a>
                </div>

                <!-- 1. Lokasi & Hub Tenant -->
               <div x-data="{ open: true }" class="border-b border-black/10 pb-5">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-xs font-bold uppercase tracking-wider text-[#000000] mb-3.5">
                        <span>LOKASI & HUB TENANT</span>
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div x-show="open" class="space-y-3 text-sm">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="inline-flex items-center gap-2.5 font-semibold text-[#000000]">
                                <input type="checkbox" onchange="if(this.checked){ document.querySelectorAll('.chk-lokasi').forEach(el => el.checked = false); this.form.submit(); }"
                                       {{ empty(request('lokasi')) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded-[4px] accent-[#000000]">
                                Semua Wilayah
                            </span>
                            <span class="px-2 py-0.5 rounded-[4px] bg-[#f9f9f9] text-xs font-semibold text-black/60">
                                {{ number_format($totalSemuaWilayah) }}
                            </span>
                        </label>

                        @foreach($wilayahList as $key => $label)
                            <label class="flex items-center justify-between cursor-pointer text-black/80 hover:text-[#000000]">
                                <span class="inline-flex items-center gap-2.5">
                                    <input type="checkbox" name="lokasi[]" value="{{ $key }}" onchange="this.form.submit()"
                                           {{ in_array($key, (array) request('lokasi', [])) ? 'checked' : '' }}
                                           class="chk-lokasi w-4 h-4 rounded-[4px] accent-[#000000]">
                                    <span class="line-clamp-1">{{ $label }}</span>
                                </span>
                                <span class="px-2 py-0.5 rounded-[4px] bg-[#f9f9f9] text-xs font-medium text-black/50">
                                    {{ $wilayahCounts[$key] ?? 0 }}
                                </span>
                            </label>
                        @endforeach

                        <div class="pt-2 flex items-center justify-between text-xs text-black/60">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                                Radius SafeDrop Hub
                            </span>
                            <span class="font-bold text-[#000000]">Hingga 25 km</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Rentang Harga Sewa -->
                <div class="border-b border-black/10 pb-5">
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-[#000000] mb-3.5">
                        <span>RENTANG HARGA SEWA</span>
                        <span class="text-xs font-normal normal-case text-black/50">/ Hari</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 mb-3.5">
                        <div class="p-2.5 rounded-[4px] bg-[#f9f9f9] border border-black/10">
                            <span class="block text-[10px] text-black/45 uppercase font-semibold">MINIMAL</span>
                            <input type="text" name="min_harga" value="{{ request('min_harga', 'Rp 100.000') }}"
                                   class="w-full text-sm font-bold text-[#000000] bg-transparent border-none p-0 mt-0.5 focus:outline-none">
                        </div>
                        <div class="p-2.5 rounded-[4px] bg-[#f9f9f9] border border-black/10">
                            <span class="block text-[10px] text-black/45 uppercase font-semibold">MAKSIMAL</span>
                            <input type="text" name="max_harga" value="{{ request('max_harga', 'Rp 5.000.000') }}"
                                   class="w-full text-sm font-bold text-[#000000] bg-transparent border-none p-0 mt-0.5 focus:outline-none">
                        </div>
                    </div>

                    <div class="space-y-3 text-sm text-black/80">
                        @php
                            $presets = [
                                'under_300k' => '< Rp 300.000 / hari',
                                '300k_1m'    => 'Rp 300.000 - Rp 1.000.000 / hari',
                                '1m_3m'      => 'Rp 1.000.000 - Rp 3.000.000 / hari',
                                'above_3m'   => '> Rp 3.000.000 / hari',
                            ];
                        @endphp
                        @foreach($presets as $val => $text)
                            <label class="flex items-center gap-2.5 cursor-pointer hover:text-[#000000]">
                                <input type="radio" name="harga_preset" value="{{ $val }}" onchange="this.form.submit()"
                                       {{ request('harga_preset') == $val ? 'checked' : '' }}
                                       class="w-4 h-4 accent-[#000000]">
                                <span class="{{ request('harga_preset') == $val ? 'font-bold text-[#000000]' : '' }}">{{ $text }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- 3. Rating Produk & Tenant -->
                <div class="border-b border-black/10 pb-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-[#000000] mb-3.5">
                        RATING PRODUK & TENANT
                    </span>
                    <div class="space-y-3 text-sm">
                        @foreach(['4.8' => '4.8 +', '4.5' => '4.5', '4.0' => '4.0 ke atas'] as $rVal => $rLabel)
                            <label class="flex items-center gap-2.5 cursor-pointer text-black/80 hover:text-[#000000]">
                                <input type="checkbox" name="rating[]" value="{{ $rVal }}" onchange="this.form.submit()"
                                       {{ in_array($rVal, (array) request('rating', [])) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded-[4px] accent-[#000000]">
                                <svg class="w-4 h-4 fill-[#000000] text-[#000000]" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <span class="font-semibold">{{ $rLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- 4. Kategori Peralatan -->
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-[#000000] mb-3.5">
                        KATEGORI PERALATAN
                    </span>
                    <div class="space-y-3 text-sm">
                        @forelse($kategoris as $kat)
                            <label class="flex items-center justify-between cursor-pointer text-black/80 hover:text-[#000000]">
                                <span class="inline-flex items-center gap-2.5">
                                    <input type="checkbox" name="kategori[]" value="{{ $kat->id_kategori }}" onchange="this.form.submit()"
                                           {{ in_array($kat->id_kategori, (array) request('kategori', [])) ? 'checked' : '' }}
                                           class="w-4 h-4 rounded-[4px] accent-[#000000]">
                                    <span class="line-clamp-1">{{ $kat->nama_kategori }}</span>
                                </span>
                                <span class="px-2 py-0.5 rounded-[4px] bg-[#f9f9f9] text-xs font-semibold text-black/60">
                                    {{ $kat->produks_count }}
                                </span>
                            </label>
                        @empty
                            <p class="text-xs text-black/45">Belum ada kategori terdaftar.</p>
                        @endforelse
                    </div>
                </div>
            </aside>

            <!-- Grid Produk (Kanan) -->
            <div class="lg:col-span-9">
                @if($produks->isEmpty())
                    <div class="bg-[#ffffff] border border-black/10 rounded-[4px] p-12 text-center">
                        <p class="text-sm font-bold text-[#000000]">Tidak ada peralatan yang sesuai filter</p>
                        <p class="text-xs text-black/50 mt-1">Coba ubah kata kunci pencarian atau reset filter di sebelah kiri.</p>
                        <a href="{{ route('katalog.index') }}" class="inline-block mt-4 px-4 py-2 rounded-[4px] bg-[#000000] text-[#ffffff] text-xs font-semibold">
                            Reset Semua Filter
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                        @foreach($produks as $produk)
                            @include('customer.tenant.components.product-card', [
                                'produk' => $produk,
                                'tenant' => $produk->tenant
                            ])
                        @endforeach
                    </div>

                    <!-- Footer Info & Pagination -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8 pt-4 border-t border-black/10">
                        <p class="text-xs text-black/60">
                            Menampilkan <span class="font-bold text-[#000000]">{{ $produks->firstItem() }} - {{ $produks->lastItem() }}</span>
                            dari <span class="font-bold text-[#000000]">{{ $produks->total() }}</span> peralatan
                        </p>

                        <div class="flex items-center gap-1.5">
                            @if ($produks->onFirstPage())
                                <span class="w-8 h-8 rounded-[4px] border border-black/15 flex items-center justify-center text-black/30">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                                </span>
                            @else
                                <a href="{{ $produks->previousPageUrl() }}" class="w-8 h-8 rounded-[4px] border border-black/15 flex items-center justify-center text-[#000000] hover:bg-[#f9f9f9]">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                                </a>
                            @endif

                            @foreach ($produks->getUrlRange(1, $produks->lastPage()) as $page => $url)
                                <a href="{{ $url }}"
                                   class="w-8 h-8 rounded-[4px] text-xs font-semibold flex items-center justify-center transition {{ $page == $produks->currentPage() ? 'bg-[#000000] text-[#ffffff]' : 'border border-black/15 text-[#000000] hover:bg-[#f9f9f9]' }}">
                                    {{ $page }}
                                </a>
                            @endforeach

                            @if ($produks->hasMorePages())
                                <a href="{{ $produks->nextPageUrl() }}" class="w-8 h-8 rounded-[4px] border border-black/15 flex items-center justify-center text-[#000000] hover:bg-[#f9f9f9]">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                                </a>
                            @else
                                <span class="w-8 h-8 rounded-[4px] border border-black/15 flex items-center justify-center text-black/30">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Modal Detail Spek Produk -->
<div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-[#ffffff] rounded-[4px] max-w-lg w-full p-6 border border-black/15 shadow-xl">
        <div class="flex items-start justify-between gap-4 pb-3 border-b border-black/10">
            <div>
                <span id="modalTenant" class="text-[11px] font-semibold text-black/50 uppercase"></span>
                <h3 id="modalTitle" class="text-lg font-bold text-[#000000] mt-0.5"></h3>
            </div>
            <button type="button" onclick="closeDetailModal()" class="p-1 text-black/50 hover:text-[#000000]">✕</button>
        </div>
        <div class="mt-4 space-y-3 text-xs">
            <img id="modalImage" src="" alt="" class="w-full h-52 object-cover rounded-[4px] bg-[#f9f9f9]">
            <div class="flex items-center justify-between pt-1">
                <span class="text-black/60">Warna / Varian: <strong id="modalColor" class="text-[#000000]"></strong></span>
                <span class="text-black/60">Stok Tersedia: <strong id="modalStock" class="text-[#000000]"></strong></span>
            </div>
            <p id="modalDesc" class="text-black/75 leading-relaxed"></p>
            <div class="pt-3 border-t border-black/10 flex items-center justify-between">
                <div>
                    <span class="block text-[10px] text-black/50">Tarif Sewa</span>
                    <span id="modalPrice" class="text-base font-bold text-[#000000]"></span>
                </div>
                <button type="button" onclick="closeDetailModal()" class="px-5 py-2.5 rounded-[4px] bg-[#000000] text-[#ffffff] font-semibold">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifikasi Tambah Keranjang -->
<div id="cartToast" class="fixed bottom-5 right-5 z-50 hidden items-center gap-3 bg-[#000000] text-[#ffffff] px-4 py-3 rounded-[4px] shadow-lg text-xs font-medium">
    <span id="cartToastText">Alat ditambahkan ke keranjang sewa!</span>
</div>

<script>
    function openDetailModal(produk) {
        document.getElementById('modalTenant').innerText = produk.tenant ? produk.tenant.nama_toko : 'MirrorCam Tenant';
        document.getElementById('modalTitle').innerText = produk.nama_produk;
        document.getElementById('modalImage').src = produk.primary_image || '';
        document.getElementById('modalColor').innerText = produk.color_produk || '-';
        document.getElementById('modalStock').innerText = (produk.total_stock_unit ?? 0) + ' Unit';
        document.getElementById('modalDesc').innerText = produk.deskripsi_lengkap || 'Tidak ada deskripsi tambahan.';
        document.getElementById('modalPrice').innerText = 'Rp ' + Number(produk.harga_sewa).toLocaleString('id-ID') + ' / hari';

        const modal = document.getElementById('detailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDetailModal() {
        const modal = document.getElementById('detailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function addToCart(produk) {
        let cart = JSON.parse(localStorage.getItem('mirrorcam_cart') || '[]');
        const existing = cart.find(item => item.id_produk === produk.id_produk);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({
                id_produk: produk.id_produk,
                nama_produk: produk.nama_produk,
                harga_sewa: produk.harga_sewa,
                qty: 1
            });
        }
        localStorage.setItem('mirrorcam_cart', JSON.stringify(cart));

        const totalQty = cart.reduce((sum, i) => sum + i.qty, 0);
        const badge = document.getElementById('cartBadgeCount');
        if (badge) badge.innerText = totalQty;

        const toast = document.getElementById('cartToast');
        document.getElementById('cartToastText').innerText = `"${produk.nama_produk}" ditambahkan ke keranjang (${totalQty} item)`;
        toast.classList.remove('hidden');
        toast.classList.add('flex');
        setTimeout(() => {
            toast.classList.add('hidden');
            toast.classList.remove('flex');
        }, 3000);
    }
</script>
@endsection
