<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Friend;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FriendService
{
    private const ALLOWED_SORTS = ["first_name", "last_name", "email"];
    private const DEFAULT_SORT = "last_name";

    /**
     * @return array{paginator: LengthAwarePaginator, sort: string, direction: string, search: string}
     */
    public function paginatedIndex(Request $request, int $userId): array
    {
        $perPage = min(max($request->integer("per_page", 10), 10), 50);
        $sort = in_array($request->input("sort"), self::ALLOWED_SORTS, true)
            ? $request->input("sort")
            : self::DEFAULT_SORT;
        $direction = $request->input("direction") === "desc" ? "desc" : "asc";
        $search = trim((string)$request->input("search", ""));

        $query = Friend::where("user_id", $userId);

        if ($search !== "") {
            $query->where(function ($q) use ($search): void {
                $q->where("first_name", "ilike", "%{$search}%")
                    ->orWhere("last_name", "ilike", "%{$search}%")
                    ->orWhere("email", "ilike", "%{$search}%");
            });
        }

        $paginator = $query
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return compact("paginator", "sort", "direction", "search");
    }
}
