<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Game;

class MergeGameCopiesAction
{
    public function execute(Game $source, Game $target): void
    {
        $target->update(["copies" => min(100, $target->copies + $source->copies)]);
        $source->delete();
    }
}
