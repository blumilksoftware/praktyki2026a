<?php

declare(strict_types=1);

namespace App\Models;

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

    public static function findDuplicateByName(int $userId, string $firstName, string $lastName): ?self
    {
        return static::query()
            ->where("user_id", $userId)
            ->whereRaw("LOWER(first_name) = LOWER(?)", [$firstName])
            ->whereRaw("LOWER(last_name) = LOWER(?)", [$lastName])
            ->first();
    }

    public static function findDuplicateByEmail(int $userId, string $email): ?self
    {
        return static::query()
            ->where("user_id", $userId)
            ->whereRaw("LOWER(email) = LOWER(?)", [$email])
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
