<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Support\UploadStorage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:clean-demo-data {--force : Run without confirmation}', function () {
    if (!$this->option('force') && !$this->confirm('Hapus data pemain, absensi, pertandingan, skor, dan foto? Admin tetap disimpan.')) {
        $this->warn('Dibatalkan.');
        return self::FAILURE;
    }

    $tables = ['players', 'attendance', 'matches', 'match_scores', 'daily_photos'];
    $counts = collect($tables)
        ->mapWithKeys(fn (string $table) => [$table => DB::table($table)->count()])
        ->all();

    $filePaths = collect()
        ->merge(DB::table('players')->whereNotNull('foto_profile')->pluck('foto_profile'))
        ->merge(DB::table('daily_photos')->pluck('foto'))
        ->merge(DB::table('matches')->whereNotNull('bukti_foto_pertandingan')->pluck('bukti_foto_pertandingan'))
        ->filter()
        ->unique()
        ->values();

    $deletedFiles = 0;
    foreach ($filePaths as $path) {
        try {
            UploadStorage::delete($path);
            $deletedFiles++;
        } catch (Throwable $e) {
            report($e);
            $this->warn("Gagal hapus file {$path}: {$e->getMessage()}");
        }
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    foreach (['match_scores', 'attendance', 'matches', 'daily_photos', 'players'] as $table) {
        DB::table($table)->truncate();
    }
    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    $this->info('Data demo berhasil dibersihkan.');
    foreach ($counts as $table => $count) {
        $this->line("- {$table}: {$count} row dihapus");
    }
    $this->line("- files: {$deletedFiles} referensi dicoba hapus");
    $this->line('- admins: tetap disimpan (' . DB::table('admins')->count() . ' row)');

    return self::SUCCESS;
})->purpose('Clean demo/player/match/upload data while preserving admins and migrations');
