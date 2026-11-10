<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterMitraRequest;
use App\Models\DetailOperasionalTenant;
use App\Models\RekeningSettlementTenant;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VerifikasiKtp;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MitraRegisterController extends Controller
{
    /**
     * Menampilkan form pendaftaran mitra
     */
    public function create(): View|RedirectResponse
    {
        $user = auth()->user();

        $existingTenant = Tenant::query()
            ->where('id_akun_pemilik', auth()->id())
            ->first();

        if ($existingTenant || $user?->role === 'mitra') {
            return redirect()->route('mitra.dashboard')
                ->with('status', 'Anda sudah mendaftarkan akun mitra. Silakan tunggu verifikasi admin.');
        }

        return view('pages.auth.register-mitra');
    }

    /**
     * Memproses formulir pendaftaran mitra
     */
    public function store(RegisterMitraRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->id_user_google || ! $user->email) {
            return back()
                ->withErrors(['account' => 'Akun Google Anda belum lengkap untuk mendaftar sebagai mitra.'])
                ->withInput();
        }

        if (Tenant::query()->where('id_akun_pemilik', $user->id_akun)->exists()) {
            return redirect()->route('mitra.dashboard')
                ->with('status', 'Anda sudah mendaftarkan akun mitra. Silakan tunggu verifikasi admin.');
        }

        $validated = $request->validated();
        $fotoKtpPath = $request->file('foto_ktp')->store('ktp_mitra', 'public');

        if ($fotoKtpPath === false) {
            throw new \RuntimeException('Gagal menyimpan foto KTP pendaftaran mitra.');
        }

        try {
            DB::transaction(function () use ($validated, $fotoKtpPath, $user): void {
                $tenant = Tenant::query()->create([
                    'google_id_token' => $user->id_user_google,
                    'id_akun_pemilik' => $user->id_akun,
                    'nama_toko' => $validated['nama_toko'],
                    'whatsapp_resmi' => $validated['whatsapp_toko'],
                    'nama_penanggungjawab' => $validated['nama_pengelola'],
                    'jabatan_penanggungjawab' => $validated['jabatan_pengelola'],
                    'email_resmi' => $user->email,
                    'alamat_toko' => $validated['alamat_lengkap'].', '.$validated['kota_operasional'],
                    'status_mitra' => 'Menunggu Verifikasi',
                ]);

                DetailOperasionalTenant::query()->create([
                    'id_tenant' => $tenant->id_tenant,
                    'jam_buka' => $validated['jam_buka'],
                    'jam_tutup' => $validated['jam_tutup'],
                    'hari_operasional' => $validated['hari_operasional'],
                ]);

                RekeningSettlementTenant::query()->create([
                    'id_tenant' => $tenant->id_tenant,
                    'nama_bank' => $validated['bank_name'],
                    'nomor_rekening' => $validated['nomor_rekening'],
                    'nama_pemilik_rekening' => $validated['nama_pemilik_rekening'],
                ]);

                VerifikasiKtp::query()->create([
                    'id_akun' => $user->id_akun,
                    'nik_ktp' => $validated['nik'],
                    'url_foto_ktp' => $fotoKtpPath,
                    'status_verifikasi' => 'Menunggu Verifikasi',
                ]);

                $user->forceFill(['role' => 'mitra'])->save();
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($fotoKtpPath);

            throw $exception;
        }

        return redirect()->route('mitra.dashboard')
            ->with('status', 'Pendaftaran mitra berhasil disimpan dan menunggu verifikasi.');
    }
}
