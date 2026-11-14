<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndonesiaGallerySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil Tenant "Indonesia Gallery" yang sudah masuk di tabel tenants
        $tenantId = DB::table('tenants')->where('nama_toko', 'Indonesia Gallery')->value('id_tenant')
            ?? DB::table('tenants')->value('id_tenant');

        // 2. Ambil atau buat 5 Kategori Produk
        $kategoris = [
            'cinema'     => 'Cinema Cameras',
            'mirrorless' => 'Mirrorless Hybrid',
            'prime'      => 'Cine Primes & Zoom',
            'lighting'   => 'Lighting & Grip',
            'audio'      => 'Audio & Wireless',
        ];

        $katIds = [];
        foreach ($kategoris as $key => $nama) {
            $existingId = DB::table('kategori_produk')->where('nama_kategori', $nama)->value('id_kategori');
            $katIds[$key] = $existingId ?? DB::table('kategori_produk')->insertGetId([
                'nama_kategori' => $nama,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // 3. Insert 8 Produk Nyata dengan kolom 'total_stock_unit' & Galeri Fotonya
        $items = [
            [
                'id_kategori'       => $katIds['cinema'],
                'nama_produk'       => 'Sony FX3 Cinema Line Full-Frame',
                'color_produk'      => 'Black',
                'deskripsi_lengkap' => 'Kamera sinema full-frame ringkas sensor Exmor R 10.2MP, perekaman internal 4K 120p 10-bit 4:2:2, Dual Base ISO 800/12800, S-Cinetone.',
                'harga_sewa'        => 550000,
                'total_stock_unit'  => 4,
                'foto'              => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id_kategori'       => $katIds['prime'],
                'nama_produk'       => 'Sony FE 24-70mm f/2.8 GM II',
                'color_produk'      => 'Black',
                'deskripsi_lengkap' => 'Lensa zoom standar G Master generasi kedua dengan 4 motor linear XD, aperture de-click ring, dan bukaan konstan f/2.8.',
                'harga_sewa'        => 250000,
                'total_stock_unit'  => 5,
                'foto'              => 'https://images.unsplash.com/photo-1617005082133-548c4dd27f35?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id_kategori'       => $katIds['mirrorless'],
                'nama_produk'       => 'Canon EOS R6 Mark II (Body)',
                'color_produk'      => 'Black',
                'deskripsi_lengkap' => 'Kamera mirrorless hybrid full-frame 24.2MP dengan Dual Pixel CMOS AF II dan video 4K 60p oversampled.',
                'harga_sewa'        => 400000,
                'total_stock_unit'  => 3,
                'foto'              => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id_kategori'       => $katIds['mirrorless'],
                'nama_produk'       => 'Sony Alpha 7 IV (A7 IV) Body',
                'color_produk'      => 'Black',
                'deskripsi_lengkap' => 'Kamera hybrid 33MP Exmor R CMOS dengan prosesor BIONZ XR, Real-time Eye AF, dan perekaman 4K 60p 10-bit.',
                'harga_sewa'        => 350000,
                'total_stock_unit'  => 6,
                'foto'              => 'https://images.unsplash.com/photo-1519183071298-a2962feb14f4?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id_kategori'       => $katIds['lighting'],
                'nama_produk'       => 'Foto Studio Sun Gallery',
                'color_produk'      => 'Space Gray',
                'deskripsi_lengkap' => 'Fasilitas suite pasca-produksi & color grading studio lengkap dengan monitor kalibrasi 10-bit dan workstation.',
                'harga_sewa'        => 150000,
                'total_stock_unit'  => 2,
                'foto'              => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id_kategori'       => $katIds['lighting'],
                'nama_produk'       => 'Aputure LS 600d Pro + Light Dome II',
                'color_produk'      => 'Silver / Black',
                'deskripsi_lengkap' => 'Lampu COB daylight 600W weather-resistant dengan kontrol Sidus Link, DMX512, dan softbox Light Dome II.',
                'harga_sewa'        => 375000,
                'total_stock_unit'  => 4,
                'foto'              => 'https://images.unsplash.com/photo-1527011046414-4781f1f94f8c?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id_kategori'       => $katIds['lighting'],
                'nama_produk'       => 'DJI Ronin RS 3 Pro Gimbal Combo',
                'color_produk'      => 'Black',
                'deskripsi_lengkap' => 'Gimbal stabilizer lengan karbon payload 4.5kg, automated axis locks, dan transmisi video RavenEye.',
                'harga_sewa'        => 220000,
                'total_stock_unit'  => 5,
                'foto'              => 'https://images.unsplash.com/photo-1589872307379-0ffdf9829123?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id_kategori'       => $katIds['prime'],
                'nama_produk'       => 'Canon RF 50mm f/1.2 L USM',
                'color_produk'      => 'Black',
                'deskripsi_lengkap' => 'Lensa prime L-series ultra-cepat dengan bukaan f/1.2 untuk bokeh sinematik dan ketajaman maksimal.',
                'harga_sewa'        => 280000,
                'total_stock_unit'  => 1,
                'foto'              => 'https://images.unsplash.com/photo-1617005082133-548c4dd27f35?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        foreach ($items as $item) {
            $foto = $item['foto'];
            unset($item['foto']);

            $produkId = DB::table('produks')->insertGetId(array_merge($item, [
                'id_tenant'  => $tenantId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            DB::table('galeri_foto_produks')->insert([
                'id_produk'       => $produkId,
                'url_foto_produk' => $foto,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}
