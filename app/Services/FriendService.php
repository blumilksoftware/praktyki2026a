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
     * @return array{id: int, name: string, match_type: string, email?: string}|null
     */
    public function findDuplicate(int $userId, string $firstName, string $lastName, ?string $email): ?array
    {
        $nameMatch = Friend::findDuplicateByName($userId, $firstName, $lastName);

        if ($nameMatch !== null) {
            return [
                "id" => $nameMatch->id,
                "name" => "{$nameMatch->first_name} {$nameMatch->last_name}",
                "match_type" => "name",
            ];
        }

        if ($email) {
            $emailMatch = Friend::findDuplicateByEmail($userId, $email);

            if ($emailMatch !== null) {
                return [
                    "id" => $emailMatch->id,
                    "name" => "{$emailMatch->first_name} {$emailMatch->last_name}",
                    "email" => $emailMatch->email,
                    "match_type" => "email",
                ];
            }
        }

        return null;
    }

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
