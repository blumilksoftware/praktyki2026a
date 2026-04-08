<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Friend;

class UpdateFriendAction
{
    public function execute(Friend $friend, array $data): void
    {
        $friend->update($data);
    }
}
