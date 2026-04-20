<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SeatingRequest;
use App\Models\Friend;
use App\Models\Game;
use App\Services\SeatingService;
use Illuminate\Http\JsonResponse;

class SeatingController extends Controller
{
    public function __construct(
        private SeatingService $seatingService,
    ) {}

    public function arrange(SeatingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $friends = Friend::with("games")
            ->whereIn("id", $validated["friend_ids"])
            ->where("user_id", $request->user()->id)
            ->get();

        $games = Game::visibleTo($request->user()->id)
            ->whereIn("id", $validated["game_ids"])
            ->get();

        $coverageWeight = $request->float("coverage_weight", 0.6);
        $allowUnknownPreference = $request->boolean("allow_unknown_preference", true);

        return response()->json(
            $this->seatingService->arrangeFormatted($friends, $games, $coverageWeight, $allowUnknownPreference),
        );
    }
}
