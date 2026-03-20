<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property Carbon $date
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property Collection<int, Friend> $friends
 * @property Collection<int, Game> $games
 */
class Session extends Model
{
    use HasFactory;

    protected $table = "sessions_game";
    protected $fillable = [
        "user_id",
        "name",
        "date",
        "notes",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(Friend::class, "friend_session")
            ->withTimestamps();
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, "game_session")
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            "date" => "date",
        ];
    }
}
