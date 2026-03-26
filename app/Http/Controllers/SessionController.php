<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = min(max($request->integer("per_page", 10), 10), 50);

        $allowedSorts = ["name", "date"];
        $sortColumn = in_array($request->input("sort"), $allowedSorts, true)
            ? $request->input("sort")
            : "date";
        $sortDirection = $request->input("direction") === "asc" ? "asc" : "desc";

        $search = trim((string)$request->input("search", ""));
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
                "meta" => [
                    "current_page" => $sessions->currentPage(),
                    "last_page" => $sessions->lastPage(),
                    "per_page" => $sessions->perPage(),
                    "total" => $sessions->total(),
                    "from" => $sessions->firstItem(),
                    "to" => $sessions->lastItem(),
                    "sort" => $sortColumn,
                    "direction" => $sortDirection,
                    "search" => $search,
                    "date_from" => $dateFrom,
                    "date_to" => $dateTo,
                ],
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $userId = $request->user()->id;

        return Inertia::render("Sessions/Create", [
            "friends" => $request->user()->friends()->orderBy("last_name")->get(),
            "games" => Game::where("user_id", $userId)->orWhere("is_shared", true)->orderBy("name")->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "date" => ["required", "date"],
            "notes" => ["nullable", "string", "max:1000"],
            "friend_ids" => ["array"],
            "friend_ids.*" => ["integer", "exists:friends,id"],
            "game_ids" => ["array"],
            "game_ids.*" => ["integer", "exists:games,id"],
        ]);

        $session = Session::create([
            "user_id" => $request->user()->id,
            "name" => $validated["name"],
            "date" => $validated["date"],
            "notes" => $validated["notes"] ?? null,
        ]);

        $session->friends()->sync($validated["friend_ids"] ?? []);
        $session->games()->sync($validated["game_ids"] ?? []);

        return Redirect::route("sessions.index");
    }

    public function show(Request $request, Session $session): Response
    {
        if ($session->user_id !== $request->user()->id) {
            abort(403);
        }

        $session->load(["friends", "games"]);

        return Inertia::render("Sessions/Show", [
            "session" => $session,
        ]);
    }

    public function edit(Request $request, Session $session): Response
    {
        if ($session->user_id !== $request->user()->id) {
            abort(403);
        }

        $userId = $request->user()->id;
        $session->load(["friends", "games"]);

        return Inertia::render("Sessions/Edit", [
            "session" => $session,
            "friends" => $request->user()->friends()->orderBy("last_name")->get(),
            "games" => Game::where("user_id", $userId)->orWhere("is_shared", true)->orderBy("name")->get(),
        ]);
    }

    public function update(Request $request, Session $session): RedirectResponse
    {
        if ($session->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "date" => ["required", "date"],
            "notes" => ["nullable", "string", "max:1000"],
            "friend_ids" => ["array"],
            "friend_ids.*" => ["integer", "exists:friends,id"],
            "game_ids" => ["array"],
            "game_ids.*" => ["integer", "exists:games,id"],
        ]);

        $session->update([
            "name" => $validated["name"],
            "date" => $validated["date"],
            "notes" => $validated["notes"] ?? null,
        ]);

        $session->friends()->sync($validated["friend_ids"] ?? []);
        $session->games()->sync($validated["game_ids"] ?? []);

        return Redirect::route("sessions.index");
    }

    public function destroy(Request $request, Session $session): RedirectResponse
    {
        if ($session->user_id !== $request->user()->id) {
            abort(403);
        }

        $session->delete();

        return Redirect::route("sessions.index");
    }
}
