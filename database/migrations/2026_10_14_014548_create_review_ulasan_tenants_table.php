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
        Schema::create('review_ulasan_tenants', function (Blueprint $table) {
            $table->id('id_review');
            $table->unsignedBigInteger('id_akun_penyewa');
            $table->unsignedBigInteger('id_pemesanan');
            $table->unsignedBigInteger('id_tenant');
            $table->integer('rating_nilai');
            $table->text('komentar_text')->nullable();
            $table->timestamps(); // Termasuk created_at

            $table->foreign('id_akun_penyewa')->references('id_akun')->on('users')->onDelete('cascade');
            $table->foreign('id_pemesanan')->references('id_pemesanan')->on('detail_pemesanan_tenants')->onDelete('cascade');
            $table->foreign('id_tenant')->references('id_tenant')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_ulasan_tenants');
    }
};
