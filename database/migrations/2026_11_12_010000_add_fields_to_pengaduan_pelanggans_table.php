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
        Schema::table('pengaduan_pelanggans', function (Blueprint $table) {
            $table->string('nomor_tiket')->nullable()->after('id_pengaduan');
            $table->dateTime('waktu_kejadian')->nullable()->after('kategori_pelanggaran');
            $table->dateTime('sla_tenggat')->nullable()->after('waktu_kejadian');
            $table->decimal('estimasi_klaim', 12, 2)->nullable()->after('sla_tenggat');
            $table->string('status_klaim')->nullable()->after('status_kasus');
            $table->text('pesan_peringatan')->nullable()->after('status_klaim');
            $table->text('catatan_resolusi')->nullable()->after('pesan_peringatan');
            $table->string('evaluasi_kepuasan')->nullable()->after('catatan_resolusi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaduan_pelanggans', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_tiket',
                'waktu_kejadian',
                'sla_tenggat',
                'estimasi_klaim',
                'status_klaim',
                'pesan_peringatan',
                'catatan_resolusi',
                'evaluasi_kepuasan',
            ]);
        });
    }
};
