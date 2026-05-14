<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class HistoryController extends Controller
{
    /**
     * Tampilkan halaman history pertandingan
     */
    public function index(Request $request)
    {
        $players = Player::orderBy('nama_pemain')->get();
        return view('admin.history', compact('players'));
    }

    /**
     * API: Get history pertandingan dengan filter
     */
    public function getData(Request $request)
    {
        $query = GameMatch::with(['scores.player'])
            ->orderByDesc('tanggal_match')
            ->orderByDesc('nomor_match');

        // Filter berdasarkan periode
        $filter = $request->get('filter', 'hari_ini');
        $today  = Carbon::now('Asia/Jakarta')->toDateString();

        switch ($filter) {
            case 'hari_ini':
                $query->where('tanggal_match', $today);
                break;
            case 'minggu_ini':
                $startOfWeek = Carbon::now('Asia/Jakarta')->startOfWeek()->toDateString();
                $query->whereBetween('tanggal_match', [$startOfWeek, $today]);
                break;
            case 'bulan_ini':
                $startOfMonth = Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString();
                $query->whereBetween('tanggal_match', [$startOfMonth, $today]);
                break;
            case 'semua':
                // No filter
                break;
            default:
                // Custom tanggal
                if ($request->has('tanggal')) {
                    $query->where('tanggal_match', $request->tanggal);
                }
                if ($request->has('bulan')) {
                    $query->whereMonth('tanggal_match', $request->bulan);
                }
                break;
        }

        // Filter berdasarkan nama pemain
        if ($request->filled('player_id')) {
            $query->whereHas('scores', function ($q) use ($request) {
                $q->where('player_id', $request->player_id);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('scores.player', function ($q) use ($search) {
                $q->where('nama_pemain', 'like', "%{$search}%");
            });
        }

        $matches = $query->paginate(10);

        $data = $matches->map(function ($match) {
            return [
                'id'            => $match->id,
                'nomor_match'   => $match->nomor_match,
                'tanggal_match' => $match->tanggal_match->locale('id')->isoFormat('dddd, D MMMM Y'),
                'tanggal_raw'   => $match->tanggal_match->toDateString(),
                'status_match'  => $match->status_match,
                'waktu_mulai'   => $match->waktu_mulai?->format('H:i'),
                'waktu_selesai' => $match->waktu_selesai?->format('H:i'),
                'finished_at'    => $match->finished_at?->format('H:i'),
                'bukti_foto_url' => $match->bukti_foto_pertandingan ? Storage::url($match->bukti_foto_pertandingan) : null,
                'scores'        => $this->sortScoresByManualPosition($match->scores)->map(function ($s) {
                    return [
                        'player_id'    => $s->player_id,
                        'nama_pemain'  => $s->player->nama_pemain,
                        'avatar_color' => $s->player->avatar_color,
                        'initials'     => $s->player->initials,
                        'foto_profile_url' => $s->player->foto_profile_url,
                        'skor_keinjek' => $s->skor_keinjek,
                        'posisi'       => $s->posisi,
                        'rank'         => $s->rank_number,
                        'position_label' => $s->position_label,
                        'badge'        => $s->badge,
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'success'     => true,
            'data'        => $data,
            'pagination'  => [
                'current_page' => $matches->currentPage(),
                'last_page'    => $matches->lastPage(),
                'total'        => $matches->total(),
            ],
        ]);
    }

    public function attendanceData(Request $request)
    {
        $filter = $request->get('filter', 'hari_ini');
        $today = Carbon::now('Asia/Jakarta');
        $query = Attendance::with('player')->orderByDesc('tanggal');

        switch ($filter) {
            case 'hari_ini':
                $query->whereDate('tanggal', $today->toDateString());
                break;
            case 'minggu_ini':
                $query->whereBetween('tanggal', [
                    $today->copy()->startOfWeek()->toDateString(),
                    $today->toDateString(),
                ]);
                break;
            case 'bulan_ini':
                $query->whereBetween('tanggal', [
                    $today->copy()->startOfMonth()->toDateString(),
                    $today->toDateString(),
                ]);
                break;
            case 'custom_date':
                if ($request->filled('tanggal')) {
                    $query->whereDate('tanggal', $request->tanggal);
                }
                break;
            case 'semua':
                break;
            default:
                $query->whereDate('tanggal', $today->toDateString());
                break;
        }

        $rows = $query->get()->groupBy(fn($attendance) => $attendance->tanggal instanceof Carbon
            ? $attendance->tanggal->toDateString()
            : Carbon::parse($attendance->tanggal)->toDateString());

        $dates = $rows->keys()->values();
        $perPage = 7;
        $page = max((int) $request->get('page', 1), 1);
        $slice = $dates->slice(($page - 1) * $perPage, $perPage)->values();

        $data = $slice->map(function ($date) use ($rows) {
            $dateCarbon = Carbon::parse($date)->locale('id');
            $items = $rows[$date]->sortBy(fn($attendance) => $attendance->player?->nama_pemain ?? '');

            return [
                'tanggal' => $dateCarbon->isoFormat('D MMMM Y'),
                'nama_hari' => $dateCarbon->isoFormat('dddd'),
                'tanggal_raw' => $date,
                'hadir' => $items->where('status_hadir', 'hadir')->map(fn($attendance) => $this->attendancePlayerPayload($attendance))->values(),
                'tidak_hadir' => $items->where('status_hadir', 'tidak_hadir')->map(fn($attendance) => $this->attendancePlayerPayload($attendance))->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'last_page' => max((int) ceil($dates->count() / $perPage), 1),
                'total' => $dates->count(),
            ],
        ]);
    }

    private function attendancePlayerPayload(Attendance $attendance): array
    {
        $player = $attendance->player;

        return [
            'player_id' => $attendance->player_id,
            'nama_pemain' => $player?->nama_pemain ?? 'Pemain terhapus',
            'avatar_color' => $player?->avatar_color ?? '#4361ee',
            'initials' => $player?->initials ?? '--',
            'foto_profile_url' => $player?->foto_profile_url,
        ];
    }

    private function sortScoresByManualPosition($scores)
    {
        $hasManualPosition = $scores->contains(fn($score) => $score->posisi !== 'none');

        if (!$hasManualPosition) {
            return $scores->sortBy('id')->values();
        }

        return $scores->sortBy(fn($score) => $score->rank_number ?? 99)->values();
    }
}
