<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('komisi_platforms', function (Blueprint $table) {
            if (! Schema::hasColumn('komisi_platforms', 'kode_komisi')) {
                $table->string('kode_komisi', 30)->nullable()->after('id_komisi');
            }
            if (! Schema::hasColumn('komisi_platforms', 'status_komisi')) {
                $table->string('status_komisi', 30)->default('Menunggu')->after('total_komisi');
            }
            if (! Schema::hasColumn('komisi_platforms', 'metode_pembayaran')) {
                $table->string('metode_pembayaran', 50)->nullable()->after('status_komisi');
            }
            if (! Schema::hasColumn('komisi_platforms', 'referensi_pembayaran')) {
                $table->string('referensi_pembayaran', 80)->nullable()->after('metode_pembayaran');
            }
            if (! Schema::hasColumn('komisi_platforms', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('referensi_pembayaran');
            }
        });
    }

    public function down(): void
    {
        Schema::table('komisi_platforms', function (Blueprint $table) {
            $table->dropColumn([
                'kode_komisi',
                'status_komisi',
                'metode_pembayaran',
                'referensi_pembayaran',
                'paid_at',
            ]);
        });
    }
};
