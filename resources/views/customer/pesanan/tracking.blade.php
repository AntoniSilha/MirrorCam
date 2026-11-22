@extends('layouts.landing')

@section('title', 'Pelacakan Pesanan Aktif — MirrorCam')

@section('content')
@php
    $tenant = $booking->tenant;
    $product = $booking->produk;
    $bookingCode = $booking->sub_order_eksternal ?: 'MC-'.str_pad((string) $booking->id_pemesanan, 4, '0', STR_PAD_LEFT);
    $transactionCode = preg_replace('/-\d{2}$/', '', $bookingCode);
    $phone = preg_replace('/\D+/', '', (string) ($tenant?->whatsapp_resmi ?? ''));
    if (str_starts_with($phone, '0')) $phone = '62'.substr($phone, 1);
    $steps = [
        ['label' => '1. Melakukan DP', 'done' => $dpPaid],
        ['label' => '2. Pembayaran Lunas', 'done' => $fullPayment],
        ['label' => '3. Digunakan', 'done' => $inUse],
        ['label' => '4. Pengembalian', 'done' => $returned],
    ];
@endphp

<div class="min-h-[65vh] bg-[#f4f4f5] py-8 sm:py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            @if (session('error'))
                <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-xs text-amber-800">{{ session('error') }}</div>
            @endif
            <a href="{{ route('dashboard') }}" class="mb-4 inline-flex items-center gap-2 text-xs font-semibold text-neutral-600 hover:text-black">&larr; Kembali ke Dashboard</a>
            <h1 class="text-2xl font-bold tracking-tight text-black sm:text-3xl">Pelacakan Pesanan Aktif</h1>
            <p class="mt-1 text-sm text-neutral-500">Kode Transaksi Induk: <strong class="text-black">{{ $transactionCode }}</strong></p>
        </div>

        <div class="grid items-start gap-6 lg:grid-cols-12">
            <section class="space-y-4 lg:col-span-8">
                <article class="overflow-hidden rounded-xl border border-neutral-200 bg-white">
                    <header class="flex flex-wrap items-center gap-x-3 gap-y-1 bg-neutral-100 px-5 py-4">
                        <h2 class="text-sm font-bold uppercase text-black">{{ $tenant?->nama_toko ?? 'Tenant' }}</h2>
                        <span class="text-[11px] text-neutral-500">ID: {{ $bookingCode }}</span>
                    </header>
                    <div class="p-5 sm:p-6">
                        <h3 class="text-sm font-bold text-black">{{ $product?->nama_produk ?? 'Produk' }}</h3>
                        <div class="mt-6 flex gap-0 overflow-x-auto pb-2">
                            @foreach ($steps as $index => $step)
                                <div class="relative flex min-w-[120px] flex-1 flex-col items-center gap-2 text-center">
                                    @if ($index < count($steps) - 1)
                                        <span class="absolute left-[calc(50%+9px)] right-[calc(-50%+9px)] top-[7px] h-0.5 {{ $step['done'] && $steps[$index + 1]['done'] ? 'bg-green-700' : 'bg-neutral-200' }}"></span>
                                    @endif
                                    <span class="relative z-10 h-4 w-4 shrink-0 rounded-full {{ $step['done'] ? 'bg-green-700' : 'bg-neutral-300' }}"></span>
                                    <span class="relative z-10 px-1 text-[10px] font-semibold leading-snug {{ $step['done'] ? 'text-black' : 'text-neutral-400' }}">{{ $step['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-6 border-t border-neutral-200 pt-5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-neutral-500">Detail Titik Lokasi</p>
                            <p class="mt-1 text-xs text-black">{{ $tenant?->nama_toko ?? 'Tenant' }} - {{ $tenant?->alamat_toko ?? 'Lokasi belum tersedia' }}</p>
                            <p class="mt-1 text-[10px] text-neutral-500">Penyewa wajib memperlihatkan KTP asli saat serah terima fisik.</p>
                        </div>
                    </div>
                </article>
            </section>

            <aside class="rounded-xl border border-neutral-200 bg-white p-5 sm:p-6 lg:col-span-4">
                <h2 class="text-base font-bold text-black">Sistem Booking</h2>
                <p class="mt-3 text-xs leading-relaxed text-neutral-500">Pantau tahapan pembayaran dan masa sewa pesanan Anda dari halaman ini.</p>
                <ul class="mt-4 list-inside list-disc space-y-2 text-xs text-neutral-500">
                    <li>Unit wajib dicek bersama saat serah terima fisik.</li>
                    <li>Keterlambatan pengembalian dapat dikenakan denda sesuai ketentuan tenant.</li>
                </ul>
                @if ($phone)
                    <a href="https://wa.me/{{ $phone }}?text={{ urlencode('Halo, saya ingin menanyakan pesanan '.$bookingCode) }}" target="_blank" rel="noopener noreferrer" class="mt-5 flex w-full justify-center rounded-md bg-black px-4 py-3 text-xs font-semibold text-white hover:bg-neutral-800">Hubungi Bantuan Support</a>
                @endif
                <a href="{{ route('pesanan.nota', $booking->id_pemesanan) }}" class="mt-3 flex w-full justify-center rounded-md bg-black px-4 py-3 text-xs font-semibold text-white hover:bg-neutral-800">Cetak Nota Transaksi</a>
            </aside>
        </div>

        @if ($dpPaid && ! str_contains(strtolower((string) $booking->status_sewa), 'batal'))
            <section class="mt-6 max-w-3xl rounded-xl border border-neutral-200 bg-white p-5 sm:p-6">
                <h2 class="text-base font-bold text-black">Komentar dan Rating Tenant</h2>
                @if (session('success'))
                    <p class="mt-2 text-xs font-semibold text-green-700">{{ session('success') }}</p>
                @endif
                @if ($review)
                    <div class="mt-3 rounded-md bg-neutral-50 p-4 text-xs text-neutral-700">
                        <p class="font-bold">Rating: {{ $review->rating_nilai }}/5</p>
                        <p class="mt-1">{{ $review->komentar_text ?: 'Tidak ada komentar.' }}</p>
                        <p class="mt-2 text-[10px] text-neutral-500">Anda dapat mengirim kembali untuk memperbarui komentar.</p>
                    </div>
                @endif
                <form method="POST" action="{{ route('pesanan.komentar', $booking->id_pemesanan) }}" class="mt-4 space-y-3">
                    @csrf
                    <label class="block text-xs font-semibold text-neutral-700">Rating
                        <select name="rating_nilai" required class="mt-1 block w-full rounded-md border border-neutral-300 px-3 py-2 text-xs">
                            @for ($rating = 5; $rating >= 1; $rating--)
                                <option value="{{ $rating }}" @selected(old('rating_nilai', $review?->rating_nilai) == $rating)>{{ $rating }} / 5</option>
                            @endfor
                        </select>
                    </label>
                    <label class="block text-xs font-semibold text-neutral-700">Komentar
                        <textarea name="komentar_text" rows="3" maxlength="1000" class="mt-1 block w-full rounded-md border border-neutral-300 px-3 py-2 text-xs" placeholder="Bagikan pengalaman Anda menyewa dari tenant ini">{{ old('komentar_text', $review?->komentar_text) }}</textarea>
                    </label>
                    @error('rating_nilai') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    @error('komentar_text') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <button type="submit" class="rounded-md bg-black px-4 py-2.5 text-xs font-semibold text-white">{{ $review ? 'Perbarui Komentar' : 'Kirim Komentar' }}</button>
                </form>
            </section>
        @endif
    </div>
</div>
<style>
@media print { header, footer, aside, a { display: none !important; } body { background: white !important; } }
</style>
@endsection
