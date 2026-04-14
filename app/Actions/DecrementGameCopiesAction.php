<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Game;

class DecrementGameCopiesAction
{
    public function execute(Game $game, int $amount): void
    {
        if ($amount >= $game->copies) {
            $game->delete();
        } else {
            $game->decrement("copies", $amount);
        }
    }
}
