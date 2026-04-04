<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Http\Requests\FriendRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class FriendController extends Controller
{
    public function index(Request $request): Response
    {
        $friends = Friend::where("user_id", $request->user()->id)
            ->orderBy("last_name")
            ->get();

        return Inertia::render("Friends/Index", [
            "friends" => $friends,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render("Friends/Create");
    }

    public function store(FriendRequest $request): RedirectResponse
    {
        Friend::create([
            ...$request->validated(),
            "user_id" => $request->user()->id,
        ]);

        return Redirect::route("friends.index");
    }

    public function edit(Friend $friend): Response
    {
        $this->authorize("update", $friend);

        return Inertia::render("Friends/Edit", [
            "friend" => $friend,
        ]);
    }

    public function update(FriendRequest $request, Friend $friend): RedirectResponse
    {
        $this->authorize("update", $friend);

        $friend->update($request->validated());

        return Redirect::route("friends.index");
    }

    public function destroy(Friend $friend): RedirectResponse
    {
        $this->authorize("delete", $friend);

        $friend->delete();

        return Redirect::route("friends.index");
    }
}
