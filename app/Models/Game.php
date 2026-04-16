<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "user_id",
        "is_shared",
        "min_players",
        "max_players",
        "description",
        "year",
        "copies",
    ];

    public static function findDuplicate(int $userId, string $name, ?int $excludeId = null): ?self
    {
        return static::query()
            ->where("user_id", $userId)
            ->whereRaw("LOWER(name) = LOWER(?)", [$name])
            ->when($excludeId !== null, fn(Builder $query): Builder => $query->where("id", "!=", $excludeId))
            ->first();
    }

    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        return $query->where("user_id", $userId)->orWhere("is_shared", true);
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

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class, "game_session")
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            "is_shared" => "boolean",
        ];
    }
}
