<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SyncPreferencesAction;
use App\Http\Requests\PreferenceRequest;
use App\Models\Friend;
use App\Models\Game;
use App\Services\PreferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PreferenceController extends Controller
{
    public function __construct(
        private PreferenceService $preferenceService,
    ) {}

    public function show(Request $request, Friend $friend): Response
    {
        $this->authorize("managePreferences", $friend);

        $friend->load("games");

        $games = Game::visibleTo($request->user()->id)->orderBy("name")->get();

        return Inertia::render("Preferences/Edit", [
            "friend" => $friend,
            "games" => $games,
            "redirectTo" => $request->query("redirect_to"),
        ]);
    }

    public function update(PreferenceRequest $request, Friend $friend, SyncPreferencesAction $action): RedirectResponse
    {
        $this->authorize("managePreferences", $friend);

        $action->execute($friend, $request->validated()["ratings"] ?? []);

        $redirectTo = $request->input("redirect_to");

        if ($redirectTo) {
            $parsed = parse_url($redirectTo);
            $path = $parsed["path"] ?? "";

            if (str_starts_with($path, "/")) {
                return Redirect::to($path);
            }
        }

        return Redirect::route("friends.index");
    }
}
