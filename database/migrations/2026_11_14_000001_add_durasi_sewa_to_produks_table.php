<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan opsi durasi sewa yang tersedia pada setiap produk.
     */
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->json('durasi_sewa')->nullable()->after('color_produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn('durasi_sewa');
        });
    }
};
