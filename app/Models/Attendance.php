<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $fillable = ['player_id', 'status_hadir', 'tanggal'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}
