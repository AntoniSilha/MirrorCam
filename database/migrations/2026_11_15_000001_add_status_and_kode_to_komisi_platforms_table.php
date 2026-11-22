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
        Schema::table('komisi_platforms', function (Blueprint $table) {
            if (! Schema::hasColumn('komisi_platforms', 'kode_komisi')) {
                $table->string('kode_komisi', 50)->nullable()->after('id_komisi');
            }
            if (! Schema::hasColumn('komisi_platforms', 'status_komisi')) {
                $table->string('status_komisi', 30)->default('Aktif')->after('total_komisi');
            }
            if (! Schema::hasColumn('komisi_platforms', 'catatan_verifikasi')) {
                $table->text('catatan_verifikasi')->nullable()->after('status_komisi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('komisi_platforms', function (Blueprint $table) {
            $table->dropColumn(['kode_komisi', 'status_komisi', 'catatan_verifikasi']);
        });
    }
};
