<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GameMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'tanggal_match',
        'nomor_match',
        'status_match',
        'bukti_foto_pertandingan',
        'waktu_mulai',
        'waktu_selesai',
        'finished_at',
    ];

    protected $casts = [
        'tanggal_match' => 'date',
        'waktu_mulai'   => 'datetime',
        'waktu_selesai' => 'datetime',
        'finished_at'   => 'datetime',
    ];

    // Relasi ke skor
    public function scores()
    {
        return $this->hasMany(MatchScore::class, 'match_id')->orderByDesc('total_skor');
    }

    // Relasi ke skor + pemain
    public function scoresWithPlayers()
    {
        return $this->hasMany(MatchScore::class, 'match_id')
                    ->with('player')
                    ->orderByDesc('total_skor');
    }

    // Helper: apakah sedang berlangsung?
    public function isActive(): bool
    {
        return $this->status_match === 'berlangsung';
    }

    public function getBuktiFotoUrlAttribute(): ?string
    {
        return $this->bukti_foto_pertandingan ? Storage::url($this->bukti_foto_pertandingan) : null;
    }

    // Helper: foto hari itu
    public function dailyPhoto()
    {
        return DailyPhoto::where('tanggal', $this->tanggal_match->toDateString())->first();
    }
}
