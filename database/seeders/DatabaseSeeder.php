<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed admin
        DB::table('admins')->insert([
            'username'   => 'admin',
            'password'   => Hash::make('admin123'),
            'nama'       => 'Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed pemain dengan warna avatar unik
        $pemain = [
            ['nama_pemain' => 'Budi',    'avatar_color' => '#ef4444'],
            ['nama_pemain' => 'Sari',    'avatar_color' => '#3b82f6'],
            ['nama_pemain' => 'Joko',    'avatar_color' => '#10b981'],
            ['nama_pemain' => 'Dewi',    'avatar_color' => '#f59e0b'],
            ['nama_pemain' => 'Agus',    'avatar_color' => '#8b5cf6'],
            ['nama_pemain' => 'Rina',    'avatar_color' => '#ec4899'],
        ];

        foreach ($pemain as $p) {
            DB::table('players')->insert(array_merge($p, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Seed absensi hari ini - semua hadir
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $players = DB::table('players')->get();

        foreach ($players as $player) {
            DB::table('attendance')->insert([
                'player_id'    => $player->id,
                'status_hadir' => 'hadir',
                'tanggal'      => $today,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Seed pertandingan hari ini sebagai contoh
        $matchId = DB::table('matches')->insertGetId([
            'tanggal_match'  => $today,
            'nomor_match'    => 1,
            'status_match'   => 'selesai',
            'waktu_mulai'    => Carbon::now('Asia/Jakarta')->subHours(2),
            'waktu_selesai'  => Carbon::now('Asia/Jakarta')->subHour(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Skor untuk pertandingan pertama (4 pemain)
        $skors = [
            ['player_id' => 1, 'skor_keinjek' => 2, 'total_skor' => 15, 'posisi' => 'juara'],
            ['player_id' => 2, 'skor_keinjek' => 3, 'total_skor' => 12, 'posisi' => 'runner_up'],
            ['player_id' => 3, 'skor_keinjek' => 5, 'total_skor' => 8,  'posisi' => 'ketiga'],
            ['player_id' => 4, 'skor_keinjek' => 7, 'total_skor' => 4,  'posisi' => 'keempat'],
        ];

        foreach ($skors as $s) {
            DB::table('match_scores')->insert(array_merge($s, [
                'match_id'   => $matchId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Seed pertandingan kedua yang sedang berlangsung
        $matchId2 = DB::table('matches')->insertGetId([
            'tanggal_match'  => $today,
            'nomor_match'    => 2,
            'status_match'   => 'berlangsung',
            'waktu_mulai'    => Carbon::now('Asia/Jakarta')->subMinutes(30),
            'waktu_selesai'  => null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $skors2 = [
            ['player_id' => 3, 'skor_keinjek' => 1, 'total_skor' => 5,  'posisi' => 'none'],
            ['player_id' => 4, 'skor_keinjek' => 2, 'total_skor' => 3,  'posisi' => 'none'],
            ['player_id' => 5, 'skor_keinjek' => 0, 'total_skor' => 7,  'posisi' => 'none'],
            ['player_id' => 6, 'skor_keinjek' => 3, 'total_skor' => 2,  'posisi' => 'none'],
        ];

        foreach ($skors2 as $s) {
            DB::table('match_scores')->insert(array_merge($s, [
                'match_id'   => $matchId2,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
