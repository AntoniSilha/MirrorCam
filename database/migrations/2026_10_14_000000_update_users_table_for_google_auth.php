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
        if (! $this->hasLegacyUsersSchema()) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->index()->after('id');
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->hasLegacyUsersSchema()) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['google_id']);
            $table->dropColumn('google_id');
            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }
            $table->string('password')->nullable(false)->change();
        });
    }

    private function hasLegacyUsersSchema(): bool
    {
        return Schema::hasTable('users')
            && Schema::hasColumn('users', 'id')
            && Schema::hasColumn('users', 'email')
            && Schema::hasColumn('users', 'password');
    }
};
