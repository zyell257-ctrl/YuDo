<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DailyPhoto extends Model
{
    protected $table = 'daily_photos';

    protected $fillable = ['tanggal', 'foto', 'deskripsi'];

    // Helper: URL foto
    public function getFotoUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->foto);
    }
}
