<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('produks', 'harga_sewa')) {
            if (Schema::hasTable('durasi_produks')) {
                DB::table('produks')
                    ->leftJoin('durasi_produks', 'produks.id_produk', '=', 'durasi_produks.id_produk')
                    ->whereNull('durasi_produks.id_durasi')
                    ->select('produks.id_produk', 'produks.harga_sewa')
                    ->orderBy('produks.id_produk')
                    ->get()
                    ->each(function ($product): void {
                        DB::table('durasi_produks')->insert([
                            'id_produk' => $product->id_produk,
                            'durasi' => '1_hari',
                            'nama_durasi' => '1 Hari',
                            'harga' => $product->harga_sewa,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    });
            }

            Schema::table('produks', function (Blueprint $table) {
                $table->dropColumn('harga_sewa');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('produks', 'harga_sewa')) {
            Schema::table('produks', function (Blueprint $table) {
                $table->decimal('harga_sewa', 12, 2)->default(0);
            });
        }
    }
};
