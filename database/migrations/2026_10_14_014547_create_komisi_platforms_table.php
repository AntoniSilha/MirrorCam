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
        Schema::create('komisi_platforms', function (Blueprint $table) {
            $table->id('id_komisi');
            $table->unsignedBigInteger('id_pemesanan');
            $table->decimal('persentase_komisi_platform', 5, 2);
            $table->decimal('total_komisi', 12, 2);
            $table->timestamps();

            $table->foreign('id_pemesanan')->references('id_pemesanan')->on('detail_pemesanan_tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komisi_platforms');
    }
};
