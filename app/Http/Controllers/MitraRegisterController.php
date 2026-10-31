<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterMitraRequest;
use App\Models\Mitra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\View\View;

class MitraRegisterController extends Controller
{
    /**
     * Menampilkan form pendaftaran mitra
     */
    public function create(): View|RedirectResponse
    {
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
        $validated = $request->validated();

        if ($request->hasFile('foto_ktp')) {
            $validated['foto_ktp_path'] = $request->file('foto_ktp')->store('ktp_mitra', 'public');
        }

        $validated['user_id'] = auth()->id() ?? 1;

        $validated['status'] = 'pending';
        Mitra::create($validated);
        return redirect()->route('register.mitra')->with('success', 'Pendaftaran Mitra Berhasil Disimpan!');
    }
}

