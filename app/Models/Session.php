<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public static function findDuplicate(int $userId, string $name, string $date, ?int $excludeId = null): ?self
    {
        return static::query()
            ->where("user_id", $userId)
            ->whereRaw("LOWER(name) = LOWER(?)", [$name])
            ->whereDate("date", $date)
            ->when($excludeId !== null, fn(Builder $query): Builder => $query->where("id", "!=", $excludeId))
            ->first();
    }

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
