<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateGameAction;
use App\Actions\UpdateGameAction;
use App\Http\Requests\GameRequest;
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
        $games = Game::visibleTo($request->user()->id)
            ->orderByDesc("updated_at")
            ->get();

        return Inertia::render("Games/Index", [
            "games" => $games,
        ]);
    }

    public function show(Game $game): Response
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

    public function store(GameRequest $request, CreateGameAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated());

        return Redirect::route("games.index");
    }

    public function edit(Game $game): Response
    {
        $this->authorize("update", $game);

        return Inertia::render("Games/Edit", [
            "game" => $game,
        ]);
    }

    public function update(GameRequest $request, Game $game, UpdateGameAction $action): RedirectResponse
    {
        $this->authorize("update", $game);

        $action->execute($game, $request->validated());

        return Redirect::route("games.index");
    }

    public function destroy(Game $game): RedirectResponse
    {
        $this->authorize("delete", $game);

        $game->delete();

        return Redirect::route("games.index");
    }
}
