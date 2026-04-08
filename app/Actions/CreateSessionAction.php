<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Session;
use App\Models\User;

class CreateSessionAction
{
    public function execute(User $user, array $data): Session
    {
        $session = Session::create([
            "user_id" => $user->id,
            "name" => $data["name"],
            "date" => $data["date"],
            "notes" => $data["notes"] ?? null,
        ]);

        $session->friends()->sync($data["friend_ids"] ?? []);
        $session->games()->sync($data["game_ids"] ?? []);

        return $session;
    }
}
