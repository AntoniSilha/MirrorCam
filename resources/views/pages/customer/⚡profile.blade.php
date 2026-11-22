<?php

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profil Saya - MirrorCam')] #[Layout('components.layouts.user-dashboard')] class extends Component {
    use WithFileUploads;

    public $avatarUpload = null;

    public string $no_whatsapp = '';

    public function mount(): void
    {
        $this->no_whatsapp = (string) (Auth::user()?->no_whatsapp ?? '');
    }

    public function save(): RedirectResponse
    {
        $this->no_whatsapp = (string) preg_replace('/[^0-9+]/', '', trim($this->no_whatsapp));

        $this->validate([
            'avatarUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'no_whatsapp' => ['required', 'string', 'regex:/^\+?[0-9]{8,19}$/'],
        ], [
            'avatarUpload.image' => 'Foto profil harus berupa gambar.',
            'avatarUpload.mimes' => 'Foto profil harus berformat JPG, JPEG, PNG, atau WEBP.',
            'avatarUpload.max' => 'Ukuran foto profil maksimal 2 MB.',
            'no_whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'no_whatsapp.regex' => 'Nomor WhatsApp harus berupa 8-19 digit angka dan boleh diawali dengan +62.',
        ]);

        $user = Auth::user();

        abort_if(! $user, 403);

        $data = ['no_whatsapp' => $this->no_whatsapp];

        if ($this->avatarUpload) {
            $oldAvatar = $user->avatar;
            $data['avatar'] = $this->avatarUpload->store('avatars', 'public');

            if ($oldAvatar && ! str_starts_with($oldAvatar, 'http://') && ! str_starts_with($oldAvatar, 'https://') && ! str_starts_with($oldAvatar, '/')) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }

        $user->update($data);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Profil berhasil diperbarui.');
    }

    public function avatarPreviewUrl(): ?string
    {
        if ($this->avatarUpload) {
            try {
                return $this->avatarUpload->temporaryUrl();
            } catch (\Throwable) {
                return null;
            }
        }

        return Auth::user()?->avatar_url;
    }
};
?>

@php
    $account = auth()->user();
    $avatar = $this->avatarPreviewUrl();
    $initials = $account?->initials ?? 'U';
    $email = $account?->email ?? '-';
    $nik = (string) ($account?->nik_ktp ?? '');
    $maskedNik = $nik !== ''
        ? (strlen($nik) > 8 ? substr($nik, 0, 4).' •••• '.substr($nik, -4) : $nik)
        : '-';
    $hasKtp = filled($account?->foto_ktp);
    $joinedAt = $account?->created_at?->translatedFormat('d F Y') ?? '-';
@endphp

<div class="mx-auto w-full max-w-5xl space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-neutral-400">Akun Saya</p>
            <h1 class="mt-1 text-xl font-extrabold tracking-tight text-black sm:text-2xl">Profil Saya</h1>
            <p class="mt-1 max-w-xl text-xs leading-relaxed text-neutral-500 sm:text-sm">
                Kelola foto profil dan nomor WhatsApp yang digunakan untuk komunikasi rental.
            </p>
        </div>

        <span class="inline-flex w-fit items-center gap-1.5 rounded-full border border-neutral-200 bg-white px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-neutral-600">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-1.657 1.343-3 3-3s3 1.343 3 3-1.343 3-3 3m-6 0a3 3 0 100-6 3 3 0 000 6z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 20a6 6 0 0112 0M15 20a6 6 0 016-6" />
            </svg>
            Akun Google
        </span>
    </div>

    <div class="grid gap-5 lg:grid-cols-[280px_minmax(0,1fr)] lg:items-start">
        <aside class="rounded-sm border border-neutral-200 bg-white p-5 shadow-xs lg:sticky lg:top-5">
            <div class="flex items-center gap-4 lg:flex-col lg:items-start">
                @if ($avatar)
                    <img src="{{ $avatar }}" alt="Foto profil {{ $account?->name }}" class="h-16 w-16 shrink-0 rounded-full border border-neutral-200 object-cover" referrerpolicy="no-referrer">
                @else
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-black text-lg font-extrabold text-white">
                        {{ $initials }}
                    </div>
                @endif

                <div class="min-w-0">
                    <h2 class="truncate text-sm font-extrabold text-black">{{ $account?->name ?? 'Pengguna' }}</h2>
                    <p class="mt-0.5 truncate text-xs text-neutral-500">{{ $email }}</p>
                    <span class="mt-2 inline-flex rounded-full bg-neutral-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-neutral-600">
                        {{ $account?->role_label ?? 'User' }}
                    </span>
                    @if ($this->avatarUpload)
                        <span class="mt-2 block text-[10px] font-semibold text-[#15803d]">Pratinjau foto baru</span>
                    @endif
                </div>
            </div>

            <dl class="mt-5 space-y-3 border-t border-neutral-100 pt-4 text-xs">
                <div class="flex items-start justify-between gap-3">
                    <dt class="text-neutral-500">NIK KTP</dt>
                    <dd class="text-right font-semibold text-black">{{ $maskedNik }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="text-neutral-500">Verifikasi KTP</dt>
                    <dd>
                        @if ($hasKtp)
                            <span class="inline-flex items-center gap-1 rounded-full bg-[#dcfce7] px-2 py-0.5 text-[10px] font-bold text-[#15803d]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#16a34a]"></span>
                                Terdaftar
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-neutral-100 px-2 py-0.5 text-[10px] font-bold text-neutral-500">
                                Belum ada
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="text-neutral-500">Bergabung</dt>
                    <dd class="text-right font-semibold text-black">{{ $joinedAt }}</dd>
                </div>
            </dl>

            <p class="mt-4 rounded-sm bg-neutral-50 p-3 text-[11px] leading-relaxed text-neutral-500">
                Nama, NIK, foto KTP, dan email mengikuti data KTP dan akun Google sehingga tidak dapat diubah dari halaman ini.
            </p>
        </aside>

        <div class="space-y-5">
            <form wire:submit="save" class="rounded-sm border border-neutral-200 bg-white p-5 shadow-xs sm:p-6">
                <div class="flex items-start gap-3 border-b border-neutral-100 pb-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-neutral-100 text-black">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-black">Informasi Pribadi</h2>
                        <p class="mt-0.5 text-xs text-neutral-500">Perbarui foto profil dan nomor WhatsApp Anda.</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-col gap-4 rounded-sm border border-neutral-200 bg-neutral-50/60 p-4 sm:flex-row sm:items-center">
                    <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-full border border-neutral-200 bg-black">
                        @if ($avatar)
                            <img src="{{ $avatar }}" alt="Foto profil {{ $account?->name }}" class="h-full w-full object-cover" referrerpolicy="no-referrer">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-xl font-extrabold text-white">
                                {{ $initials }}
                            </div>
                        @endif

                        <div wire:loading wire:target="avatarUpload" class="absolute inset-0 flex items-center justify-center bg-black/60 text-white">
                            <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-black">Foto Profil</p>
                        <p class="mt-1 text-[11px] leading-relaxed text-neutral-500">
                            Unggah JPG, PNG, atau WEBP maksimal 2 MB. Foto baru akan menggantikan avatar Google Anda.
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <label for="avatarUpload" class="inline-flex cursor-pointer items-center gap-1.5 rounded-sm border border-neutral-300 bg-white px-3 py-2 text-[11px] font-bold text-black transition hover:bg-neutral-50">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Pilih Foto
                                <input id="avatarUpload" type="file" wire:model="avatarUpload" accept="image/jpeg,image/png,image/webp" class="sr-only">
                            </label>

                            @if ($this->avatarUpload)
                                <button type="button" wire:click="$set('avatarUpload', null)" class="inline-flex items-center rounded-sm px-2.5 py-2 text-[11px] font-bold text-neutral-500 transition hover:bg-neutral-100 hover:text-black">
                                    Batalkan pilihan
                                </button>
                            @endif

                            <span wire:loading wire:target="avatarUpload" class="text-[11px] font-medium text-neutral-500">Mengunggah...</span>
                        </div>
                        @error('avatarUpload')
                            <p class="mt-1.5 text-[11px] font-medium text-[#ba1a1a]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="nama_lengkap_ktp" class="mb-1.5 block text-xs font-bold text-black">Nama Lengkap</label>
                        <input
                            id="nama_lengkap_ktp"
                            type="text"
                            value="{{ $account?->name }}"
                            disabled
                            class="w-full cursor-not-allowed rounded-sm border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-sm text-neutral-500"
                        >
                        <p class="mt-1.5 text-[10px] text-neutral-400">Nama mengikuti data KTP dan tidak dapat diubah.</p>
                    </div>

                    <div>
                        <label for="nik_ktp" class="mb-1.5 block text-xs font-bold text-black">NIK KTP</label>
                        <input
                            id="nik_ktp"
                            type="text"
                            value="{{ $maskedNik }}"
                            disabled
                            class="w-full cursor-not-allowed rounded-sm border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-sm text-neutral-500"
                        >
                        <p class="mt-1.5 text-[10px] text-neutral-400">Tidak dapat diubah.</p>
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-bold text-black">Email</label>
                        <input
                            id="email"
                            type="email"
                            value="{{ $email }}"
                            disabled
                            class="w-full cursor-not-allowed rounded-sm border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-sm text-neutral-500"
                        >
                        <p class="mt-1.5 text-[10px] text-neutral-400">Dikelola oleh akun Google.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="no_whatsapp" class="mb-1.5 block text-xs font-bold text-black">Nomor WhatsApp</label>
                        <div class="flex rounded-sm border border-neutral-300 bg-white transition focus-within:border-black focus-within:ring-1 focus-within:ring-black">
                            <span class="flex items-center border-r border-neutral-200 px-3 text-neutral-500">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </span>
                            <input
                                id="no_whatsapp"
                                type="tel"
                                wire:model="no_whatsapp"
                                autocomplete="tel"
                                inputmode="tel"
                                placeholder="0812-3456-7890"
                                class="w-full rounded-r-sm border-0 bg-transparent px-3 py-2.5 text-sm text-black outline-none placeholder:text-neutral-400"
                            >
                        </div>
                        @error('no_whatsapp')
                            <p class="mt-1.5 text-[11px] font-medium text-[#ba1a1a]">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-[10px] text-neutral-400">Gunakan nomor yang aktif. Nomor ini dipakai untuk koordinasi pesanan.</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-2 border-t border-neutral-100 pt-4 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-sm border border-neutral-300 bg-white px-4 py-2.5 text-xs font-bold text-neutral-700 transition hover:bg-neutral-50">
                        Batal
                    </a>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center justify-center gap-2 rounded-sm bg-black px-4 py-2.5 text-xs font-bold text-white transition hover:bg-neutral-800 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            </form>

            <div class="rounded-sm border border-neutral-200 bg-white p-5 shadow-xs sm:p-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-neutral-100 text-black">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-extrabold text-black">Kata Sandi &amp; Keamanan</h2>
                        <p class="mt-1 text-xs leading-relaxed text-neutral-500">
                            Akun MirrorCam terhubung melalui Google OAuth. Perubahan dan reset kata sandi dilakukan langsung melalui akun Google Anda.
                        </p>
                        <a href="https://myaccount.google.com/security" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-2 rounded-sm bg-black px-3.5 py-2.5 text-[11px] font-bold text-white transition hover:bg-neutral-800">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9a12.02 12.02 0 00-.382-3.016z" />
                            </svg>
                            Kelola Keamanan Google
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 5h5v5M19 5l-8 8M19 14v5H5V5h5" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
