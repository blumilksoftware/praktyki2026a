<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Friend;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class FriendController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = min(max($request->integer("per_page", 10), 10), 50);

        $friends = Friend::where("user_id", $request->user()->id)
            ->orderBy("last_name")
            ->orderBy("first_name")
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render("Friends/Index", [
            "friends" => [
                "data" => $friends->items(),
                "meta" => [
                    "current_page" => $friends->currentPage(),
                    "last_page" => $friends->lastPage(),
                    "per_page" => $friends->perPage(),
                    "total" => $friends->total(),
                    "from" => $friends->firstItem(),
                    "to" => $friends->lastItem(),
                ],
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render("Friends/Create");
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "first_name" => ["required", "string", "max:255"],
            "last_name" => ["required", "string", "max:255"],
            "email" => ["nullable", "email", "max:255"],
        ]);

        Friend::create([
            ...$validated,
            "user_id" => $request->user()->id,
        ]);

        return Redirect::route("friends.index");
    }

    public function edit(Request $request, Friend $friend): Response
    {
        if ($friend->user_id !== $request->user()->id) {
            abort(403);
        }

        return Inertia::render("Friends/Edit", [
            "friend" => $friend,
        ]);
    }

    public function update(Request $request, Friend $friend): RedirectResponse
    {
        if ($friend->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            "first_name" => ["required", "string", "max:255"],
            "last_name" => ["required", "string", "max:255"],
            "email" => ["nullable", "email", "max:255"],
        ]);

        $friend->update($validated);

        return Redirect::route("friends.index");
    }

    public function destroy(Request $request, Friend $friend): RedirectResponse
    {
        if ($friend->user_id !== $request->user()->id) {
            abort(403);
        }

        $friend->delete();

        return Redirect::route("friends.index");
    }
}
