<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_akun');
            $table->string('id_user_google');
            $table->string('role');
            $table->string('nama_lengkap_ktp')->nullable();
            $table->string('nik_ktp')->nullable();
            $table->string('no_whatsapp')->nullable();
            $table->string('foto_ktp')->nullable();
            $table->timestamps();

            $table->foreign('id_user_google')->references('id_user_google')->on('account_google')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};