<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateSessionAction;
use App\Actions\UpdateSessionAction;
use App\Http\Requests\SessionRequest;
use App\Models\Game;
use App\Models\Session;
use App\Services\SeatingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function __construct(
        private SeatingService $seatingService,
    ) {}

    public function index(Request $request): Response
    {
        $sessions = Session::where("user_id", $request->user()->id)
            ->with(["friends", "games"])
            ->orderByDesc("date")
            ->get();

        return Inertia::render("Sessions/Index", [
            "sessions" => $sessions,
        ]);
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

    public function arrange(Session $session): Response
    {
        $this->authorize("arrange", $session);

        $session->load(["friends.games", "games"]);

        $arrangement = $this->seatingService->arrangeFormatted($session->friends, $session->games);

        return Inertia::render("Sessions/Show", [
            "session" => $session,
            "arrangement" => $arrangement,
        ]);
    }
}
