<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Section 01 & 02: Informasi Toko / Workshop
            $table->string('nama_toko');
            $table->text('alamat_lengkap');
            $table->string('kota_operasional');
            $table->string('whatsapp_toko');
            $table->time('jam_buka');
            $table->time('jam_tutup');

            // Section 03: Data Pengelola & Legalitas PIC
            $table->string('nama_pengelola');
            $table->string('nik', 16);
            $table->string('jabatan_pengelola');
            $table->string('foto_ktp_path');

            // Section 04: Rekening Settlement & Payout
            $table->string('bank_name');
            $table->string('nomor_rekening');
            $table->string('nama_pemilik_rekening');

            // Status Pengajuan & Verifikasi
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_penolakan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitras');
    }
};
