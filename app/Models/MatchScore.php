<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchScore extends Model
{
    protected $table = 'match_scores';

    protected $fillable = [
        'match_id',
        'player_id',
        'skor_keinjek',
        'total_skor',
        'posisi',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function match()
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public static function positionOrder(): array
    {
        return [
            'juara' => 1,
            'runner_up' => 2,
            'ketiga' => 3,
            'keempat' => 4,
            'kelima' => 5,
            'keenam' => 6,
            'none' => 99,
        ];
    }

    public function getRankNumberAttribute(): ?int
    {
        return self::positionOrder()[$this->posisi] ?? null;
    }

    public function getBadgeAttribute(): string
    {
        $rank = $this->rank_number;

        return match ($rank) {
            1 => '🥇',
            2 => '🥈',
            3 => '🥉',
            4, 5, 6 => (string) $rank,
            default => '',
        };
    }

    public function getPositionLabelAttribute(): string
    {
        $rank = $this->rank_number;

        return $rank && $rank < 99 ? "Juara {$rank}" : '';
    }
}
