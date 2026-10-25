<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use thiagoalessio\TesseractOCR\TesseractOCR;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $accountGoogle = \App\Models\AccountGoogle::firstOrCreate(
                ['id_user_google' => $googleUser->id],
                ['email' => $googleUser->email]
            );

            $user = \App\Models\User::where('id_user_google', $googleUser->id)->first();

            if ($user) {
                Auth::login($user);
                return redirect()->intended('/dashboard');
            } else {
                session([
                    'google_id' => $googleUser->id,
                    'google_email' => $googleUser->email,
                    'google_name' => $googleUser->name 
                ]);

                return redirect()->route('complete.profile');
            }

        }catch (\Exception $e) {
            return redirect('/login')->with('error', 'Terjadi kesalahan saat otentikasi Google.');
        }
    }

    public function storeDataProfile(Request $request)
    {
        if (!session()->has('google_id')) {
            return redirect('/login')->with('error', 'Sesi pendaftaran habis, silakan ulangi.');
        }

        $request->validate([
            'nama_lengkap_ktp' => 'required|string|max:255',
            'nik_ktp' => 'required|string|max:16|unique:users',
            'no_whatsapp' => 'required|string|max:20',
            'foto_ktp' => 'required|image|mimes:jpeg,png,jpg|max:5120', 
        ]);

        $imagePath = $request->file('foto_ktp')->getPathName();

        try{
        $ocrText = (new TesseractOCR($imagePath))
            ->executable(env('TESSERACT_PATH', '/usr/bin/tesseract'))
            ->lang('ind', 'eng')
            ->run();

        if (empty(trim($ocrText))) {
        return back()->with('error', 'Tidak ada teks yang terbaca. Pastikan Anda mengunggah foto KTP asli yang jelas.')->withInput();
        }
            
        $ocrText = strtoupper($ocrText);

        if (!str_contains($ocrText, 'NIK') && !str_contains($ocrText, 'PROVINSI')) {
            return back()->withErrors(['foto_ktp' => 'Teks standar KTP tidak terbaca. Pastikan foto KTP fisik jelas.'])->withInput();
        }

        preg_match('/\b\d{16}\b/', $ocrText, $matches);
        $detectedNik = $matches[0] ?? null;

        if ($detectedNik !== $request->nik_ktp) {
            return back()->withErrors(['foto_ktp' => 'NIK di foto KTP tidak sesuai dengan yang diketik.'])->withInput();
        }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memindai gambar KTP. Pastikan file bukan gambar kosong atau buram.')->withInput();
        }

        $user = User::create([
            'id_user_google' => session('google_id'),
            'role' => 'tenant',
            'nama_lengkap_ktp' => $request->nama_lengkap_ktp,
            'nik_ktp' => $request->nik_ktp,
            'no_whatsapp' => $request->no_whatsapp,
            'foto_ktp' => $fotoPath,
        ]);

        session()->forget(['google_id', 'google_email', 'google_name']);

        Auth::login($user);
        return redirect('/dashboard')->with('success', 'Pendaftaran berhasil!');
    }
}