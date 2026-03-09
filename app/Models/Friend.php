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
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property Collection<int, Game> $games
 */
class Friend extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "first_name",
        "last_name",
        "email",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class)
            ->using(FriendGame::class)
            ->withPivot("rating")
            ->withTimestamps();
    }
}
