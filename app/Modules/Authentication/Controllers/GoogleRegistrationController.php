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
use thiagoalessio\TesseractOCR\TesseractOcrException;

class GoogleRegistrationController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        // 1. Bersihkan sisa sesi pendaftaran Google yang lama jika ada
        session()->forget(['google_id', 'google_email', 'google_name']);

        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');

        // 2. Tambahkan prompt select_account agar Google SELALU menampilkan layar pilih akun
        return $driver->stateless()
            ->with(['prompt' => 'select_account'])
            ->redirect();
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

        $inputNikBersih = (string) preg_replace('/[^0-9]/', '', (string) $request->nik_ktp);

        if (empty($inputNikBersih) || strlen($inputNikBersih) < 16) {
            return back()->withErrors(['nik_ktp' => 'Format NIK yang Anda masukkan tidak valid (harus 16 angka).'])->withInput();
        }

        try {
            $nikLength = strlen($inputNikBersih);
            $nikThreshold = (float) config('services.tesseract.nik_threshold', 80);

            // Coba dulu gambar asli (cepat). Ini yang biasanya terbaca untuk
            // foto yang justru rusak bila diproses (mis. KTP A).
            $ocrResults = $this->scanKtp($imagePath, false, true);
            $hasKtpMarker = $this->hasKtpMarker($ocrResults['text']);
            $bestMatchPercentage = $this->bestNikMatch($inputNikBersih, $ocrResults['digits']);

            // Jika belum lolos, coba varian hasil preprocessing (lebih lambat,
            // tetapi menolong foto yang baru terbaca setelah dipertajam).
            if (! $hasKtpMarker || $bestMatchPercentage < $nikThreshold) {
                $processedResults = $this->scanKtp($imagePath, true, ! $hasKtpMarker);

                $ocrResults['text'] .= ($ocrResults['text'] !== '' && $processedResults['text'] !== '' ? "\n" : '').$processedResults['text'];
                $ocrResults['digits'] = array_merge($ocrResults['digits'], $processedResults['digits']);
                $hasKtpMarker = $hasKtpMarker || $this->hasKtpMarker($processedResults['text']);
                $bestMatchPercentage = max($bestMatchPercentage, $this->bestNikMatch($inputNikBersih, $processedResults['digits']));
            }

            if (trim($ocrResults['text']) === '') {
                return back()->with('error', 'Tidak ada teks yang terbaca. Pastikan Anda mengunggah foto KTP asli yang jelas.')->withInput();
            }

            if (! $hasKtpMarker) {
                return back()->withErrors(['foto_ktp' => 'Teks standar KTP tidak terbaca. Pastikan foto KTP fisik jelas.'])->withInput();
            }

            $hasEnoughDigits = false;

            foreach ($ocrResults['digits'] as $ocrDigits) {
                if (strlen($ocrDigits) >= $nikLength) {
                    $hasEnoughDigits = true;
                    break;
                }
            }

            if (! $hasEnoughDigits) {
                return back()->withErrors(['foto_ktp' => 'Angka pada KTP tidak terbaca jelas oleh sistem. Pastikan foto terang dan fokus.'])->withInput();
            }

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
            'role' => 'user',
            'nama_lengkap_ktp' => $request->nama_lengkap_ktp,
            'nik_ktp' => $request->nik_ktp,
            'no_whatsapp' => $request->no_whatsapp,
            'foto_ktp' => $fotoPath,
        ]);

        session()->forget(['google_id', 'google_email', 'google_name']);

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Pendaftaran berhasil!');
    }

    /**
     * Jalankan OCR pada beberapa varian gambar dan beberapa mode segmentasi,
     * lalu gabungkan hasilnya. Pendekatan ini membuat satu foto yang gagal di
     * satu varian tetap bisa terbaca lewat varian lain.
     *
     * @return array{text: string, digits: list<string>}
     */
    private function scanKtp(string $imagePath, bool $processedOnly, bool $withText): array
    {
        $temporaryImagePaths = [];
        $texts = [];
        $digits = [];

        try {
            $variantPaths = $this->buildOcrVariants($imagePath, $temporaryImagePaths, $processedOnly);
            $pageSegmentationModes = $processedOnly ? [11] : [6, 11];

            foreach ($variantPaths as $variantPath) {
                foreach ($pageSegmentationModes as $psm) {
                    if ($withText) {
                        $text = $this->runTesseract($variantPath, $psm, false);

                        if (trim($text) !== '') {
                            $texts[] = $text;
                        }
                    }

                    $digitText = (string) preg_replace('/[^0-9]/', '', $this->runTesseract($variantPath, $psm, true));

                    if ($digitText !== '') {
                        $digits[] = $digitText;
                    }
                }
            }
        } finally {
            foreach ($temporaryImagePaths as $temporaryImagePath) {
                if (is_file($temporaryImagePath)) {
                    unlink($temporaryImagePath);
                }
            }
        }

        return ['text' => implode("\n", $texts), 'digits' => $digits];
    }

    /**
     * Hasilkan varian gambar untuk OCR. Jika $processedOnly false, hanya
     * gambar asli yang dipakai. Jika true, gambar di-resize, di-grayscale,
     * dan dipertajam untuk menolong foto yang kurang kontras.
     *
     * @param  list<string>  $temporaryImagePaths
     * @return list<string>
     */
    private function buildOcrVariants(string $imagePath, array &$temporaryImagePaths, bool $processedOnly): array
    {
        $variantPaths = [];
        $imageInfo = getimagesize($imagePath);

        if (! extension_loaded('gd') || $imageInfo === false || $imageInfo[0] * $imageInfo[1] > 2_500_000) {
            return $processedOnly ? $variantPaths : [$imagePath];
        }

        $imageData = file_get_contents($imagePath);
        $sourceImage = $imageData === false ? false : imagecreatefromstring($imageData);

        if ($sourceImage === false) {
            return $processedOnly ? $variantPaths : [$imagePath];
        }

        try {
            [$sourceImage, $rotated] = $this->applyExifOrientation($sourceImage, $imagePath);

            if (! $processedOnly) {
                if (! $rotated) {
                    return [$imagePath];
                }

                $temporaryImagePath = $this->saveTemporaryImage($sourceImage);

                if ($temporaryImagePath !== null) {
                    $variantPaths[] = $temporaryImagePath;
                    $temporaryImagePaths[] = $temporaryImagePath;
                }

                return $variantPaths;
            }

            $image = imagescale($sourceImage, imagesx($sourceImage) * 2, imagesy($sourceImage) * 2, IMG_BICUBIC);

            if ($image === false) {
                return $variantPaths;
            }

            imagefilter($image, IMG_FILTER_GRAYSCALE);
            imagefilter($image, IMG_FILTER_CONTRAST, -20);
            imageconvolution($image, [[-1, -1, -1], [-1, 16, -1], [-1, -1, -1]], 8, 0);

            $temporaryImagePath = $this->saveTemporaryImage($image);

            if ($temporaryImagePath !== null) {
                $variantPaths[] = $temporaryImagePath;
                $temporaryImagePaths[] = $temporaryImagePath;
            }

            imagedestroy($image);
        } finally {
            imagedestroy($sourceImage);
        }

        return $variantPaths;
    }

    /**
     * Putar gambar sesuai tag EXIF Orientation. Foto dari WhatsApp sering
     * tersimpan miring sehingga Tesseract membaca teksnya secara acak.
     *
     * @return array{0: \GdImage, 1: bool}
     */
    private function applyExifOrientation(\GdImage $image, string $imagePath): array
    {
        $orientation = 1;

        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($imagePath);
            $orientation = (int) ($exif['Orientation'] ?? 1);
        }

        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return [$image, false];
        }

        $rotatedImage = imagerotate($image, $angle, 0);

        if ($rotatedImage === false) {
            return [$image, false];
        }

        imagedestroy($image);

        return [$rotatedImage, true];
    }

    private function saveTemporaryImage(\GdImage $image): ?string
    {
        $temporaryImagePath = tempnam(sys_get_temp_dir(), 'ktp-ocr-');

        if ($temporaryImagePath === false) {
            return null;
        }

        if (imagepng($image, $temporaryImagePath)) {
            return $temporaryImagePath;
        }

        if (is_file($temporaryImagePath)) {
            unlink($temporaryImagePath);
        }

        return null;
    }

    private function hasKtpMarker(string $text): bool
    {
        $normalized = strtr(strtoupper($text), ['!' => 'I', '|' => 'I']);

        foreach (['NIK', 'PROVINSI', 'KABUPATEN', 'PENDUDUK'] as $marker) {
            if (str_contains($normalized, $marker)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $candidates
     */
    private function bestNikMatch(string $nik, array $candidates): float
    {
        $nikLength = strlen($nik);
        $bestMatchPercentage = 0.0;

        foreach ($candidates as $ocrDigits) {
            $ocrLength = strlen($ocrDigits);

            for ($i = 0; $i <= $ocrLength - $nikLength; $i++) {
                similar_text($nik, substr($ocrDigits, $i, $nikLength), $percent);

                if ($percent > $bestMatchPercentage) {
                    $bestMatchPercentage = $percent;
                }
            }
        }

        return $bestMatchPercentage;
    }

    private function runTesseract(string $imagePath, int $psm, bool $digitsOnly): string
    {
        $ocr = (new TesseractOCR($imagePath))
            ->executable((string) config('services.tesseract.path', '/usr/bin/tesseract'))
            ->lang('ind', 'eng')
            ->psm($psm);

        $tessdataDir = config('services.tesseract.tessdata_dir');

        if (is_string($tessdataDir) && $tessdataDir !== '') {
            $ocr->tessdataDir($tessdataDir);
        }

        if ($digitsOnly) {
            $ocr->allowlist('0123456789');
        }

        try {
            return $ocr->run();
        } catch (TesseractOcrException $exception) {
            return '';
        }
    }
}
