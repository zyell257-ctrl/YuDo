<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameMatch;
use App\Models\MatchScore;
use App\Models\Player;
use App\Models\Attendance;
use App\Models\DailyPhoto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MatchController extends Controller
{
    /** Halaman pertandingan - admin & viewer */
    public function index()
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $matches = GameMatch::with(['scores.player'])
            ->where('tanggal_match', $today)
            ->orderByRaw("FIELD(status_match, 'berlangsung', 'selesai')")
            ->orderBy('nomor_match')
            ->get();

        $dailyPhoto = DailyPhoto::where('tanggal', $today)->first();

        $hadirToday = Attendance::with('player')
            ->where('tanggal', $today)
            ->where('status_hadir', 'hadir')
            ->get()->pluck('player')->filter();

        $view = Auth::guard('admin')->check() ? 'admin.matches' : 'viewer.matches';

        return view($view, compact('matches', 'today', 'dailyPhoto', 'hadirToday'));
    }

    /** Detail pertandingan - admin & viewer */
    public function show($id)
    {
        $match = GameMatch::with(['scores.player'])->findOrFail($id);

        return view('matches.show', compact('match'));
    }

    /** Simpan pertandingan baru */
    public function store(Request $request)
    {
        $request->validate([
            'player_ids'   => 'required|array|min:2|max:6',
            'player_ids.*' => 'exists:players,id',
        ]);

        $today      = Carbon::now('Asia/Jakarta')->toDateString();
        $nomorMatch = GameMatch::where('tanggal_match', $today)->count() + 1;

        $match = GameMatch::create([
            'tanggal_match' => $today,
            'nomor_match'   => $nomorMatch,
            'status_match'  => 'berlangsung',
            'waktu_mulai'   => Carbon::now('Asia/Jakarta'),
        ]);

        foreach ($request->player_ids as $pid) {
            MatchScore::create(['match_id' => $match->id, 'player_id' => $pid,
                'skor_keinjek' => 0, 'posisi' => 'none']);
        }

        return response()->json(['success' => true, 'message' => "Pertandingan #{$nomorMatch} dimulai!", 'match_id' => $match->id]);
    }

    /** Update skor via AJAX */
    public function updateScore(Request $request)
    {
        $request->validate([
            'score_id'     => 'required|exists:match_scores,id',
            'skor_keinjek' => 'required|integer|min:0',
        ]);

        MatchScore::findOrFail($request->score_id)->update([
            'skor_keinjek' => $request->skor_keinjek,
        ]);

        return response()->json(['success' => true]);
    }

    /** Pilih posisi juara pertandingan secara manual */
    public function setPosition(Request $request, $id)
    {
        $request->validate([
            'score_id' => 'required|exists:match_scores,id',
            'posisi' => 'required|in:juara,runner_up,ketiga,keempat,kelima,keenam',
        ]);

        $match = GameMatch::with('scores')->findOrFail($id);
        if ($match->status_match === 'selesai') {
            return response()->json(['success' => false, 'message' => 'Pertandingan sudah selesai.'], 422);
        }

        $score = $match->scores->firstWhere('id', (int) $request->score_id);
        if (!$score) {
            return response()->json(['success' => false, 'message' => 'Pemain tidak ada di pertandingan ini.'], 422);
        }

        MatchScore::where('match_id', $match->id)
            ->where('posisi', $request->posisi)
            ->where('id', '!=', $score->id)
            ->update(['posisi' => 'none']);

        $score->update(['posisi' => $request->posisi]);
        $match->load('scores.player');

        return response()->json([
            'success' => true,
            'message' => "{$score->player->nama_pemain} diset sebagai juara {$score->rank_number}.",
            'positions' => $match->scores->map(fn($item) => [
                'score_id' => $item->id,
                'posisi' => $item->posisi,
                'rank' => $item->rank_number,
            ])->values(),
        ]);
    }

    /** Selesaikan pertandingan */
    public function finish($id)
    {
        $match = GameMatch::with('scores')->findOrFail($id);
        if ($match->status_match === 'selesai')
            return response()->json(['success' => false, 'message' => 'Sudah selesai.']);

        if (!$match->bukti_foto_pertandingan) {
            return response()->json(['success' => false, 'message' => 'Upload bukti pertandingan terlebih dahulu'], 422);
        }

        if ($match->scores->contains(fn($score) => $score->posisi === 'none')) {
            return response()->json(['success' => false, 'message' => 'Lengkapi semua posisi juara pemain terlebih dahulu'], 422);
        }

        $finishedAt = Carbon::now('Asia/Jakarta');
        $match->update([
            'status_match' => 'selesai',
            'waktu_selesai' => $finishedAt,
            'finished_at' => $finishedAt,
        ]);

        return response()->json(['success' => true, 'message' => 'Pertandingan selesai! Juara ditentukan.']);
    }

    /** Hapus pertandingan */
    public function destroy($id)
    {
        $match = GameMatch::findOrFail($id);
        if ($match->bukti_foto_pertandingan) {
            Storage::disk('public')->delete($match->bukti_foto_pertandingan);
        }
        $match->delete();
        return response()->json(['success' => true, 'message' => 'Pertandingan dihapus.']);
    }

    /** API: data hari ini */
    public function getToday()
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $data  = GameMatch::with(['scores.player'])
            ->where('tanggal_match', $today)
            ->orderByRaw("FIELD(status_match,'berlangsung','selesai')")
            ->orderBy('nomor_match')->get()
            ->map(fn($m) => [
                'id' => $m->id, 'nomor_match' => $m->nomor_match,
                'status_match' => $m->status_match,
                'waktu_mulai'  => $m->waktu_mulai?->format('H:i'),
                'waktu_selesai'=> $m->waktu_selesai?->format('H:i'),
                'finished_at'   => $m->finished_at?->format('H:i'),
                'bukti_foto_url' => $m->bukti_foto_url,
                'scores'       => $this->sortScoresByManualPosition($m->scores)->map(fn($s) => [
                    'id' => $s->id, 'player_id' => $s->player_id,
                    'nama_pemain'  => $s->player->nama_pemain,
                    'avatar_color' => $s->player->avatar_color,
                    'initials'     => $s->player->initials,
                    'foto_profile_url' => $s->player->foto_profile_url,
                    'skor_keinjek' => $s->skor_keinjek,
                    'posisi'       => $s->posisi,
                    'rank'         => $s->rank_number,
                    'position_label' => $s->position_label,
                    'badge'        => $s->badge,
                ]),
            ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function sortScoresByManualPosition($scores)
    {
        $hasManualPosition = $scores->contains(fn($score) => $score->posisi !== 'none');

        if (!$hasManualPosition) {
            return $scores->sortBy('id')->values();
        }

        return $scores->sortBy(fn($score) => $score->rank_number ?? 99)->values();
    }

    /** API ringan untuk indikator navbar */
    public function activeStatus()
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $activeCount = GameMatch::where('tanggal_match', $today)
            ->where('status_match', 'berlangsung')
            ->count();

        return response()->json([
            'success' => true,
            'active' => $activeCount > 0,
            'count' => $activeCount,
        ]);
    }

    /** Upload bukti selesai per pertandingan */
    public function uploadProof(Request $request, $id)
    {
        $match = GameMatch::findOrFail($id);

        if ($match->status_match === 'selesai') {
            return response()->json(['success' => false, 'message' => 'Pertandingan sudah selesai.'], 422);
        }

        $request->validate([
            'bukti_foto_pertandingan' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $file = $request->file('bukti_foto_pertandingan');
        $extension = strtolower($file->extension() ?: $file->guessExtension() ?: 'jpg');
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'jpg';
        $filename = 'match_' . $match->id . '_' . now('Asia/Jakarta')->format('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $path = $file->storeAs('match-proofs', $filename, 'public');

        if ($match->bukti_foto_pertandingan) {
            Storage::disk('public')->delete($match->bukti_foto_pertandingan);
        }

        $match->update(['bukti_foto_pertandingan' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Bukti pertandingan berhasil diupload.',
            'url' => Storage::url($path),
        ]);
    }

    /** Upload foto harian */
    public function uploadPhoto(Request $request)
    {
        $request->validate(['foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', 'deskripsi' => 'nullable|string|max:255']);

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $extension = strtolower($request->file('foto')->extension() ?: 'jpg');
        $filename = $today . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $path  = $request->file('foto')->storeAs('photos', $filename, 'public');

        DailyPhoto::updateOrCreate(['tanggal' => $today], ['foto' => $path, 'deskripsi' => $request->deskripsi]);

        return response()->json(['success' => true, 'message' => 'Foto diupload.', 'url' => Storage::url($path)]);
    }
}
