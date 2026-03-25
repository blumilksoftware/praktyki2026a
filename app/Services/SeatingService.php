<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Friend;
use App\Models\Game;
use Illuminate\Support\Collection;

class SeatingService
{
    public function arrange(Collection $friends, Collection $games): array
    {
        $friends->loadMissing("games");

        $unseated = collect();
        $tables = [];
        $remaining = $friends->keyBy("id");

        while ($remaining->isNotEmpty()) {
            $best = $this->findBestTable($remaining, $games);

            if ($best === null) {
                $unseated = $unseated->merge($remaining);

                break;
            }

            $tables[] = $best;

            foreach ($best["friends"] as $friend) {
                $remaining->forget($friend->id);
            }
        }

        return ["tables" => $tables, "unseated" => $unseated];
    }

    private function findBestTable(Collection $remaining, Collection $games): ?array
    {
        $bestScore = -1;
        $bestTable = null;

        foreach ($games as $game) {
            $eligible = $remaining->filter(fn(Friend $friend) => $friend->games->contains("id", $game->id));

            $count = $eligible->count();

            if ($count < $game->min_players) {
                continue;
            }

            if ($count > $game->max_players) {
                $eligible = $this->topRatedFriends($eligible, $game, $game->max_players);
                $count = $eligible->count();
            }

            $avgRating = $this->averageRating($eligible, $game);
            $coverageWeight = 0.7;
            $satisfactionWeight = 0.3;
            $score = $this->compositeScore($count, $avgRating, $remaining->count(), $coverageWeight, $satisfactionWeight);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestTable = [
                    "game" => $game,
                    "friends" => $eligible,
                    "avg_rating" => round($avgRating, 2),
                ];
            }
        }

        if ($bestTable === null) {
            $bestTable = $this->forceAssign($remaining, $games);
        }

        return $bestTable;
    }

    private function compositeScore(int $count, float $avgRating, int $totalRemaining, float $coverageWeight, float $satisfactionWeight): float
    {
        $coverageScore = $totalRemaining > 0 ? $count / $totalRemaining : 0;
        $satisfactionScore = $avgRating / 10.0;

        return ($coverageWeight * $coverageScore) + ($satisfactionWeight * $satisfactionScore);
    }

    private function topRatedFriends(Collection $eligible, Game $game, int $limit): Collection
    {
        return $eligible
            ->sortByDesc(fn(Friend $f) => $this->ratingFor($f, $game))
            ->take($limit)
            ->values();
    }

    private function forceAssign(Collection $remaining, Collection $games): ?array
    {
        $bestGame = null;
        $bestFriends = collect();

        foreach ($games as $game) {
            $eligible = $remaining->filter(
                fn(Friend $f) => $f->games->contains("id", $game->id),
            );

            if ($eligible->count() > $bestFriends->count()) {
                $bestGame = $game;
                $bestFriends = $eligible;
            }
        }

        if ($bestGame === null || $bestFriends->isEmpty()) {
            return null;
        }

        if ($bestFriends->count() > $bestGame->max_players) {
            $bestFriends = $this->topRatedFriends($bestFriends, $bestGame, $bestGame->max_players);
        }

        return [
            "game" => $bestGame,
            "friends" => $bestFriends,
            "avg_rating" => round($this->averageRating($bestFriends, $bestGame), 2),
        ];
    }

    private function averageRating(Collection $friends, Game $game): float
    {
        if ($friends->isEmpty()) {
            return 0.0;
        }

        $total = $friends->sum(fn(Friend $f) => $this->ratingFor($f, $game));

        return $total / $friends->count();
    }

    private function ratingFor(Friend $friend, Game $game): int
    {
        $pivot = $friend->games->find($game->id)?->pivot;

        return $pivot?->rating ?? 0;
    }
}
