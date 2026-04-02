<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function index(Request $request): Response
    {
        $games = Game::where("user_id", $request->user()->id)
            ->orWhere("is_shared", true)
            ->orderByDesc("updated_at")
            ->get();

        return Inertia::render("Games/Index", [
            "games" => $games,
        ]);
    }

    public function show(Request $request, Game $game): Response
    {
        if (!$game->is_shared && $game->user_id !== $request->user()->id) {
            abort(403);
        }

        return Inertia::render("Games/Show", [
            "game" => $game,
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
            "max_players" => ["required", "integer", "min:1", "max:100", "gte:min_players"],
        ]);

        Game::create([
            ...$validated,
            "user_id" => $request->user()->id,
        ]);

        return Redirect::route("games.index");
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
            "max_players" => ["required", "integer", "min:1", "max:100", "gte:min_players"],
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
