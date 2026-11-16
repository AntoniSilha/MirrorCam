<div id="detailModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4 font-['Montserrat']">
    <div class="bg-[#ffffff] border border-black/15 rounded-[4px] max-w-lg w-full overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-black/10">
            <h3 class="text-sm font-bold text-[#000000]">Detail Spesifikasi Peralatan</h3>
            <button type="button" onclick="closeDetailModal()" class="text-black/60 hover:text-[#000000] font-bold">✕</button>
        </div>
        <div class="p-5 space-y-4">
            <img id="detailImg" src="" alt="" class="w-full h-56 object-cover rounded-[4px] bg-[#f9f9f9]">
            <div>
                <span id="detailKategori" class="px-2 py-0.5 rounded-[4px] bg-[#f9f9f9] border border-black/10 text-[10px] font-semibold"></span>
                <h4 id="detailNama" class="text-lg font-bold text-[#000000] mt-2"></h4>
                <p id="detailDeskripsi" class="text-xs text-black/70 leading-relaxed mt-2"></p>
            </div>
            <div class="grid grid-cols-3 gap-3 bg-[#f9f9f9] p-3 rounded-[4px] border border-black/10 text-xs">
                <div>
                    <span class="text-[10px] text-black/50 block">Warna Unit</span>
                    <strong id="detailWarna" class="font-semibold"></strong>
                </div>
                <div>
                    <span class="text-[10px] text-black/50 block">Stok Tersedia</span>
                    <strong id="detailStok" class="font-semibold"></strong>
                </div>
                <div>
                    <span class="text-[10px] text-black/50 block">Tarif Sewa</span>
                    <strong id="detailHarga" class="font-bold"></strong>
                </div>
            </div>
        </div>
        <div class="px-5 py-3.5 bg-[#f9f9f9] border-t border-black/10 flex justify-end">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 rounded-[4px] bg-[#d9d9d9] text-[#000000] text-xs font-semibold">
                Tutup
            </button>
        </div>
    </div>
</div>
