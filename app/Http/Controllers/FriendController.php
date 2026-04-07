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

        $allowedSorts = ["last_name", "first_name", "email"];
        $sortColumn = in_array($request->input("sort"), $allowedSorts, true)
            ? $request->input("sort")
            : "last_name";
        $sortDirection = $request->input("direction") === "desc" ? "desc" : "asc";

        $search = trim((string)$request->input("search", ""));

        $query = Friend::where("user_id", $request->user()->id);

        if ($search !== "") {
            $query->where(function ($q) use ($search): void {
                $q->where("first_name", "ilike", "%{$search}%")
                    ->orWhere("last_name", "ilike", "%{$search}%")
                    ->orWhere("email", "ilike", "%{$search}%");
            });
        }

        $query->orderBy($sortColumn, $sortDirection);

        if ($sortColumn === "last_name") {
            $query->orderBy("first_name", $sortDirection);
        } elseif ($sortColumn === "first_name") {
            $query->orderBy("last_name", $sortDirection);
        }

        $friends = $query->paginate($perPage)->withQueryString();

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
                    "sort" => $sortColumn,
                    "direction" => $sortDirection,
                    "search" => $search,
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

    public function edit(Friend $friend): Response
    {
        $this->authorize("update", $friend);

        return Inertia::render("Friends/Edit", [
            "friend" => $friend,
        ]);
    }

    public function update(Request $request, Friend $friend): RedirectResponse
    {
        $this->authorize("update", $friend);

        $validated = $request->validate([
            "first_name" => ["required", "string", "max:255"],
            "last_name" => ["required", "string", "max:255"],
            "email" => ["nullable", "email", "max:255"],
        ]);

        $friend->update($validated);

        return Redirect::route("friends.index");
    }

    public function destroy(Friend $friend): RedirectResponse
    {
        $this->authorize("delete", $friend);

        $friend->delete();

        return Redirect::route("friends.index");
    }
}
