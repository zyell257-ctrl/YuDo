<?php

namespace App\Models;

use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Model;
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
        return $this->hasMany(MatchScore::class, 'match_id')->orderBy('id');
    }

    // Relasi ke skor + pemain
    public function scoresWithPlayers()
    {
        return $this->hasMany(MatchScore::class, 'match_id')
                    ->with('player')
                    ->orderBy('id');
    }

    // Helper: apakah sedang berlangsung?
    public function isActive(): bool
    {
        return $this->status_match === 'berlangsung';
    }

    public function getBuktiFotoUrlAttribute(): ?string
    {
        return UploadStorage::url($this->bukti_foto_pertandingan);
    }

    // Helper: foto hari itu
    public function dailyPhoto()
    {
        return DailyPhoto::where('tanggal', $this->tanggal_match->toDateString())->first();
    }
}
