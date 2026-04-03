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
            "friend_ids"   => ["required", "array", "min:1"],
            "friend_ids.*" => ["integer", "exists:friends,id"],
            "game_ids"     => ["required", "array", "min:1"],
            "game_ids.*"   => ["integer", "exists:games,id"],
        ]);

        $friends = Friend::with("games")
            ->whereIn("id", $validated["friend_ids"])
            ->where("user_id", auth()->id())
            ->get();

        $games = Game::visibleTo(auth()->id())
            ->whereIn("id", $validated["game_ids"])
            ->get();

        return response()->json(
            $this->seatingService->arrangeFormatted($friends, $games)
        );
    }
}
