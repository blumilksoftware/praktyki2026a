<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Game;

class IncrementGameCopiesAction
{
    public function execute(Game $game): void
    {
        if ($game->copies < 100) {
            $game->increment("copies");
        }
    }
}
