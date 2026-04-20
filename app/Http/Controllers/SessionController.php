<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateSessionAction;
use App\Actions\UpdateSessionAction;
use App\Http\Requests\ArrangeSessionRequest;
use App\Http\Requests\CheckDuplicateSessionRequest;
use App\Http\Requests\SessionRequest;
use App\Models\Game;
use App\Models\Session;
use App\Services\SeatingService;
use App\Services\SessionService;
use App\Traits\BuildsPaginationMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    use BuildsPaginationMeta;

    public function __construct(
        private SeatingService $seatingService,
    ) {}

    public function index(Request $request): Response
    {
        $perPage = min(max($request->integer("per_page", 10), 10), 50);

        $allowedSorts = ["name", "date"];
        $sortColumn = in_array($request->input("sort"), $allowedSorts, true)
            ? $request->input("sort")
            : "date";
        $sortDirection = $request->input("direction") === "asc" ? "asc" : "desc";

        $search = trim($request->string("search")->value());
        $dateFrom = $request->input("date_from", "");
        $dateTo = $request->input("date_to", "");
        $dateFrom = (is_string($dateFrom) && $dateFrom !== "" && strtotime($dateFrom) !== false)
            ? $dateFrom
            : null;
        $dateTo = (is_string($dateTo) && $dateTo !== "" && strtotime($dateTo) !== false)
            ? $dateTo
            : null;

        $query = Session::where("user_id", $request->user()->id)
            ->with(["friends", "games"]);

        if ($search !== "") {
            $query->where("name", "ilike", "%{$search}%");
        }

        if ($dateFrom !== null) {
            $query->whereDate("date", ">=", $dateFrom);
        }

        if ($dateTo !== null) {
            $query->whereDate("date", "<=", $dateTo);
        }

        $sessions = $query
            ->orderBy($sortColumn, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render("Sessions/Index", [
            "sessions" => [
                "data" => $sessions->items(),
                "meta" => $this->paginationMeta($sessions, [
                    "sort" => $sortColumn,
                    "direction" => $sortDirection,
                    "search" => $search,
                    "date_from" => $dateFrom,
                    "date_to" => $dateTo,
                ]),
            ],
        ]);
    }

    public function checkDuplicate(CheckDuplicateSessionRequest $request, SessionService $sessionService): JsonResponse
    {
        $duplicate = $sessionService->findDuplicate(
            $request->user()->id,
            $request->input("name"),
            $request->input("date"),
            $request->integer("exclude_id") ?: null,
        );

        return response()->json(compact("duplicate"));
    }

    public function create(Request $request): Response
    {
        return Inertia::render("Sessions/Create", [
            "friends" => $request->user()->friends()->orderBy("last_name")->get(),
            "games" => Game::visibleTo($request->user()->id)->orderBy("name")->get(),
        ]);
    }

    public function store(SessionRequest $request, CreateSessionAction $action): RedirectResponse
    {
        $session = $action->execute($request->user(), $request->validated());

        return Redirect::route("sessions.show", $session);
    }

    public function show(Session $session): Response
    {
        $this->authorize("view", $session);

        $session->load(["friends", "games"]);

        return Inertia::render("Sessions/Show", [
            "session" => $session,
        ]);
    }

    public function edit(Request $request, Session $session): Response
    {
        $this->authorize("update", $session);

        $session->load(["friends", "games"]);

        return Inertia::render("Sessions/Edit", [
            "session" => $session,
            "friends" => $request->user()->friends()->orderBy("last_name")->get(),
            "games" => Game::visibleTo($request->user()->id)->orderBy("name")->get(),
        ]);
    }

    public function update(SessionRequest $request, Session $session, UpdateSessionAction $action): RedirectResponse
    {
        $this->authorize("update", $session);

        $session = $action->execute($session, $request->validated());

        return Redirect::route("sessions.show", $session);
    }

    public function destroy(Session $session): RedirectResponse
    {
        $this->authorize("delete", $session);

        $session->delete();

        return Redirect::route("sessions.index");
    }

    public function arrange(ArrangeSessionRequest $request, Session $session): Response
    {
        $this->authorize("arrange", $session);

        $session->load(["friends.games", "games"]);

        $coverageWeight = $request->float("coverage_weight", 0.6);
        $allowUnknownPreference = $request->boolean("allow_unknown_preference", true);

        $arrangement = $this->seatingService->arrangeFormatted($session->friends, $session->games, $coverageWeight, $allowUnknownPreference);

        return Inertia::render("Sessions/Show", [
            "session" => $session,
            "arrangement" => $arrangement,
        ]);
    }
}
