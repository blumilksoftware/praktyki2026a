<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FriendGame extends Pivot
{
    public $incrementing = true;
    protected $table = "friend_game";
    protected $fillable = [
        "friend_id",
        "game_id",
        "rating",
    ];

    public function friend(): BelongsTo
    {
        return $this->belongsTo(Friend::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    protected function casts(): array
    {
        return [
            "rating" => "float",
        ];
    }
}
