<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccountGoogle;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use thiagoalessio\TesseractOCR\TesseractOCR;

class GoogleRegistrationController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver->stateless()->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('google');
            /** @var SocialiteUser $googleUser */
            $googleUser = $driver->stateless()->user();

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

    public function storeDataProfile(Request $request): RedirectResponse
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
                ->executable((string) config('services.tesseract.path', '/usr/bin/tesseract'))
                ->lang('ind', 'eng')
                ->psm(11)
                ->run();

            if (empty(trim($ocrText))) {
                return back()->with('error', 'Tidak ada teks yang terbaca. Pastikan Anda mengunggah foto KTP asli yang jelas.')->withInput();
            }

            $ocrText = strtoupper($ocrText);

            if (! str_contains($ocrText, 'NIK') && ! str_contains($ocrText, 'PROVINSI')) {
                return back()->withErrors(['foto_ktp' => 'Teks standar KTP tidak terbaca. Pastikan foto KTP fisik jelas.'])->withInput();
            }

            $inputNikBersih = preg_replace('/[^0-9]/', '', $request->nik_ktp);

            if (empty($inputNikBersih) || strlen($inputNikBersih) < 16) {
                return back()->withErrors(['nik_ktp' => 'Format NIK yang Anda masukkan tidak valid (harus 16 angka).'])->withInput();
            }

            $ocrAllNumbers = $this->scanNikDigits($imagePath);

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

            $nikThreshold = (float) config('services.tesseract.nik_threshold', 80);

            if ($bestMatchPercentage < $nikThreshold) {
                return back()->withErrors([
                    'foto_ktp' => 'Data NIK pada foto tidak cukup cocok dengan yang diketik (Akurasi sistem: '.round($bestMatchPercentage).'%). Pastikan NIK benar dan foto KTP jelas.',
                ])->withInput();
            }

        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Sistem gagal memproses pemindaian KTP. Silakan coba lagi atau hubungi dukungan.')->withInput();
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

    private function scanNikDigits(string $imagePath): string
    {
        $ocrImagePath = $imagePath;
        $image = null;
        $temporaryImagePath = null;

        try {
            $imageInfo = getimagesize($imagePath);

            if (extension_loaded('gd') && $imageInfo !== false && $imageInfo[0] * $imageInfo[1] <= 2_500_000) {
                $imageData = file_get_contents($imagePath);
                $image = $imageData === false ? false : imagecreatefromstring($imageData);

                if ($image === false) {
                    throw new \RuntimeException('Unable to decode the uploaded KTP image for OCR.');
                }

                $resizedImage = imagescale($image, imagesx($image) * 2, imagesy($image) * 2, IMG_BICUBIC);

                if ($resizedImage === false) {
                    throw new \RuntimeException('Unable to resize the uploaded KTP image for OCR.');
                }

                imagedestroy($image);
                $image = $resizedImage;

                imagefilter($image, IMG_FILTER_GRAYSCALE);
                imagefilter($image, IMG_FILTER_CONTRAST, -20);

                if (! imageconvolution($image, [[-1, -1, -1], [-1, 16, -1], [-1, -1, -1]], 8, 0)) {
                    throw new \RuntimeException('Unable to sharpen the uploaded KTP image for OCR.');
                }

                $temporaryImagePath = tempnam(sys_get_temp_dir(), 'ktp-ocr-');

                if ($temporaryImagePath === false || ! imagepng($image, $temporaryImagePath)) {
                    throw new \RuntimeException('Unable to prepare the uploaded KTP image for OCR.');
                }

                $ocrImagePath = $temporaryImagePath;
            }

            return (new TesseractOCR($ocrImagePath))
                ->executable((string) config('services.tesseract.path', '/usr/bin/tesseract'))
                ->lang('ind', 'eng')
                ->psm(11)
                ->allowlist('0123456789')
                ->run();
        } finally {
            if ($image instanceof \GdImage) {
                imagedestroy($image);
            }

            if ($temporaryImagePath !== null && is_file($temporaryImagePath)) {
                unlink($temporaryImagePath);
            }
        }
    }
}
