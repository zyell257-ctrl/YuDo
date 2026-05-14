<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Support\UploadStorage;
use Illuminate\Validation\Rule;
use Throwable;

class PlayerController extends Controller
{
    public function manage()
    {
        $players = Player::orderBy('nama_pemain')->get();

        return view('admin.players', compact('players'));
    }

    /**
     * Daftar semua pemain (JSON untuk API)
     */
    public function index()
    {
        $players = Player::orderBy('nama_pemain')->get()->map(function ($p) {
            return [
                'id'           => $p->id,
                'nama_pemain'  => $p->nama_pemain,
                'avatar_color' => $p->avatar_color,
                'initials'     => $p->initials,
                'foto_profile_url' => $p->foto_profile_url,
            ];
        });

        return response()->json(['success' => true, 'data' => $players]);
    }

    /**
     * Tambah pemain baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pemain'  => 'required|string|max:50|unique:players,nama_pemain',
            'avatar_color' => 'nullable|string|max:10',
            'foto_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'nama_pemain.unique' => 'Nama pemain sudah terdaftar.',
        ]);

        // Warna acak jika tidak dipilih
        $colors = ['#ef4444','#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#f97316'];
        $color  = $request->avatar_color ?? $colors[array_rand($colors)];

        try {
            $path = $this->storeProfilePhoto($request);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Upload ke Cloudinary gagal: ' . $e->getMessage(),
            ], 500);
        }

        $player = Player::create([
            'nama_pemain'  => trim($request->nama_pemain),
            'avatar_color' => $color,
            'foto_profile' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pemain {$player->nama_pemain} berhasil ditambahkan!",
            'data'    => [
                'id'           => $player->id,
                'nama_pemain'  => $player->nama_pemain,
                'avatar_color' => $player->avatar_color,
                'initials'     => $player->initials,
                'foto_profile_url' => $player->foto_profile_url,
            ],
        ]);
    }

    /**
     * Update data pemain
     */
    public function update(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        $request->validate([
            'nama_pemain'  => [
                'required',
                'string',
                'max:50',
                Rule::unique('players', 'nama_pemain')->ignore($id),
            ],
            'avatar_color' => 'nullable|string|max:10',
            'foto_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        try {
            $path = $this->storeProfilePhoto($request);
            if ($path && $player->foto_profile) {
                UploadStorage::delete($player->foto_profile);
            }
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Upload ke Cloudinary gagal: ' . $e->getMessage(),
            ], 500);
        }

        $player->update([
            'nama_pemain'  => trim($request->nama_pemain),
            'avatar_color' => $request->avatar_color ?? $player->avatar_color,
            'foto_profile' => $path ?? $player->foto_profile,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pemain berhasil diperbarui.',
            'data' => [
                'id' => $player->id,
                'nama_pemain' => $player->nama_pemain,
                'avatar_color' => $player->avatar_color,
                'initials' => $player->initials,
                'foto_profile_url' => $player->foto_profile_url,
            ],
        ]);
    }

    /**
     * Hapus pemain
     */
    public function destroy($id)
    {
        $player = Player::findOrFail($id);
        $nama   = $player->nama_pemain;
        if ($player->foto_profile) {
            UploadStorage::delete($player->foto_profile);
        }
        $player->delete();

        return response()->json([
            'success' => true,
            'message' => "Pemain {$nama} berhasil dihapus.",
        ]);
    }

    private function storeProfilePhoto(Request $request): ?string
    {
        if (!$request->hasFile('foto_profile')) {
            return null;
        }

        $file = $request->file('foto_profile');
        $extension = strtolower($file->extension() ?: $file->guessExtension() ?: 'jpg');
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'jpg';
        $filename = 'player_' . now('Asia/Jakarta')->format('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;

        return UploadStorage::store($file, 'player-profiles', $filename);
    }
}
