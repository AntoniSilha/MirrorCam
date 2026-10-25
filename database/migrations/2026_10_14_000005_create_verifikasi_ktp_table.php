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
        Schema::create('verifikasi_ktp', function (Blueprint $table) {
            $table->id('id_ktp');
            $table->unsignedBigInteger('id_akun');
            $table->string('nik_ktp');
            $table->string('url_foto_ktp');
            $table->string('status_verifikasi');
            $table->dateTime('waktu_verifikasi')->nullable();
            $table->timestamps();

            $table->foreign('id_akun')->references('id_akun')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi_ktps');
    }
};
