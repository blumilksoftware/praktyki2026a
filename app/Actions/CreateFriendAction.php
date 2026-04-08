<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Friend;
use App\Models\User;

class CreateFriendAction
{
    public function execute(User $user, array $data): Friend
    {
        return Friend::create([
            ...$data,
            "user_id" => $user->id,
        ]);
    }
}
