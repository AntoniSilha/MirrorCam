<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detail_pemesanan_tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'status_harga')) {
                $table->string('status_harga')->default('belum_lunas')->after('total_harga');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'nominal_dp')) {
                $table->decimal('nominal_dp', 12, 2)->default(0)->after('status_harga');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'sisa_tagihan')) {
                $table->decimal('sisa_tagihan', 12, 2)->default(0)->after('nominal_dp');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'is_payment_verified')) {
                $table->boolean('is_payment_verified')->default(false)->after('sisa_tagihan');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'payment_verified_at')) {
                $table->dateTime('payment_verified_at')->nullable()->after('is_payment_verified');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'actual_handover_at')) {
                $table->dateTime('actual_handover_at')->nullable()->after('waktu_kembali_booking');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'return_deadline')) {
                $table->dateTime('return_deadline')->nullable()->after('actual_handover_at');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'actual_returned_at')) {
                $table->dateTime('actual_returned_at')->nullable()->after('return_deadline');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'foto_kondisi_awal')) {
                $table->string('foto_kondisi_awal')->nullable()->after('actual_returned_at');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'foto_kondisi_akhir')) {
                $table->string('foto_kondisi_akhir')->nullable()->after('foto_kondisi_awal');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'kondisi_barang_kembali')) {
                $table->string('kondisi_barang_kembali')->nullable()->default('baik')->after('foto_kondisi_akhir');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'deskripsi_kerusakan')) {
                $table->text('deskripsi_kerusakan')->nullable()->after('kondisi_barang_kembali');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'estimasi_biaya_kerusakan')) {
                $table->decimal('estimasi_biaya_kerusakan', 12, 2)->default(0)->after('deskripsi_kerusakan');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'denda_keterlambatan')) {
                $table->decimal('denda_keterlambatan', 12, 2)->default(0)->after('estimasi_biaya_kerusakan');
            }
            if (! Schema::hasColumn('detail_pemesanan_tenants', 'checklist_data')) {
                $table->json('checklist_data')->nullable()->after('denda_keterlambatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_pemesanan_tenants', function (Blueprint $table) {
            $columns = [
                'status_harga',
                'nominal_dp',
                'sisa_tagihan',
                'is_payment_verified',
                'payment_verified_at',
                'actual_handover_at',
                'return_deadline',
                'actual_returned_at',
                'foto_kondisi_awal',
                'foto_kondisi_akhir',
                'kondisi_barang_kembali',
                'deskripsi_kerusakan',
                'estimasi_biaya_kerusakan',
                'denda_keterlambatan',
                'checklist_data',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('detail_pemesanan_tenants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
