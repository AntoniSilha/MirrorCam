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
        Schema::create('detail_operasional_tenants', function (Blueprint $table) {
            $table->id('id_operasional');
            $table->unsignedBigInteger('id_tenant');
            $table->time('jam_buka');
            $table->time('jam_tutup');
            $table->string('hari_operasional');
            $table->timestamps();

            $table->foreign('id_tenant')->references('id_tenant')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_operasional_tenants');
    }
};
