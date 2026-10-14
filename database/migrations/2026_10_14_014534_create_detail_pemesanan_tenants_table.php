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
        Schema::create('detail_pemesanan_tenants', function (Blueprint $table) {
            $table->id('id_pemesanan');
            $table->string('sub_order_eksternal');
            $table->unsignedBigInteger('id_tenant');
            $table->unsignedBigInteger('id_produk');
            $table->unsignedBigInteger('id_ktp');
            $table->string('url_foto_ktp');
            $table->dateTime('waktu_ambil_booking');
            $table->dateTime('waktu_kembali_booking');
            $table->integer('durasi_sewa_hari');
            $table->decimal('harga_sewa_booking', 12, 2);
            $table->decimal('total_harga', 12, 2);
            $table->string('status_sewa');
            $table->timestamps();

            $table->foreign('id_tenant')->references('id_tenant')->on('tenants')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('produks')->onDelete('cascade');
            $table->foreign('id_ktp')->references('id_ktp')->on('verifikasi_ktp')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pemesanan_tenants');
    }
};
