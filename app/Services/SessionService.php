<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Session;
use App\Models\User;

class SessionService
{
    public function findDuplicate(int $userId, string $name, string $date, ?int $excludeId = null): ?array
    {
        $match = Session::findDuplicate($userId, $name, $date, $excludeId);

        if ($match === null) {
            return null;
        }

        return [
            "id" => $match->id,
            "name" => $match->name,
            "date" => $match->date->toDateString(),
        ];
    }

    public function create(User $user, array $data): Session
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

    public function update(Session $session, array $data): Session
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
