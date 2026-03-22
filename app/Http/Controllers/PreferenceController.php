<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PreferenceController extends Controller
{
    public function show(Request $request, Friend $friend): Response
    {
        if ($friend->user_id !== $request->user()->id) {
            abort(403);
        }

        $friend->load("games");

        $games = Game::where("user_id", $request->user()->id)
            ->orWhere("is_shared", true)
            ->orderBy("name")
            ->get();

        return Inertia::render("Preferences/Edit", [
            "friend" => $friend,
            "games" => $games,
        ]);
    }

    public function update(Request $request, Friend $friend): RedirectResponse
    {
        if ($friend->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            "ratings" => ["array"],
            "ratings.*.game_id" => ["required", "integer", "exists:games,id"],
            "ratings.*.rating" => ["required", "integer", "min:1", "max:10"],
        ]);

        $data = collect($request->ratings)->mapWithKeys(fn($item) => [$item["game_id"] => ["rating" => $item["rating"]]])->toArray();

        $friend->games()->sync($data);

        return Redirect::route("friends.index");
    }
}
