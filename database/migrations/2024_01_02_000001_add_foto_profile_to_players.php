<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom foto_profile ke tabel players jika belum ada
        if (!Schema::hasColumn('players', 'foto_profile')) {
            Schema::table('players', function (Blueprint $table) {
                $table->string('foto_profile')->nullable()->after('avatar_color');
            });
        }
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('foto_profile');
        });
    }
};
