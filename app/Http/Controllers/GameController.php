<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Game;
use App\Http\Requests\GameRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function index(Request $request): Response
    {
        $games = Game::visibleTo($request->user()->id)
            ->orderByDesc("updated_at")
            ->get();

        return Inertia::render("Games/Index", [
            "games" => $games,
        ]);
    }

    public function show(Request $request, Game $game): Response
    {
        $this->authorize("view", $game);

        return Inertia::render("Games/Show", [
            "game" => $game,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render("Games/Create");
    }

    public function store(GameRequest $request): RedirectResponse
    {
        Game::create([
            ...$request->validated(),
            "user_id" => $request->user()->id,
        ]);

        return Redirect::route("games.index");
    }

    public function edit(Request $request, Game $game): Response
    {
        $this->authorize("update", $game);

        return Inertia::render("Games/Edit", [
            "game" => $game,
        ]);
    }

    public function update(GameRequest $request, Game $game): RedirectResponse
    {
        $this->authorize("update", $game);

        $game->update($request->validated());

        return Redirect::route("games.index");
    }

    public function destroy(Game $game): RedirectResponse
    {
        $this->authorize("delete", $game);

        $game->delete();

        return Redirect::route("games.index");
    }
}
