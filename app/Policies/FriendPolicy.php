<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Friend;
use App\Models\User;

class FriendPolicy
{
    public function update(User $user, Friend $friend): bool
    {
        return $friend->user_id === $user->id;
    }

    public function delete(User $user, Friend $friend): bool
    {
        return $friend->user_id === $user->id;
    }

    public function managePreferences(User $user, Friend $friend): bool
    {
        return $friend->user_id === $user->id;
    }
}
