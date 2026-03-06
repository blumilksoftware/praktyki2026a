<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $friend_id
 * @property int $game_id
 * @property int $rating
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Friend $friend
 * @property Game $game
 */
class FriendGame extends Pivot
{
    protected $table = "friend_game";
    public $incrementing = true;

    protected $fillable = [
        "friend_id",
        "game_id",
        "rating",
    ];

    protected function casts(): array
    {
        return [
            "rating" => "integer",
        ];
    }

    public function friend(): BelongsTo
    {
        return $this->belongsTo(Friend::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
