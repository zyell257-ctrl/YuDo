<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\DailyPhoto;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PlayerController;

// Root redirect
Route::get('/', fn() => redirect()->route('viewer.attendance'))->name('home');

Route::get('/media/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('media.public');

Route::get('/api/upload-debug', function () {
    $photo = DailyPhoto::latest('id')->first();
    $path = $photo?->foto;

    return response()->json([
        'latest_daily_photo_path' => $path,
        'latest_daily_photo_url' => $path ? Storage::url($path) : null,
        'exists_in_public_uploads' => $path ? File::exists(public_path('uploads/' . $path)) : false,
        'exists_in_storage_public' => $path ? Storage::disk('public')->exists($path) : false,
        'filesystem_public_url' => config('filesystems.disks.public.url'),
    ]);
})->name('api.uploadDebug');

// Viewer pages
Route::get('/viewer/absensi',      [AttendanceController::class, 'index'])->name('viewer.attendance');
Route::get('/viewer/pertandingan', [MatchController::class, 'index'])->name('viewer.matches');
Route::get('/viewer/pertandingan/{id}', [MatchController::class, 'show'])->name('viewer.matches.show');
Route::get('/viewer/history',      [HistoryController::class, 'index'])->name('viewer.history');

// API publik
Route::get('/api/attendance/today', [AttendanceController::class, 'getToday'])->name('api.attendance.today');
Route::get('/api/matches/today',    [MatchController::class, 'getToday'])->name('api.matches.today');
Route::get('/api/matches/active-status', [MatchController::class, 'activeStatus'])->name('api.matches.activeStatus');
Route::get('/api/history',          [HistoryController::class, 'getData'])->name('api.history');
Route::get('/api/history/attendance', [HistoryController::class, 'attendanceData'])->name('api.history.attendance');
Route::get('/api/players',          [PlayerController::class, 'index'])->name('api.players');

// Auth
Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin (protected)
Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/dashboard',                      [AttendanceController::class, 'index'])->name('admin.dashboard');
    Route::put('/absensi',                        [AttendanceController::class, 'update'])->name('admin.attendance.update');
    Route::post('/absensi/hadir-semua',           [AttendanceController::class, 'hadirSemua'])->name('admin.attendance.hadirSemua');
    Route::get('/pertandingan',                   [MatchController::class, 'index'])->name('admin.matches');
    Route::post('/pertandingan',                  [MatchController::class, 'store'])->name('admin.matches.store');
    Route::get('/pertandingan/{id}',              [MatchController::class, 'show'])->name('admin.matches.show');
    Route::post('/pertandingan/{id}/bukti-foto',  [MatchController::class, 'uploadProof'])->name('admin.matches.uploadProof');
    Route::put('/pertandingan/{id}/skor',         [MatchController::class, 'updateScore'])->name('admin.matches.updateScore');
    Route::put('/pertandingan/{id}/posisi',       [MatchController::class, 'setPosition'])->name('admin.matches.setPosition');
    Route::put('/pertandingan/{id}/selesai',      [MatchController::class, 'finish'])->name('admin.matches.finish');
    Route::delete('/pertandingan/{id}',           [MatchController::class, 'destroy'])->name('admin.matches.destroy');
    Route::post('/pertandingan/foto',             [MatchController::class, 'uploadPhoto'])->name('admin.matches.uploadPhoto');
    Route::get('/history',                        [HistoryController::class, 'index'])->name('admin.history');
    Route::get('/pemain',                         [PlayerController::class, 'manage'])->name('admin.players');
    Route::post('/pemain',                        [PlayerController::class, 'store'])->name('admin.players.store');
    Route::put('/pemain/{id}',                    [PlayerController::class, 'update'])->name('admin.players.update');
    Route::delete('/pemain/{id}',                 [PlayerController::class, 'destroy'])->name('admin.players.destroy');
});
