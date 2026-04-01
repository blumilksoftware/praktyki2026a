<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\Game;
use App\Services\SeatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeatingController extends Controller
{
    public function __construct(
        private SeatingService $seatingService,
    ) {}

    public function arrange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "friend_ids" => ["required", "array", "min:1"],
            "friend_ids.*" => ["integer", "exists:friends,id"],
            "game_ids" => ["required", "array", "min:1"],
            "game_ids.*" => ["integer", "exists:games,id"],
        ]);

        $friends = Friend::with("games")
            ->whereIn("id", $validated["friend_ids"])
            ->where("user_id", auth()->id())
            ->get();

        $games = Game::whereIn("id", $validated["game_ids"])
            ->where("user_id", auth()->id())
            ->get();

        $result = $this->seatingService->arrange($friends, $games);

        return response()->json([
            "tables" => collect($result["tables"])->map(fn($t) => [
                "game" => [
                    "id" => $t["game"]->id,
                    "name" => $t["game"]->name,
                    "min_players" => $t["game"]->min_players,
                    "max_players" => $t["game"]->max_players,
                ],
                "friends" => $t["friends"]->map(fn($f) => [
                    "id" => $f->id,
                    "name" => $f->first_name . " " . $f->last_name,
                ])->values(),
                "avg_rating" => $t["avg_rating"],
            ]),
            "unseated" => $result["unseated"]->map(fn($f) => [
                "id" => $f->id,
                "name" => $f->first_name . " " . $f->last_name,
            ])->values(),
        ]);
    }
}
