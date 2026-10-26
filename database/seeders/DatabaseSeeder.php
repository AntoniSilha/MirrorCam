<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $googleUser = \App\Models\AccountGoogle::firstOrCreate(
            ['id_user_google' => 'google_antoni_01'],
            ['email' => 'antoni@mirrorcam.com']
        );

        $user = User::firstOrCreate(
            ['email' => 'antoni@mirrorcam.com'],
            [
                'name' => 'Antoni Silha',
                'password' => 'Password123!',
                'email_verified_at' => now(),
                'id_user_google' => 'google_antoni_01',
                'role' => 'partner',
            ]
        );

        // 1. Kategori Produk
        $categoriesData = [
            ['id_kategori' => 1, 'nama_kategori' => 'Kamera Cinema & Mirrorless'],
            ['id_kategori' => 2, 'nama_kategori' => 'Lensa Prime & Cine'],
            ['id_kategori' => 3, 'nama_kategori' => 'Lighting & Rigging'],
            ['id_kategori' => 4, 'nama_kategori' => 'Audio & Monitor'],
            ['id_kategori' => 5, 'nama_kategori' => 'Drone & Gimbal'],
            ['id_kategori' => 6, 'nama_kategori' => 'Studio'],
        ];

        foreach ($categoriesData as $cat) {
            \Illuminate\Support\Facades\DB::table('kategori_produk')->updateOrInsert(
                ['id_kategori' => $cat['id_kategori']],
                ['nama_kategori' => $cat['nama_kategori'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 2. Tenants Mitra Resmi
        $tenantsData = [
            [
                'id_tenant' => 1,
                'google_id_token' => 'google_antoni_01',
                'id_akun_pemilik' => $user->id_akun ?? 1,
                'nama_toko' => 'Indonesia Battery',
                'bio_deskripsi' => 'Penyedia gear shooting terpercaya di Jawa Timur, spesialis cinema camera Sony FX series & Zeiss lenses.',
                'url_logo' => null,
                'whatsapp_resmi' => '6281234567890',
                'nama_penanggungjawab' => 'Budi Santoso',
                'jabatan_penanggungjawab' => 'Founder & Rental Lead',
                'email_resmi' => 'indonesiabattery@gmail.com',
                'alamat_toko' => 'Jl. Ahmad Yani No. 45, Banyuwangi',
                'koordinat_lokasi' => '-8.2192, 114.3692',
                'status_mitra' => 'official',
            ],
            [
                'id_tenant' => 2,
                'google_id_token' => 'google_antoni_01',
                'id_akun_pemilik' => $user->id_akun ?? 1,
                'nama_toko' => 'Studio Proyek ID',
                'bio_deskripsi' => 'Pusat sewa kamera mirrorless, rigging stabilizer, & sound recorder profesional untuk kebutuhan video komersial.',
                'url_logo' => null,
                'whatsapp_resmi' => '6281987654321',
                'nama_penanggungjawab' => 'Dimas Prakoso',
                'jabatan_penanggungjawab' => 'Operations Lead',
                'email_resmi' => 'studioproyek@gmail.com',
                'alamat_toko' => 'Ruko Darmo Park II, Surabaya',
                'koordinat_lokasi' => '-7.2891, 112.7344',
                'status_mitra' => 'official',
            ],
            [
                'id_tenant' => 3,
                'google_id_token' => 'google_antoni_01',
                'id_akun_pemilik' => $user->id_akun ?? 1,
                'nama_toko' => 'Koo Gallery',
                'bio_deskripsi' => 'Rental kamera & studio modern terlengkap di Solo dan sekitarnya. Menyediakan paket lighting studio, flash wireless, dan kamera pro.',
                'url_logo' => null,
                'whatsapp_resmi' => '6281122334455',
                'nama_penanggungjawab' => 'Kevin Koo',
                'jabatan_penanggungjawab' => 'Managing Director',
                'email_resmi' => 'koogallery@gmail.com',
                'alamat_toko' => 'Jl. Slamet Riyadi No. 120, Bandung',
                'koordinat_lokasi' => '-6.9175, 107.6191',
                'status_mitra' => 'official',
            ],
        ];

        foreach ($tenantsData as $tenant) {
            \Illuminate\Support\Facades\DB::table('tenants')->updateOrInsert(
                ['id_tenant' => $tenant['id_tenant']],
                $tenant + ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 3. Produks Rekomendasi Gear
        $productsData = [
            [
                'id_produk' => 1,
                'id_tenant' => 1,
                'id_kategori' => 1,
                'nama_produk' => 'Sony FX30 Cinema Line Full...',
                'color_produk' => 'Hitam Matte',
                'deskripsi_lengkap' => 'Kamera cinema line dengan sensor Super 35 4K 120fps, profil warna S-Cinetone, dan dual base ISO.',
                'harga_sewa' => 550000.00,
                'total_stok_unit' => 5,
                'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'id_produk' => 2,
                'id_tenant' => 1,
                'id_kategori' => 2,
                'nama_produk' => 'Sony FE 24–70 mm f/2.8 GM II',
                'color_produk' => 'Hitam',
                'deskripsi_lengkap' => 'Lensa zoom standar profesional G Master generasi kedua dengan resolusi optik luar biasa dan bobot lebih ringan.',
                'harga_sewa' => 250000.00,
                'total_stok_unit' => 8,
                'image' => 'https://images.unsplash.com/photo-1617005082133-548c4dd27f35?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'id_produk' => 3,
                'id_tenant' => 3,
                'id_kategori' => 1,
                'nama_produk' => 'Canon EOS R6 Mark II (Body)',
                'color_produk' => 'Hitam',
                'deskripsi_lengkap' => 'Kamera mirrorless full-frame 24.2MP dengan kecepatan burst hingga 40 fps dan rekaman 4K 60p oversampled.',
                'harga_sewa' => 400000.00,
                'total_stok_unit' => 4,
                'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'id_produk' => 4,
                'id_tenant' => 2,
                'id_kategori' => 1,
                'nama_produk' => 'Sony Alpha 7 IV (A7 IV) Body',
                'color_produk' => 'Hitam',
                'deskripsi_lengkap' => 'Kamera hybrid 33MP dengan sensor Exmor R BSI dan prosesor BIONZ XR untuk foto dan video profesional.',
                'harga_sewa' => 350000.00,
                'total_stok_unit' => 6,
                'image' => 'https://images.unsplash.com/photo-1512790182412-b19e6d62bc39?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'id_produk' => 5,
                'id_tenant' => 3,
                'id_kategori' => 6,
                'nama_produk' => 'Sewa Studio Sun Gallery',
                'color_produk' => 'Putih Studio',
                'deskripsi_lengkap' => 'Ruang studio foto & syuting lengkap dengan cyclorama wall, make up room, lighting ceiling, dan AC 2 PK.',
                'harga_sewa' => 150000.00,
                'total_stok_unit' => 2,
                'image' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'id_produk' => 6,
                'id_tenant' => 2,
                'id_kategori' => 3,
                'nama_produk' => 'Aputure LS 600d Pro + Light...',
                'color_produk' => 'Hitam Abu',
                'deskripsi_lengkap' => 'Lampu LED daylight continuous point-source 600W dengan Bowens mount, Weather-resistant, dan wireless control.',
                'harga_sewa' => 375000.00,
                'total_stok_unit' => 3,
                'image' => 'https://images.unsplash.com/photo-1524368535928-5b5e00ddc76b?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'id_produk' => 7,
                'id_tenant' => 2,
                'id_kategori' => 5,
                'nama_produk' => 'DJI Ronin RS 3 Pro Gimbal...',
                'color_produk' => 'Hitam Karbon',
                'deskripsi_lengkap' => 'Gimbal stabilizer 3-axis dengan lengan serat karbon, motor focus LiDAR, dan kapasitas beban hingga 4.5 kg.',
                'harga_sewa' => 220000.00,
                'total_stok_unit' => 5,
                'image' => 'https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'id_produk' => 8,
                'id_tenant' => 3,
                'id_kategori' => 2,
                'nama_produk' => 'Canon RF 50mm f/1.2L USM...',
                'color_produk' => 'Hitam',
                'deskripsi_lengkap' => 'Lensa prime legendaris seri L untuk mount Canon RF dengan aperture f/1.2 ultra-terang dan bokeh lembut sinematik.',
                'harga_sewa' => 280000.00,
                'total_stok_unit' => 7,
                'image' => 'https://images.unsplash.com/photo-1622818451877-c999331f4a9b?auto=format&fit=crop&w=600&q=80',
            ],
        ];

        foreach ($productsData as $prod) {
            $image = $prod['image'];
            unset($prod['image']);

            \Illuminate\Support\Facades\DB::table('produks')->updateOrInsert(
                ['id_produk' => $prod['id_produk']],
                $prod + ['created_at' => now(), 'updated_at' => now()]
            );

            // 4. Galeri Foto Produk
            \Illuminate\Support\Facades\DB::table('galeri_foto_produks')->updateOrInsert(
                ['id_produk' => $prod['id_produk']],
                [
                    'url_foto_produk' => $image,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
