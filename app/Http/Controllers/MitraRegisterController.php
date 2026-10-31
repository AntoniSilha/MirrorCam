<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterMitraRequest;
use App\Models\Mitra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MitraRegisterController extends Controller
{
    /**
     * Menampilkan form pendaftaran mitra
     */
    public function create(): View|RedirectResponse
    {
        // Cek jika user sudah mendaftar mitra sebelumnya
        $existingMitra = Mitra::where('user_id', auth()->id())->first();

        if ($existingMitra) {
            return redirect()->route('dashboard')->with('info', 'Anda sudah mendaftarkan akun mitra. Silakan tunggu verifikasi admin.');
        }

        return view('pages.auth.register-mitra');
    }

    /**
     * Memproses formulir pendaftaran mitra
     */
    public function store(RegisterMitraRequest $request): RedirectResponse
    {
        // 1. Ambil data yang sudah tervalidasi dari RegisterMitraRequest
        $validated = $request->validated();

        // 2. Handle upload foto KTP jika ada
        if ($request->hasFile('foto_ktp')) {
            $validated['foto_ktp_path'] = $request->file('foto_ktp')->store('ktp_mitra', 'public');
        }

        // 3. Set user_id (pake ID user yang login, atau ID 1 jika belum login/testing)
        $validated['user_id'] = auth()->id() ?? 1;

        // 4. Set status default untuk pendaftaran baru
        $validated['status'] = 'pending';

        // 5. Simpan ke database (Cukup 1 kali eksekusi)
        Mitra::create($validated);

        // 6. Redirect kembali dengan pesan sukses
        return redirect()->route('register.mitra')->with('success', 'Pendaftaran Mitra Berhasil Disimpan!');
    }
}

