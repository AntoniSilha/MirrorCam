<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('kategori_bisnis')->default('Rental Kamera, Lensa & Lighting Studio')->after('nama_toko');
            $table->string('slogan')->nullable()->after('kategori_bisnis');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['kategori_bisnis', 'slogan']);
        });
    }
};
