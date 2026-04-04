<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Game;

class UpdateGameAction
{
    public function execute(Game $game, array $data): void
    {
        $game->update($data);
    }
}
