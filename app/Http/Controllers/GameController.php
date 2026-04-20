<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateGameAction;
use App\Actions\DecrementGameCopiesAction;
use App\Actions\IncrementGameCopiesAction;
use App\Actions\MergeGameCopiesAction;
use App\Actions\UpdateGameAction;
use App\Http\Requests\CheckDuplicateGameRequest;
use App\Http\Requests\DecrementGameCopiesRequest;
use App\Http\Requests\GameRequest;
use App\Http\Requests\ImportFromBggRequest;
use App\Http\Requests\MergeGameRequest;
use App\Models\Game;
use App\Services\BoardGameGeekService;
use App\Traits\BuildsPaginationMeta;
use Illuminate\Database\Eloquent\Builder;
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

        $search = trim($request->string("search")->value());
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

    public function checkDuplicate(CheckDuplicateGameRequest $request): JsonResponse
    {
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

    public function mergeInto(MergeGameRequest $request, Game $source, MergeGameCopiesAction $action): RedirectResponse
    {
        $this->authorize("update", $source);

        $target = Game::findOrFail($request->integer("target_id"));
        $this->authorize("incrementCopies", $target);

        $action->execute($source, $target);

        return Redirect::route("games.index");
    }

    public function decrementCopies(DecrementGameCopiesRequest $request, Game $game, DecrementGameCopiesAction $action): RedirectResponse
    {
        $this->authorize("update", $game);

        $action->execute($game, $request->integer("amount"));

        return Redirect::route("games.index", array_filter(
            $request->only(["sort", "direction", "search", "players", "per_page", "page"]),
        ));
    }

    public function importFromBgg(ImportFromBggRequest $request, BoardGameGeekService $bgg): JsonResponse
    {
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
