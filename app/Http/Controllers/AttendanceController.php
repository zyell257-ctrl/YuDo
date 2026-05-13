<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $today   = Carbon::now('Asia/Jakarta')->toDateString();
        $players = Player::orderBy('nama_pemain')->get();

        foreach ($players as $player) {
            Attendance::firstOrCreate(
                ['player_id' => $player->id, 'tanggal' => $today],
                ['status_hadir' => 'tidak_hadir']
            );
        }

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

        $att = Attendance::updateOrCreate(
            ['player_id' => $request->player_id, 'tanggal' => $request->tanggal],
            ['status_hadir' => $request->status]
        );

        return response()->json(['success' => true, 'message' => 'Absensi diperbarui.', 'data' => $att]);
    }

    public function hadirSemua()
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        Player::all()->each(fn($p) => Attendance::updateOrCreate(
            ['player_id' => $p->id, 'tanggal' => $today],
            ['status_hadir' => 'hadir']
        ));
        return response()->json(['success' => true, 'message' => 'Semua pemain ditandai hadir.']);
    }

    public function getToday()
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $data  = Attendance::with('player')->where('tanggal', $today)->get()->map(fn($a) => [
            'player_id'    => $a->player_id,
            'nama_pemain'  => $a->player->nama_pemain,
            'avatar_color' => $a->player->avatar_color,
            'initials'     => $a->player->initials,
            'foto_profile_url' => $a->player->foto_profile_url,
            'status_hadir' => $a->status_hadir,
        ]);
        return response()->json(['success' => true, 'data' => $data]);
    }
}
