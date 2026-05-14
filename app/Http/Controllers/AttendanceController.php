<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index()
    {
        $today   = Carbon::now('Asia/Jakarta')->toDateString();
        $players = Player::orderBy('nama_pemain')->get();

        $attendances = Attendance::with('player')->where('tanggal', $today)->get()->keyBy('player_id');
        $hariIni     = Carbon::now('Asia/Jakarta');
        $view        = Auth::guard('admin')->check() ? 'admin.attendance' : 'viewer.attendance';

        return view($view, compact('players', 'attendances', 'today', 'hariIni'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'status'    => 'required|in:hadir,tidak_hadir',
            'tanggal'   => 'required|date',
        ]);

        $tanggal = Carbon::parse($request->tanggal, 'Asia/Jakarta')->toDateString();
        $now = now('Asia/Jakarta');

        DB::table('attendance')->upsert(
            [[
                'player_id' => $request->player_id,
                'tanggal' => $tanggal,
                'status_hadir' => $request->status,
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['player_id', 'tanggal'],
            ['status_hadir', 'updated_at']
        );

        $att = Attendance::where('player_id', $request->player_id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        return response()->json(['success' => true, 'message' => 'Absensi diperbarui.', 'data' => $att]);
    }

    public function saveBatch(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.player_id' => 'required|exists:players,id',
            'attendances.*.status' => 'required|in:hadir,tidak_hadir',
        ]);

        $tanggal = Carbon::parse($request->tanggal, 'Asia/Jakarta')->toDateString();
        $now = now('Asia/Jakarta');

        foreach ($request->attendances as $item) {
            DB::table('attendance')->upsert(
                [[
                    'player_id' => $item['player_id'],
                    'tanggal' => $tanggal,
                    'status_hadir' => $item['status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]],
                ['player_id', 'tanggal'],
                ['status_hadir', 'updated_at']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil disimpan.',
            'saved_count' => count($request->attendances),
        ]);
    }

    public function hadirSemua()
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $now = now('Asia/Jakarta');
        Player::all()->each(fn($p) => DB::table('attendance')->upsert(
            [[
                'player_id' => $p->id,
                'tanggal' => $today,
                'status_hadir' => 'hadir',
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['player_id', 'tanggal'],
            ['status_hadir', 'updated_at']
        ));
        return response()->json(['success' => true, 'message' => 'Semua pemain ditandai hadir.']);
    }

    public function getToday()
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $attendances = Attendance::where('tanggal', $today)->get()->keyBy('player_id');
        $data  = Player::orderBy('nama_pemain')->get()->map(function ($player) use ($attendances) {
            $attendance = $attendances->get($player->id);

            return [
                'player_id'    => $player->id,
                'nama_pemain'  => $player->nama_pemain,
                'avatar_color' => $player->avatar_color,
                'initials'     => $player->initials,
                'foto_profile_url' => $player->foto_profile_url,
                'status_hadir' => $attendance?->status_hadir ?? 'tidak_hadir',
            ];
        });
        return response()->json(['success' => true, 'data' => $data]);
    }
}
