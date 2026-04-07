<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    public function view(User $user, Game $game): bool
    {
        return $game->is_shared || $game->user_id === $user->id;
    }

    public function update(User $user, Game $game): bool
    {
        return !$game->is_shared && $game->user_id === $user->id;
    }

    public function delete(User $user, Game $game): bool
    {
        return !$game->is_shared && $game->user_id === $user->id;
    }
}
