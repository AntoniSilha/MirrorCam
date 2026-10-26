<?php

namespace App\Http\Controllers;

use App\Models\AccountGoogle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
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

            $accountGoogle = AccountGoogle::firstOrCreate(
                ['id_user_google' => $googleUser->id],
                ['email' => $googleUser->email]
            );

            $user = User::where('id_user_google', $googleUser->id)->first();

            if ($user) {
                Auth::login($user);

                return redirect()->intended('/dashboard');
            } else {
                session([
                    'google_id' => $googleUser->id,
                    'google_email' => $googleUser->email,
                    'google_name' => $googleUser->name,
                ]);

                return redirect()->route('complete.profile');
            }

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Terjadi kesalahan saat otentikasi Google.');
        }
    }

    public function storeDataProfile(Request $request)
    {
        if (! session()->has('google_id')) {
            return redirect('/login')->with('error', 'Sesi pendaftaran habis, silakan ulangi.');
        }

        $request->validate([
            'nama_lengkap_ktp' => 'required|string|max:255',
            'nik_ktp' => 'required|string|max:16|unique:users',
            'no_whatsapp' => 'required|string|max:20',
            'foto_ktp' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $imagePath = $request->file('foto_ktp')->getPathName();

        try {
            $ocrText = (new TesseractOCR($imagePath))
                ->executable(env('TESSERACT_PATH', '/usr/bin/tesseract'))
                ->lang('ind', 'eng')
                ->run();

            if (empty(trim($ocrText))) {
                return back()->with('error', 'Tidak ada teks yang terbaca. Pastikan Anda mengunggah foto KTP asli yang jelas.')->withInput();
            }

            $ocrText = strtoupper($ocrText);

            if (! str_contains($ocrText, 'NIK') && ! str_contains($ocrText, 'PROVINSI')) {
                return back()->withErrors(['foto_ktp' => 'Teks standar KTP tidak terbaca. Pastikan foto KTP fisik jelas.'])->withInput();
            }

            // Normalisasi Input User
            $inputNikBersih = preg_replace('/[^0-9]/', '', $request->nik_ktp);

            // 1. Perbaiki kesalahan baca OCR yang umum (Tambahan D => 0, A => 4)
            $ocrTextFixed = strtr($ocrText, [
                'O' => '0', 'D' => '0',
                'I' => '1', 'L' => '1',
                'Z' => '2',
                'A' => '4',
                'S' => '5',
                'G' => '6',
                'B' => '8'
            ]);

            $ocrAllNumbers = preg_replace('/[^0-9]/', '', $ocrTextFixed);

            if (empty($inputNikBersih) || strlen($inputNikBersih) < 16) {
                return back()->withErrors(['nik_ktp' => 'Format NIK yang Anda masukkan tidak valid (harus 16 angka).'])->withInput();
            }

            if (strlen($ocrAllNumbers) < 16) {
                return back()->withErrors(['foto_ktp' => 'Angka pada KTP tidak terbaca jelas oleh sistem. Pastikan foto terang dan fokus.'])->withInput();
            }

            $bestMatchPercentage = 0;
            $nikLength = strlen($inputNikBersih);
            $ocrLen = strlen($ocrAllNumbers);

            for ($i = 0; $i <= $ocrLen - $nikLength; $i++) {
                $substring = substr($ocrAllNumbers, $i, $nikLength);
                similar_text($inputNikBersih, $substring, $percent);
                if ($percent > $bestMatchPercentage) {
                    $bestMatchPercentage = $percent;
                }
            }

            if ($bestMatchPercentage < 80) {
                return back()->withErrors([
                    'foto_ktp' => "Data NIK pada foto tidak cukup cocok dengan yang diketik (Akurasi sistem: " . round($bestMatchPercentage) . "%). Pastikan NIK benar dan foto KTP jelas."
                ])->withInput();
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memindai gambar KTP. Pastikan file bukan gambar kosong atau buram.')->withInput();
        }

        $fotoPath = $request->file('foto_ktp')->store('ktp', 'public');

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