<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Kategori Produk (Master Data Kategori)
        $categoriesData = [
            ['id_kategori' => 1, 'nama_kategori' => 'Camera'],
            ['id_kategori' => 2, 'nama_kategori' => 'Mic'],
            ['id_kategori' => 3, 'nama_kategori' => 'Handphone'],
            ['id_kategori' => 4, 'nama_kategori' => 'Sound'],
            ['id_kategori' => 5, 'nama_kategori' => 'Studio'],
            ['id_kategori' => 6, 'nama_kategori' => 'Lighting'],
        ];

        foreach ($categoriesData as $cat) {
            DB::table('kategori_produk')->updateOrInsert(
                ['id_kategori' => $cat['id_kategori']],
                ['nama_kategori' => $cat['nama_kategori'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
