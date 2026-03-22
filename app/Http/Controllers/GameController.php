<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\BoardGameGeekService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use RuntimeException;

class GameController extends Controller
{
    public function index(Request $request): Response
    {
        $games = Game::where("user_id", $request->user()->id)
            ->orWhere("is_shared", true)
            ->orderBy("name")
            ->get();

        return Inertia::render("Games/Index", [
            "games" => $games,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render("Games/Create");
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "min_players" => ["required", "integer", "min:1"],
            "max_players" => ["required", "integer", "min:1", "gte:min_players"],
            // BGG fields are optional — present when coming from BGG import,
            // absent when coming from the manual form.
            "bgg_id" => ["nullable", "integer"],
            "bgg_url" => ["nullable", "string", "url", "max:500"],
            "description" => ["nullable", "string"],
            "year" => ["nullable", "integer", "min:1900", "max:2100"],
            "min_age" => ["nullable", "integer", "min:0"],
        ]);

        Game::create([
            ...$validated,
            "user_id" => $request->user()->id,
        ]);

        return Redirect::route("games.index");
    }

    public function importFromBgg(Request $request, BoardGameGeekService $bgg): JsonResponse
    {
        $request->validate([
            "url" => ["required", "string", "url"],
        ]);

        try {
            $data = $bgg->fetchPreview($request->input("url"));

            return response()->json(["game" => $data]);
        } catch (InvalidArgumentException $e) {
            return response()->json(["message" => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            return response()->json(["message" => $e->getMessage()], 502);
        }
    }

    public function edit(Request $request, Game $game): Response
    {
        if ($game->is_shared || $game->user_id !== $request->user()->id) {
            abort(403);
        }

        return Inertia::render("Games/Edit", [
            "game" => $game,
        ]);
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        if ($game->is_shared || $game->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "min_players" => ["required", "integer", "min:1"],
            "max_players" => ["required", "integer", "min:1", "gte:min_players"],
        ]);

        $game->update($validated);

        return Redirect::route("games.index");
    }

    public function destroy(Request $request, Game $game): RedirectResponse
    {
        if ($game->is_shared || $game->user_id !== $request->user()->id) {
            abort(403);
        }

        $game->delete();

        return Redirect::route("games.index");
    }
}
