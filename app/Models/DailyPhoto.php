<?php

namespace App\Models;

use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Model;
class DailyPhoto extends Model
{
    protected $table = 'daily_photos';

    protected $fillable = ['tanggal', 'foto', 'deskripsi'];

    // Helper: URL foto
    public function getFotoUrlAttribute(): string
    {
        return UploadStorage::url($this->foto);
    }
}
