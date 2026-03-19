<?php

declare(strict_types=1);

namespace App\Models;

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
    ];

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

    protected function casts(): array
    {
        return [
            "is_shared" => "boolean",
        ];
    }
}
