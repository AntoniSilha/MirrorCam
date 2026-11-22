<?php

namespace App\Services\Mitra;

use App\Models\AuditLog;
use App\Models\DetailPemesananTenant;
use App\Models\Tenant;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderChecklistService
{
    /**
     * Dapatkan tenant terautentikasi dan kunci pesanan dengan lockForUpdate
     * untuk mencegah race condition (SRS NFR-DAT-10).
     */
    protected function lockTenantBooking(User $mitraUser, int $bookingId): array
    {
        $tenant = Tenant::query()
            ->where('id_akun_pemilik', $mitraUser->id_akun)
            ->first();

        if (! $tenant) {
            throw new AuthorizationException('Akun Anda tidak memiliki hak akses sebagai mitra tenant.');
        }

        $booking = DetailPemesananTenant::query()
            ->where('id_tenant', $tenant->id_tenant)
            ->where('id_pemesanan', $bookingId)
            ->lockForUpdate()
            ->first();

        if (! $booking) {
            throw new ModelNotFoundException('Pesanan tidak ditemukan atau tidak berada di bawah tenant Anda.');
        }

        return [$tenant, $booking];
    }

    /**
     * TAHAP 1: Ceklist Pembayaran Selesai (SRS UC-08 & FR-PAY-02)
     */
    public function verifyPayment(User $mitraUser, int $bookingId, array $data = []): DetailPemesananTenant
    {
        return DB::transaction(function () use ($mitraUser, $bookingId, $data) {
            /** @var Tenant $tenant */
            /** @var DetailPemesananTenant $booking */
            [$tenant, $booking] = $this->lockTenantBooking($mitraUser, $bookingId);

            $oldStatusHarga = $booking->status_harga;
            $now = Carbon::now();

            $booking->is_payment_verified = true;
            $booking->status_harga = 'lunas';
            $booking->sisa_tagihan = 0.00;
            $booking->payment_verified_at = $now;

            $checklist = $booking->checklist_data ?? [];
            $checklist['stage_1_pembayaran'] = [
                'verified_by_user_id' => $mitraUser->id_akun,
                'verified_by_name' => $mitraUser->name,
                'verified_at' => $now->toIso8601String(),
                'total_sewa' => (float) $booking->total_harga,
                'nominal_dp' => (float) $booking->nominal_dp,
                'sisa_tagihan_akhir' => 0.00,
                'catatan' => $data['catatan'] ?? 'Pelunasan pembayaran diverifikasi oleh mitra.',
            ];
            $booking->checklist_data = $checklist;
            $booking->save();

            AuditLog::create([
                'user_id' => $mitraUser->id_akun,
                'action' => 'CHECKLIST_PEMBAYARAN_SELESAI',
                'auditable_type' => DetailPemesananTenant::class,
                'auditable_id' => $booking->id_pemesanan,
                'description' => "Mitra [{$tenant->nama_toko}] memverifikasi pelunasan pembayaran pesanan [{$booking->booking_code}].",
                'details' => [
                    'tenant_id' => $tenant->id_tenant,
                    'booking_id' => $booking->id_pemesanan,
                    'booking_code' => $booking->booking_code,
                    'previous_status_harga' => $oldStatusHarga,
                    'new_status_harga' => 'lunas',
                    'is_payment_verified' => true,
                    'total_harga' => (float) $booking->total_harga,
                    'catatan' => $data['catatan'] ?? null,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => $now,
            ]);

            return $booking;
        });
    }

    /**
     * TAHAP 2: Ceklist Barang Digunakan / Serah Terima (SRS FR-TEN-03 / UC-10)
     */
    public function confirmHandover(User $mitraUser, int $bookingId, array $data): DetailPemesananTenant
    {
        return DB::transaction(function () use ($mitraUser, $bookingId, $data) {
            /** @var Tenant $tenant */
            /** @var DetailPemesananTenant $booking */
            [$tenant, $booking] = $this->lockTenantBooking($mitraUser, $bookingId);

            // Guard: Pembayaran wajib lunas sebelum serah terima unit
            if (! $booking->isPaymentComplete()) {
                throw new DomainException('Tahap 2 tidak dapat diselesaikan karena status pembayaran belum lunas.');
            }

            if (empty($data['identitas_terverifikasi'])) {
                throw new DomainException('Verifikasi identitas fisik penyewa wajib dicentang.');
            }

            if (empty($data['kondisi_fisik_awal_ok'])) {
                throw new DomainException('Verifikasi kelengkapan & kondisi fisik awal unit wajib dicentang.');
            }

            $now = Carbon::now();
            $durationDays = max(1, (int) ($booking->durasi_sewa_hari ?: 1));
            $returnDeadline = $now->copy()->addDays($durationDays);
            $oldStatusSewa = $booking->status_sewa;

            $booking->actual_handover_at = $now;
            $booking->return_deadline = $returnDeadline;
            $booking->status_sewa = 'sedang disewa';

            if (! empty($data['foto_kondisi_awal'])) {
                $booking->foto_kondisi_awal = $data['foto_kondisi_awal'];
            }

            $checklist = $booking->checklist_data ?? [];
            $checklist['stage_2_serah_terima'] = [
                'handover_by_user_id' => $mitraUser->id_akun,
                'handover_by_name' => $mitraUser->name,
                'actual_handover_at' => $now->toIso8601String(),
                'return_deadline' => $returnDeadline->toIso8601String(),
                'identitas_terverifikasi' => true,
                'kondisi_fisik_awal_ok' => true,
                'foto_kondisi_awal' => $booking->foto_kondisi_awal,
                'catatan' => $data['catatan'] ?? null,
            ];
            $booking->checklist_data = $checklist;
            $booking->save();

            AuditLog::create([
                'user_id' => $mitraUser->id_akun,
                'action' => 'CHECKLIST_BARANG_DIGUNAKAN',
                'auditable_type' => DetailPemesananTenant::class,
                'auditable_id' => $booking->id_pemesanan,
                'description' => "Mitra [{$tenant->nama_toko}] mengonfirmasi serah terima barang pesanan [{$booking->booking_code}]. Unit berstatus 'sedang disewa'.",
                'details' => [
                    'tenant_id' => $tenant->id_tenant,
                    'booking_id' => $booking->id_pemesanan,
                    'booking_code' => $booking->booking_code,
                    'previous_status_sewa' => $oldStatusSewa,
                    'new_status_sewa' => 'sedang disewa',
                    'actual_handover_at' => $now->toIso8601String(),
                    'return_deadline' => $returnDeadline->toIso8601String(),
                    'foto_kondisi_awal' => $booking->foto_kondisi_awal,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => $now,
            ]);

            return $booking;
        });
    }

    /**
     * TAHAP 3: Ceklist Penanda Peminjaman Selesai / Pengembalian (SRS FR-TEN-04 / UC-13 & UC-14)
     */
    public function confirmReturn(User $mitraUser, int $bookingId, array $data): DetailPemesananTenant
    {
        return DB::transaction(function () use ($mitraUser, $bookingId, $data) {
            /** @var Tenant $tenant */
            /** @var DetailPemesananTenant $booking */
            [$tenant, $booking] = $this->lockTenantBooking($mitraUser, $bookingId);

            // Guard: Hanya aktif jika pesanan berstatus sedang disewa
            if (! $booking->isInUse()) {
                throw new DomainException('Tahap 3 hanya dapat dilakukan apabila pesanan sudah berstatus sedang disewa (in_use).');
            }

            if (empty($data['kondisi_fisik_akhir_ok'])) {
                throw new DomainException('Pengecekan kelengkapan fisik akhir unit wajib dicentang.');
            }

            $now = Carbon::now();
            $dendaOtomatis = $booking->calculateAutomaticLateFine($now);
            $isDamaged = ($data['kondisi_kerusakan'] ?? 'baik') === 'rusak';
            $estimasiKlaim = $isDamaged ? max(0, (float) ($data['estimasi_biaya_kerusakan'] ?? 0)) : 0.00;
            $deskripsiKerusakan = $isDamaged ? trim((string) ($data['deskripsi_kerusakan'] ?? '')) : null;

            $oldStatusSewa = $booking->status_sewa;
            $newStatusSewa = $isDamaged ? 'disputed' : 'selesai';

            $booking->actual_returned_at = $now;
            $booking->denda_keterlambatan = $dendaOtomatis;
            $booking->kondisi_barang_kembali = $isDamaged ? 'rusak' : 'baik';
            $booking->deskripsi_kerusakan = $deskripsiKerusakan;
            $booking->estimasi_biaya_kerusakan = $estimasiKlaim;
            $booking->status_sewa = $newStatusSewa;

            if (! empty($data['foto_kondisi_akhir'])) {
                $booking->foto_kondisi_akhir = $data['foto_kondisi_akhir'];
            }

            $checklist = $booking->checklist_data ?? [];
            $checklist['stage_3_pengembalian'] = [
                'returned_by_user_id' => $mitraUser->id_akun,
                'returned_by_name' => $mitraUser->name,
                'actual_returned_at' => $now->toIso8601String(),
                'kondisi_fisik_akhir_ok' => true,
                'kondisi_barang_kembali' => $booking->kondisi_barang_kembali,
                'denda_keterlambatan' => $dendaOtomatis,
                'deskripsi_kerusakan' => $deskripsiKerusakan,
                'estimasi_biaya_kerusakan' => $estimasiKlaim,
                'foto_kondisi_akhir' => $booking->foto_kondisi_akhir,
                'status_akhir' => $newStatusSewa,
            ];
            $booking->checklist_data = $checklist;
            $booking->save();

            AuditLog::create([
                'user_id' => $mitraUser->id_akun,
                'action' => 'CHECKLIST_PENANDA_PEMINJAMAN_SELESAI',
                'auditable_type' => DetailPemesananTenant::class,
                'auditable_id' => $booking->id_pemesanan,
                'description' => "Mitra [{$tenant->nama_toko}] menyelesaikan peminjaman pesanan [{$booking->booking_code}]. Status menjadi '{$newStatusSewa}'."
                    . ($dendaOtomatis > 0 ? " Denda keterlambatan: Rp ".number_format($dendaOtomatis, 0, ',', '.') : '')
                    . ($isDamaged ? " Klaim kerusakan: Rp ".number_format($estimasiKlaim, 0, ',', '.') : ''),
                'details' => [
                    'tenant_id' => $tenant->id_tenant,
                    'booking_id' => $booking->id_pemesanan,
                    'booking_code' => $booking->booking_code,
                    'previous_status_sewa' => $oldStatusSewa,
                    'new_status_sewa' => $newStatusSewa,
                    'actual_returned_at' => $now->toIso8601String(),
                    'denda_keterlambatan' => $dendaOtomatis,
                    'kondisi_kerusakan' => $booking->kondisi_barang_kembali,
                    'deskripsi_kerusakan' => $deskripsiKerusakan,
                    'estimasi_biaya_kerusakan' => $estimasiKlaim,
                    'foto_kondisi_akhir' => $booking->foto_kondisi_akhir,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => $now,
            ]);

            return $booking;
        });
    }
}
