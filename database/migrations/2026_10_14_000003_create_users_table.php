<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'id_akun') && Schema::hasColumn('users', 'id')) {
                    $table->renameColumn('id', 'id_akun');
                }
                if (! Schema::hasColumn('users', 'id_user_google')) {
                    $table->string('id_user_google')->nullable();
                }
                if (! Schema::hasColumn('users', 'role')) {
                    $table->string('role')->default('customer');
                }
                if (! Schema::hasColumn('users', 'nama_lengkap_ktp')) {
                    $table->string('nama_lengkap_ktp')->nullable();
                }
                if (! Schema::hasColumn('users', 'nik_ktp')) {
                    $table->string('nik_ktp')->nullable();
                }
                if (! Schema::hasColumn('users', 'no_whatsapp')) {
                    $table->string('no_whatsapp')->nullable();
                }
                if (! Schema::hasColumn('users', 'foto_ktp')) {
                    $table->string('foto_ktp')->nullable();
                }
            });
        } else {
            Schema::create('users', function (Blueprint $table) {
                $table->id('id_akun');
                $table->string('id_user_google')->nullable();
                $table->string('role')->default('customer');
                $table->string('nama_lengkap_ktp')->nullable();
                $table->string('nik_ktp')->nullable();
                $table->string('no_whatsapp')->nullable();
                $table->string('foto_ktp')->nullable();
                $table->timestamps();
            });
        }
    }
    public function down(): void {
        // Keep users table intact
    }
};