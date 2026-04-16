<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateGameAction;
use App\Actions\DecrementGameCopiesAction;
use App\Actions\IncrementGameCopiesAction;
use App\Actions\MergeGameCopiesAction;
use App\Actions\UpdateGameAction;
use App\Http\Requests\GameRequest;
use App\Models\Game;
use App\Services\BoardGameGeekService;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\BuildsPaginationMeta;
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
    use BuildsPaginationMeta;

    public function index(Request $request): Response
    {
        $perPage = min(max($request->integer("per_page", 10), 10), 50);

        $allowedSorts = ["name", "copies", "min_players", "max_players", "year"];
        $sortColumn = in_array($request->input("sort"), $allowedSorts, true)
            ? $request->input("sort")
            : "name";
        $sortDirection = $request->input("direction") === "desc" ? "desc" : "asc";

        $search = trim((string)$request->input("search", ""));
        $players = $request->integer("players", 0);
        $players = $players > 0 ? $players : null;

        $query = Game::query()->visibleTo($request->user()->id);

        if ($search !== "") {
            $query->where(function (Builder $subQuery) use ($search): void {
                $subQuery->where("name", "ilike", "%{$search}%")
                    ->orWhere("description", "ilike", "%{$search}%");
            });
        }

        if ($players !== null) {
            $query->where("min_players", "<=", $players)
                ->where("max_players", ">=", $players);
        }

        $games = $query
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render("Games/Index", [
            "games" => [
                "data" => $games->items(),
                "meta" => $this->paginationMeta($games, [
                    "sort" => $sortColumn,
                    "direction" => $sortDirection,
                    "search" => $search,
                    "players" => $players,
                ]),
            ],
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

    public function checkDuplicate(Request $request): JsonResponse
    {
        $request->validate([
            "name" => ["required", "string", "max:255"],
            "exclude_id" => ["nullable", "integer"],
        ]);

        $match = Game::findDuplicate(
            $request->user()->id,
            $request->input("name"),
            $request->integer("exclude_id") ?: null,
        );

        if ($match === null) {
            return response()->json(["duplicate" => null]);
        }

        return response()->json([
            "duplicate" => [
                "id" => $match->id,
                "name" => $match->name,
                "copies" => $match->copies,
                "is_shared" => $match->is_shared,
            ],
        ]);
    }

    public function store(GameRequest $request, CreateGameAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated());

        return Redirect::route("games.index");
    }

    public function incrementCopies(Game $game, IncrementGameCopiesAction $action): RedirectResponse
    {
        $this->authorize("incrementCopies", $game);

        $action->execute($game);

        return Redirect::route("games.index");
    }

    public function mergeInto(Request $request, Game $source, MergeGameCopiesAction $action): RedirectResponse
    {
        $this->authorize("update", $source);

        $request->validate([
            "target_id" => ["required", "integer"],
        ]);

        $target = Game::findOrFail($request->integer("target_id"));
        $this->authorize("incrementCopies", $target);

        $action->execute($source, $target);

        return Redirect::route("games.index");
    }

    public function decrementCopies(Request $request, Game $game, DecrementGameCopiesAction $action): RedirectResponse
    {
        $this->authorize("update", $game);

        $request->validate([
            "amount" => ["required", "integer", "min:1", "max:{$game->copies}"],
        ]);

        $action->execute($game, $request->integer("amount"));

        return Redirect::route("games.index", array_filter([
            "sort" => $request->input("sort"),
            "direction" => $request->input("direction"),
            "search" => $request->input("search"),
            "players" => $request->input("players"),
            "per_page" => $request->input("per_page"),
            "page" => $request->input("page"),
        ], fn(mixed $value): bool => $value !== null && $value !== ""));
    }

    public function importFromBgg(Request $request, BoardGameGeekService $bgg): JsonResponse
    {
        $request->validate([
            "url" => ["required", "string", "url"],
        ]);

        try {
            $data = $bgg->fetchPreview($request->input("url"));

            return response()->json(["game" => $data]);
        } catch (InvalidArgumentException $exception) {
            return response()->json(["message" => $exception->getMessage()], 422);
        } catch (RuntimeException $exception) {
            return response()->json(["message" => $exception->getMessage()], 502);
        }
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
