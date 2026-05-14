<?php

namespace App\Models;

use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Model;
class Player extends Model
{
    protected $table = 'players';

    protected $fillable = ['nama_pemain', 'avatar_color', 'foto_profile'];

    protected $appends = ['initials', 'foto_profile_url'];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'player_id');
    }

    public function matchScores()
    {
        return $this->hasMany(MatchScore::class, 'player_id');
    }

    // Helper: inisial nama untuk avatar default
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->nama_pemain);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->nama_pemain, 0, 2));
    }

    public function getFotoProfileUrlAttribute(): ?string
    {
        return UploadStorage::url($this->foto_profile);
    }
}
