<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Game;
use App\Models\User;

class CreateGameAction
{
    public function execute(User $user, array $data): Game
    {
        return Game::create([
            ...$data,
            "user_id" => $user->id,
        ]);
    }
}
