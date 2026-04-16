<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Friend;

class SyncPreferencesAction
{
    public function execute(Friend $friend, array $ratings): void
    {
        $data = collect($ratings)
            ->mapWithKeys(fn(array $item): array => [$item["game_id"] => ["rating" => $item["rating"]]])
            ->toArray();

        $friend->games()->sync($data);
    }
}
