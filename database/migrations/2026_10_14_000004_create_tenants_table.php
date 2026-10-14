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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id('id_tenant');
            $table->string('google_id_token');
            $table->unsignedBigInteger('id_akun_pemilik');
            $table->string('nama_toko');
            $table->text('bio_deskripsi')->nullable();
            $table->string('url_logo')->nullable();
            $table->string('whatsapp_resmi');
            $table->string('nama_penanggungjawab');
            $table->string('jabatan_penanggungjawab');
            $table->string('email_resmi');
            $table->text('alamat_toko');
            $table->string('koordinat_lokasi')->nullable();
            $table->string('status_mitra');
            $table->timestamps();

            $table->foreign('google_id_token')->references('id_user_google')->on('account_google')->onDelete('cascade');
            $table->foreign('id_akun_pemilik')->references('id_akun')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
