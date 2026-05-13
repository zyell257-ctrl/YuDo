<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel absensi pemain
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->enum('status_hadir', ['hadir', 'tidak_hadir'])->default('tidak_hadir');
            $table->date('tanggal');
            $table->timestamps();

            // Satu pemain hanya bisa satu absensi per hari
            $table->unique(['player_id', 'tanggal']);
        });

        // Tabel pertandingan
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_match');
            $table->integer('nomor_match')->default(1); // urutan pertandingan di hari itu
            $table->enum('status_match', ['berlangsung', 'selesai'])->default('berlangsung');
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->timestamps();
        });

        // Tabel skor pertandingan per pemain
        Schema::create('match_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->integer('skor_keinjek')->default(0); // berapa kali diinjek lawan
            $table->integer('total_skor')->default(0);   // total skor (bisa disesuaikan logika)
            $table->enum('posisi', ['juara', 'runner_up', 'ketiga', 'keempat', 'kelima', 'keenam', 'none'])->default('none');
            $table->timestamps();

            $table->unique(['match_id', 'player_id']);
        });

        // Tabel foto harian - satu foto untuk semua pertandingan di hari yang sama
        Schema::create('daily_photos', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('foto'); // path foto
            $table->string('deskripsi')->nullable();
            $table->timestamps();

            $table->unique('tanggal'); // satu foto per hari
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_scores');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('daily_photos');
    }
};
