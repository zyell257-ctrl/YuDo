<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            if (!Schema::hasColumn('matches', 'bukti_foto_pertandingan')) {
                $table->string('bukti_foto_pertandingan')->nullable()->after('status_match');
            }

            if (!Schema::hasColumn('matches', 'finished_at')) {
                $table->timestamp('finished_at')->nullable()->after('waktu_selesai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            if (Schema::hasColumn('matches', 'bukti_foto_pertandingan')) {
                $table->dropColumn('bukti_foto_pertandingan');
            }

            if (Schema::hasColumn('matches', 'finished_at')) {
                $table->dropColumn('finished_at');
            }
        });
    }
};
