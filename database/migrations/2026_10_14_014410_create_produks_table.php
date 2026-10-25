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
        Schema::create('produks', function (Blueprint $table) {
            $table->id('id_produk');
            $table->unsignedBigInteger('id_tenant');
            $table->unsignedBigInteger('id_kategori');
            $table->string('nama_produk');
            $table->string('color_produk')->nullable();
            $table->text('deskripsi_lengkap');
            $table->decimal('harga_sewa', 12, 2);
            $table->integer('total_stok_unit');
            $table->timestamps();

            $table->foreign('id_tenant')->references('id_tenant')->on('tenants')->onDelete('cascade');
            $table->foreign('id_kategori')->references('id_kategori')->on('kategori_produk')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
