<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Session;
use App\Models\User;

class SessionPolicy
{
    public function view(User $user, Session $session): bool
    {
        return $session->user_id === $user->id;
    }

    public function update(User $user, Session $session): bool
    {
        return $session->user_id === $user->id;
    }

    public function delete(User $user, Session $session): bool
    {
        return $session->user_id === $user->id;
    }

    public function arrange(User $user, Session $session): bool
    {
        return $session->user_id === $user->id;
    }
}
