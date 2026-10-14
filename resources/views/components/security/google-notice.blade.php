@props([
    'isGoogleAccount' => false,
    'hasPassword' => true,
])

<div class="bg-[#f3f4f6] border border-neutral-200/80 rounded-[4px] p-4 mb-6 text-xs text-neutral-700">
    <div class="flex items-start gap-3">
        <!-- Info Icon -->
        <div class="w-5 h-5 text-neutral-500 flex-shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                <line x1="12" y1="16" x2="12" y2="12" stroke-width="2"></line>
                <line x1="12" y1="8" x2="12.01" y2="8" stroke-width="2"></line>
            </svg>
        </div>

        <div class="flex-1 leading-relaxed">
            <p>
                Gunakan minimal 8 karakter dengan kombinasi huruf besar, kecil, angka numerik, dan simbol khusus untuk menjaga privasi kontrak sewa gear Anda.
            </p>

            @if ($isGoogleAccount)
                <div class="mt-2.5 pt-2.5 border-t border-neutral-200/80 text-neutral-800">
                    <span class="font-semibold text-black flex items-center gap-1.5 mb-1">
                        <svg class="w-4 h-4 text-neutral-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                        </svg>
                        Otorisasi Akun Google OAuth Terdeteksi
                    </span>
                    <p class="text-neutral-600">
                        Akun ini terhubung dengan Google. Anda dapat menetapkan kata sandi lokal di bawah ini agar dapat masuk langsung menggunakan alamat email dan kata sandi tanpa bergantung pada Google OAuth.
                        @if (! $hasPassword)
                            <strong class="text-black font-semibold">Anda tidak perlu memasukkan kata sandi saat ini karena belum memiliki sandi lokal.</strong>
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
