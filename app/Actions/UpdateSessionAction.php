<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Session;

class UpdateSessionAction
{
    public function execute(Session $session, array $data): Session
    {
        $session->update([
            "name" => $data["name"],
            "date" => $data["date"],
            "notes" => $data["notes"] ?? null,
        ]);

        $session->friends()->sync($data["friend_ids"] ?? []);
        $session->games()->sync($data["game_ids"] ?? []);

        return $session;
    }
}
