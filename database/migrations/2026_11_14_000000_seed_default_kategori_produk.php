<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed kategori produk bawaan agar mitra dapat memilih kategori saat menambah produk.
     */
    public function up(): void
    {
        $categories = [
            'Camera',
            'Mic',
            'Handphone',
            'Sound',
            'Studio',
            'Lighting',
        ];

        foreach ($categories as $namaKategori) {
            $exists = DB::table('kategori_produk')
                ->where('nama_kategori', $namaKategori)
                ->exists();

            if (! $exists) {
                DB::table('kategori_produk')->insert([
                    'nama_kategori' => $namaKategori,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('kategori_produk')
            ->whereIn('nama_kategori', [
                'Camera',
                'Mic',
                'Handphone',
                'Sound',
                'Studio',
                'Lighting',
            ])
            ->delete();
    }
};
