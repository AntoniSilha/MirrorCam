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
        Schema::create('pengaduan_pelanggans', function (Blueprint $table) {
            $table->id('id_pengaduan');
            $table->unsignedBigInteger('id_akun');
            $table->unsignedBigInteger('id_tenant');
            $table->unsignedBigInteger('id_pemesanan');
            $table->string('kategori_pelanggaran');
            $table->text('kronologi_kejadian');
            $table->string('url_bukti_dokumen')->nullable();
            $table->string('status_kasus');
            $table->timestamps(); // Termasuk created_at

            $table->foreign('id_akun')->references('id_akun')->on('users')->onDelete('cascade');
            $table->foreign('id_tenant')->references('id_tenant')->on('tenants')->onDelete('cascade');
            $table->foreign('id_pemesanan')->references('id_pemesanan')->on('detail_pemesanan_tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduan_pelanggans');
    }
};
