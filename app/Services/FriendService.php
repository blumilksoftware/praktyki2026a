<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Friend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FriendService
{
    private const ALLOWED_SORTS = ["first_name", "last_name", "email"];
    private const DEFAULT_SORT = "last_name";

    public function findDuplicate(int $userId, string $firstName, string $lastName, ?string $email, ?int $excludeId = null): ?array
    {
        $nameMatch = Friend::findDuplicateByName($userId, $firstName, $lastName, $excludeId);

        if ($nameMatch !== null) {
            return [
                "id" => $nameMatch->id,
                "name" => "{$nameMatch->first_name} {$nameMatch->last_name}",
                "match_type" => "name",
            ];
        }

        if ($email) {
            $emailMatch = Friend::findDuplicateByEmail($userId, $email, $excludeId);

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

    public function paginatedIndex(Request $request, int $userId): array
    {
        $perPage = min(max($request->integer("per_page", 10), 10), 50);
        $sort = in_array($request->input("sort"), self::ALLOWED_SORTS, true)
            ? $request->input("sort")
            : self::DEFAULT_SORT;
        $direction = $request->input("direction") === "desc" ? "desc" : "asc";
        $search = trim($request->string("search")->value());

        $query = Friend::where("user_id", $userId);

        if ($search !== "") {
            $query->where(function (Builder $subQuery) use ($search): void {
                $subQuery->where("first_name", "ilike", "%{$search}%")
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
