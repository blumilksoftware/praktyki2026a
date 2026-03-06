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
 * @property string $name
 * @property int|null $user_id
 * @property bool $is_shared
 * @property int $min_players
 * @property int $max_players
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User|null $user
 * @property Collection<int, Friend> $friends
 */
class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "user_id",
        "is_shared",
        "min_players",
        "max_players",
    ];

    protected function casts(): array
    {
        return [
            "is_shared" => "boolean",
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(Friend::class)
            ->using(FriendGame::class)
            ->withPivot("rating")
            ->withTimestamps();
    }
}
