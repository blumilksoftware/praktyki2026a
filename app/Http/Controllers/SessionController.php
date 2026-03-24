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

        $sessions = Session::where("user_id", $request->user()->id)
            ->with(["friends", "games"])
            ->orderByDesc("date")
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
