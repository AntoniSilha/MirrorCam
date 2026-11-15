<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ganti penyimpanan durasi sewa dari kolom JSON menjadi tabel terpisah
     * agar setiap durasi dapat memiliki harga sendiri.
     */
    public function up(): void
    {
        Schema::create('durasi_produks', function (Blueprint $table) {
            $table->id('id_durasi');
            $table->unsignedBigInteger('id_produk');
            $table->string('durasi', 30);
            $table->string('nama_durasi', 50);
            $table->decimal('harga', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['id_produk', 'durasi']);
            $table->foreign('id_produk')->references('id_produk')->on('produks')->onDelete('cascade');
        });

        if (Schema::hasColumn('produks', 'durasi_sewa')) {
            Schema::table('produks', function (Blueprint $table) {
                $table->dropColumn('durasi_sewa');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('durasi_produks');

        if (! Schema::hasColumn('produks', 'durasi_sewa')) {
            Schema::table('produks', function (Blueprint $table) {
                $table->json('durasi_sewa')->nullable()->after('color_produk');
            });
        }
    }
};
