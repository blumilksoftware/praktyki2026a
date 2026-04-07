<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Friend;

class PreferenceService
{
    public function sync(Friend $friend, array $ratings): void
    {
        $data = collect($ratings)
            ->mapWithKeys(fn($item) => [$item["game_id"] => ["rating" => $item["rating"]]])
            ->toArray();

        $friend->games()->sync($data);
    }
}
