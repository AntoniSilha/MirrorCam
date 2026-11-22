<?php

namespace App\Modules\TenantManagement\Concerns;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ManagesTenantProfile
{
    public $logoUpload = null;

    public ?string $currentLogoUrl = null;

    public string $tenantKode = '';

    public string $jamOperasionalText = '08.00 - 21.00 WIB (Senin - Minggu)';

    // 1. Informasi Toko & Legalitas
    public string $namaToko = '';

    public string $kategoriBisnis = 'Rental Kamera, Lensa & Lighting Studio';

    public string $sloganToko = '';

    public string $bioDeskripsi = '';

    // 2. Kontak & Penanggung Jawab Operasional (PIC)
    public string $namaPenanggungJawab = '';

    public string $jabatanPenanggungJawab = '';

    public string $emailResmi = '';

    public string $whatsappResmi = '';

    // 3. Lokasi Toko & Hub Penyerahan Unit
    public string $alamatToko = '';

    public string $koordinatLokasi = '';

    // 4. Rekening Bank Pencairan Saldo Sewa
    public bool $editingRekening = false;

    public string $namaBank = '';

    public string $nomorRekening = '';

    public string $namaPemilikRekening = '';

    abstract protected function tenant(): ?Tenant;

    protected function initTenantProfile(): void
    {
        $tenant = $this->tenant();

        if (! $tenant) {
            return;
        }

        $this->tenantKode = 'MCH-'.str_pad((string) $tenant->id_tenant, 4, '0', STR_PAD_LEFT);
        $this->namaToko = (string) ($tenant->nama_toko ?? '');
        $this->kategoriBisnis = (string) ($tenant->kategori_bisnis ?? 'Rental Kamera, Lensa & Lighting Studio');
        $this->sloganToko = (string) ($tenant->slogan ?? '');
        $this->bioDeskripsi = (string) ($tenant->bio_deskripsi ?? '');

        $this->namaPenanggungJawab = (string) ($tenant->nama_penanggungjawab ?? '');
        $this->jabatanPenanggungJawab = (string) ($tenant->jabatan_penanggungjawab ?? '');
        $this->emailResmi = (string) ($tenant->email_resmi ?? '');
        $this->whatsappResmi = (string) ($tenant->whatsapp_resmi ?? '');

        $this->alamatToko = (string) ($tenant->alamat_toko ?? '');
        $this->koordinatLokasi = (string) ($tenant->koordinat_lokasi ?? '');

        if ($tenant->url_logo) {
            $this->currentLogoUrl = Str::startsWith($tenant->url_logo, ['http://', 'https://'])
                ? $tenant->url_logo
                : asset('storage/'.ltrim($tenant->url_logo, '/'));
        }

        $ops = DB::table('detail_operasional_tenants')
            ->where('id_tenant', $tenant->id_tenant)
            ->first();

        if ($ops) {
            $buka = substr((string) $ops->jam_buka, 0, 5);
            $tutup = substr((string) $ops->jam_tutup, 0, 5);
            $this->jamOperasionalText = str_replace(':', '.', $buka).' - '.str_replace(':', '.', $tutup).' WIB ('.$ops->hari_operasional.')';
        }

        $rekening = DB::table('rekening_settlement_tenants')
            ->where('id_tenant', $tenant->id_tenant)
            ->first();

        if ($rekening) {
            $this->namaBank = (string) ($rekening->nama_bank ?? '');
            $this->nomorRekening = (string) ($rekening->nomor_rekening ?? '');
            $this->namaPemilikRekening = (string) ($rekening->nama_pemilik_rekening ?? '');
        }
    }

    public function updatedLogoUpload(): void
    {
        $this->validate([
            'logoUpload' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'logoUpload.image' => 'File logo harus berupa gambar.',
            'logoUpload.mimes' => 'Format logo harus JPG, PNG, atau WEBP.',
            'logoUpload.max' => 'Ukuran foto toko maksimal 2 MB.',
        ]);

        $tenant = $this->tenant();
        if (! $tenant) {
            return;
        }

        if ($tenant->url_logo && ! Str::startsWith($tenant->url_logo, ['http://', 'https://'])) {
            Storage::disk('public')->delete($tenant->url_logo);
        }

        $path = $this->logoUpload->store('tenants/logos', 'public');
        $tenant->update(['url_logo' => $path]);

        $this->currentLogoUrl = asset('storage/'.$path);
        $this->logoUpload = null;
        unset($this->dashboard);

        session()->flash('status', 'Foto profil toko berhasil diperbarui.');
    }

    public function toggleRekeningForm(): void
    {
        $this->editingRekening = ! $this->editingRekening;
    }

    public function saveRekening(): void
    {
        $this->validate([
            'namaBank' => ['required', 'string', 'max:50'],
            'nomorRekening' => ['required', 'string', 'max:50'],
            'namaPemilikRekening' => ['required', 'string', 'max:120'],
        ], [
            'namaBank.required' => 'Nama bank wajib diisi.',
            'nomorRekening.required' => 'Nomor rekening wajib diisi.',
            'namaPemilikRekening.required' => 'Nama pemilik rekening wajib diisi.',
        ]);

        $tenant = $this->tenant();
        if (! $tenant) {
            return;
        }

        DB::table('rekening_settlement_tenants')->updateOrInsert(
            ['id_tenant' => $tenant->id_tenant],
            [
                'nama_bank' => strtoupper(trim($this->namaBank)),
                'nomor_rekening' => trim($this->nomorRekening),
                'nama_pemilik_rekening' => strtoupper(trim($this->namaPemilikRekening)),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->editingRekening = false;
        unset($this->dashboard);

        session()->flash('status', 'Rekening bank pencairan saldo berhasil disimpan.');
    }

    public function saveTenantProfile(): void
    {
        $this->validate([
            'namaToko' => ['required', 'string', 'max:120'],
            'kategoriBisnis' => ['required', 'string', 'max:120'],
            'sloganToko' => ['nullable', 'string', 'max:150'],
            'bioDeskripsi' => ['nullable', 'string', 'max:300'],
            'namaPenanggungJawab' => ['required', 'string', 'max:120'],
            'jabatanPenanggungJawab' => ['required', 'string', 'max:100'],
            'emailResmi' => ['required', 'email', 'max:120'],
            'whatsappResmi' => ['required', 'string', 'max:30'],
            'alamatToko' => ['required', 'string', 'max:500'],
        ], [
            'namaToko.required' => 'Nama toko / rental wajib diisi.',
            'kategoriBisnis.required' => 'Kategori bisnis rental wajib dipilih.',
            'bioDeskripsi.max' => 'Deskripsi singkat maksimal 300 karakter.',
            'namaPenanggungJawab.required' => 'Nama lengkap pemilik / PIC wajib diisi.',
            'jabatanPenanggungJawab.required' => 'Jabatan wajib diisi.',
            'emailResmi.required' => 'Email notifikasi pesanan wajib diisi.',
            'emailResmi.email' => 'Format email tidak valid.',
            'whatsappResmi.required' => 'Nomor WhatsApp operasional wajib diisi.',
            'alamatToko.required' => 'Alamat offline tenant wajib diisi.',
        ]);

        $tenant = $this->tenant();

        if (! $tenant) {
            $this->addError('namaToko', 'Data tenant tidak ditemukan.');

            return;
        }

        $tenant->update([
            'nama_toko' => trim($this->namaToko),
            'kategori_bisnis' => trim($this->kategoriBisnis),
            'slogan' => trim($this->sloganToko) ?: null,
            'bio_deskripsi' => trim($this->bioDeskripsi) ?: null,
            'nama_penanggungjawab' => trim($this->namaPenanggungJawab),
            'jabatan_penanggungjawab' => trim($this->jabatanPenanggungJawab),
            'email_resmi' => trim($this->emailResmi),
            'whatsapp_resmi' => trim($this->whatsappResmi),
            'alamat_toko' => trim($this->alamatToko),
            'koordinat_lokasi' => trim($this->koordinatLokasi) ?: null,
        ]);

        unset($this->dashboard);

        session()->flash('status', 'Pengaturan profil toko berhasil disimpan ke database.');
    }
}
