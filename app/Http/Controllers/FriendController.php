<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateFriendAction;
use App\Actions\UpdateFriendAction;
use App\Http\Requests\CheckDuplicateFriendRequest;
use App\Http\Requests\FriendRequest;
use App\Models\Friend;
use App\Services\FriendService;
use App\Traits\BuildsPaginationMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class FriendController extends Controller
{
    use BuildsPaginationMeta;

    public function __construct(
        private FriendService $friendService,
    ) {}

    public function index(Request $request): Response
    {
        ["paginator" => $paginator, "sort" => $sort, "direction" => $direction, "search" => $search]
            = $this->friendService->paginatedIndex($request, $request->user()->id);

        return Inertia::render("Friends/Index", [
            "friends" => [
                "data" => $paginator->items(),
                "meta" => $this->paginationMeta($paginator, compact("sort", "direction", "search")),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render("Friends/Create");
    }

    public function checkDuplicate(CheckDuplicateFriendRequest $request): JsonResponse
    {
        $duplicate = $this->friendService->findDuplicate(
            $request->user()->id,
            $request->input("first_name"),
            $request->input("last_name"),
            $request->input("email"),
            $request->integer("exclude_id") ?: null,
        );

        return response()->json(compact("duplicate"));
    }

    public function store(FriendRequest $request, CreateFriendAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated());

        return Redirect::route("friends.index");
    }

    public function edit(Friend $friend): Response
    {
        $this->authorize("update", $friend);

        return Inertia::render("Friends/Edit", [
            "friend" => $friend,
        ]);
    }

    public function update(FriendRequest $request, Friend $friend, UpdateFriendAction $action): RedirectResponse
    {
        $this->authorize("update", $friend);

        $action->execute($friend, $request->validated());

        return Redirect::route("friends.index");
    }

    public function destroy(Friend $friend): RedirectResponse
    {
        $this->authorize("delete", $friend);

        $friend->delete();

        return Redirect::route("friends.index");
    }
}
