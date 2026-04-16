<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Friend extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "first_name",
        "last_name",
        "email",
    ];

    public static function findDuplicateByName(int $userId, string $firstName, string $lastName, ?int $excludeId = null): ?self
    {
        return static::query()
            ->where("user_id", $userId)
            ->whereRaw("LOWER(first_name) = LOWER(?)", [$firstName])
            ->whereRaw("LOWER(last_name) = LOWER(?)", [$lastName])
            ->when($excludeId !== null, fn(Builder $query): Builder => $query->where("id", "!=", $excludeId))
            ->first();
    }

    public static function findDuplicateByEmail(int $userId, string $email, ?int $excludeId = null): ?self
    {
        return static::query()
            ->where("user_id", $userId)
            ->whereRaw("LOWER(email) = LOWER(?)", [$email])
            ->when($excludeId !== null, fn(Builder $query): Builder => $query->where("id", "!=", $excludeId))
            ->first();
    }

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

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class, "friend_session")
            ->withTimestamps();
    }
}
