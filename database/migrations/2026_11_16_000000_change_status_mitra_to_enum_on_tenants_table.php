<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tenants')
            ->whereIn('status_mitra', ['aktif', 'Aktif', 'terverifikasi', 'Terverifikasi', 'verified', 'official'])
            ->update(['status_mitra' => 'verified']);

        DB::table('tenants')
            ->whereNotIn('status_mitra', ['verified'])
            ->update(['status_mitra' => 'noverified']);

        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('status_mitra', ['verified', 'noverified'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('status_mitra')->change();
        });

        DB::table('tenants')->where('status_mitra', 'verified')->update(['status_mitra' => 'aktif']);
        DB::table('tenants')->where('status_mitra', 'noverified')->update(['status_mitra' => 'Menunggu Verifikasi']);
    }
};
