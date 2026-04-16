<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Friend;
use App\Models\Game;
use App\Services\SeatingService;
use Illuminate\Database\Eloquent\Collection;
use stdClass;
use Tests\TestCase;

class SeatingServiceTest extends TestCase
{
    private SeatingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SeatingService();
    }

    public function testTwoFriendsSharingOneGameFormASingleTable(): void
    {
        $game = $this->makeGame(id: 1, min: 2, max: 4);

        $alice = $this->makeFriend(id: 1, games: [$this->gameWithRating($game, 4)]);
        $bob = $this->makeFriend(id: 2, games: [$this->gameWithRating($game, 3)]);

        $result = $this->service->arrange(
            new Collection([$alice, $bob]),
            new Collection([$game]),
        );

        $this->assertCount(1, $result["tables"], "Expected exactly one table");
        $this->assertCount(0, $result["unseated"], "Expected nobody unseated");

        $table = $result["tables"][0];

        $this->assertEquals(1, $table["game"]->id);
        $this->assertCount(2, $table["friends"]);

        $this->assertEquals(3.5, $table["avg_rating"]);
    }

    public function testFriendsWithDifferentPreferencesSitAtSeparateTables(): void
    {
        $chess = $this->makeGame(id: 1, min: 2, max: 4);
        $poker = $this->makeGame(id: 2, min: 2, max: 6);

        $alice = $this->makeFriend(id: 1, games: [$this->gameWithRating($chess, 5)]);
        $bob = $this->makeFriend(id: 2, games: [$this->gameWithRating($chess, 5)]);

        $carol = $this->makeFriend(id: 3, games: [$this->gameWithRating($poker, 5)]);
        $dave = $this->makeFriend(id: 4, games: [$this->gameWithRating($poker, 5)]);

        $result = $this->service->arrange(
            new Collection([$alice, $bob, $carol, $dave]),
            new Collection([$chess, $poker]),
            0.6,
            false,
        );

        $this->assertCount(2, $result["tables"], "Expected two separate tables");
        $this->assertCount(0, $result["unseated"]);

        $totalSeated = collect($result["tables"])->sum(fn($t) => $t["friends"]->count());
        $this->assertEquals(4, $totalSeated, "All four friends should be seated");
    }

    public function testMaxPlayersIsRespectedAndHighestRatersChosen(): void
    {
        $game = $this->makeGame(id: 1, min: 1, max: 2);

        $superfan = $this->makeFriend(id: 1, games: [$this->gameWithRating($game, 5)]);
        $casual = $this->makeFriend(id: 2, games: [$this->gameWithRating($game, 3)]);
        $reluctant = $this->makeFriend(id: 3, games: [$this->gameWithRating($game, 1)]);

        $result = $this->service->arrange(
            new Collection([$superfan, $casual, $reluctant]),
            new Collection([$game]),
        );

        $seatedIds = $result["tables"][0]["friends"]->pluck("id")->toArray();

        $this->assertCount(2, $seatedIds, "Exactly 2 people should be seated (max_players)");
        $this->assertContains(1, $seatedIds, "Superfan (rating 5) should be seated");
        $this->assertContains(2, $seatedIds, "Casual (rating 3) should be seated");
        $this->assertNotContains(3, $seatedIds, "Reluctant (rating 1) should NOT be seated");
    }

    public function testGameBelowMinPlayersTriggersForceAssign(): void
    {
        $game = $this->makeGame(id: 1, min: 4, max: 8);
        $alice = $this->makeFriend(id: 1, games: [$this->gameWithRating($game, 5)]);
        $bob = $this->makeFriend(id: 2, games: [$this->gameWithRating($game, 5)]);

        $result = $this->service->arrange(
            new Collection([$alice, $bob]),
            new Collection([$game]),
        );

        $totalSeated = collect($result["tables"])->sum(fn($t) => $t["friends"]->count());
        $this->assertEquals(2, $totalSeated, "Force-assign should seat all friends");
        $this->assertCount(0, $result["unseated"]);
    }

    public function testEachFriendAppearsInExactlyOneTable(): void
    {
        $gameA = $this->makeGame(id: 1, min: 2, max: 4);
        $gameB = $this->makeGame(id: 2, min: 2, max: 4);

        $friends = new Collection([
            $this->makeFriend(id: 1, games: [$this->gameWithRating($gameA, 4)]),
            $this->makeFriend(id: 2, games: [$this->gameWithRating($gameA, 4)]),
            $this->makeFriend(id: 3, games: [$this->gameWithRating($gameA, 4)]),
            $this->makeFriend(id: 4, games: [$this->gameWithRating($gameB, 4)]),
            $this->makeFriend(id: 5, games: [$this->gameWithRating($gameB, 4)]),
            $this->makeFriend(id: 6, games: [$this->gameWithRating($gameB, 4)]),
        ]);

        $result = $this->service->arrange($friends, new Collection([$gameA, $gameB]));

        $allSeatedIds = collect($result["tables"])
            ->flatMap(fn($t) => $t["friends"]->pluck("id"))
            ->toArray();

        $this->assertEquals(
            count($allSeatedIds),
            count(array_unique($allSeatedIds)),
            "A friend appeared at more than one table — duplication detected",
        );
    }

    public function testAllFriendsAreAccountedForInOutput(): void
    {
        $game = $this->makeGame(id: 1, min: 2, max: 4);
        $alice = $this->makeFriend(id: 1, games: [$this->gameWithRating($game, 3)]);
        $bob = $this->makeFriend(id: 2, games: [$this->gameWithRating($game, 3)]);

        $result = $this->service->arrange(
            new Collection([$alice, $bob]),
            new Collection([$game]),
        );

        $seatedCount = collect($result["tables"])->sum(fn($t) => $t["friends"]->count());
        $unseatedCount = $result["unseated"]->count();

        $this->assertEquals(2, $seatedCount + $unseatedCount, "All friends must be accounted for");
    }

    public function testEmptyFriendsReturnsEmptyResult(): void
    {
        $game = $this->makeGame(id: 1, min: 2, max: 4);
        $result = $this->service->arrange(new Collection(), new Collection([$game]));

        $this->assertCount(0, $result["tables"]);
        $this->assertCount(0, $result["unseated"]);
    }

    public function testEmptyGamesReturnsNoTables(): void
    {
        $alice = $this->makeFriend(id: 1, games: []);
        $result = $this->service->arrange(new Collection([$alice]), new Collection());

        $this->assertCount(0, $result["tables"]);
    }

    public function testFriendWithNoRatingsIsSeatedWithNeutralPreference(): void
    {
        $game = $this->makeGame(id: 1, min: 2, max: 4);

        $alice = $this->makeFriend(id: 1, games: [$this->gameWithRating($game, 4)]);
        $bob = $this->makeFriend(id: 2, games: []);

        $result = $this->service->arrange(
            new Collection([$alice, $bob]),
            new Collection([$game]),
        );

        $this->assertCount(1, $result["tables"]);
        $this->assertCount(0, $result["unseated"]);

        $seatedIds = $result["tables"][0]["friends"]->pluck("id")->toArray();
        $this->assertContains(1, $seatedIds, "Alice should be seated");
        $this->assertContains(2, $seatedIds, "Bob (unrated) should be seated with neutral preference");
        $this->assertEquals(4.5, $result["tables"][0]["avg_rating"]);
    }

    public function testFriendWhoRatedOtherGamesIsNotEligibleForUnratedGame(): void
    {
        $chess = $this->makeGame(id: 1, min: 2, max: 4);
        $poker = $this->makeGame(id: 2, min: 2, max: 4);

        $alice = $this->makeFriend(id: 1, games: [$this->gameWithRating($chess, 5)]);
        $bob = $this->makeFriend(id: 2, games: [$this->gameWithRating($poker, 5)]);

        $result = $this->service->arrange(
            new Collection([$alice, $bob]),
            new Collection([$chess]),
            0.6,
            false,
        );

        $unseatedIds = $result["unseated"]->pluck("id")->toArray();
        $this->assertContains(2, $unseatedIds, "Bob rated other games but not chess — should be unseated");
    }

    public function testMinimumRatingOfOneStillQualifiesForSeating(): void
    {
        $game = $this->makeGame(id: 1, min: 2, max: 4);
        $alice = $this->makeFriend(id: 1, games: [$this->gameWithRating($game, 1)]);
        $bob = $this->makeFriend(id: 2, games: [$this->gameWithRating($game, 1)]);

        $result = $this->service->arrange(
            new Collection([$alice, $bob]),
            new Collection([$game]),
        );

        $this->assertCount(1, $result["tables"], "Even rating-1 friends should be seated");
        $this->assertEquals(1.0, $result["tables"][0]["avg_rating"]);
    }

    public function testTiedScoresCanProduceDifferentResults(): void
    {
        $chess = $this->makeGame(id: 1, min: 2, max: 2);
        $poker = $this->makeGame(id: 2, min: 2, max: 2);

        $alice = $this->makeFriend(id: 1, games: [$this->gameWithRating($chess, 5)]);
        $bob = $this->makeFriend(id: 2, games: [$this->gameWithRating($chess, 5)]);
        $carol = $this->makeFriend(id: 3, games: [$this->gameWithRating($poker, 5)]);
        $dave = $this->makeFriend(id: 4, games: [$this->gameWithRating($poker, 5)]);

        $firstGameIds = [];

        for ($i = 0; $i < 20; $i++) {
            $result = $this->service->arrange(
                new Collection([$alice, $bob, $carol, $dave]),
                new Collection([$chess, $poker]),
            );

            $firstGameIds[] = $result["tables"][0]["game"]->id;
        }

        $unique = array_unique($firstGameIds);
        $this->assertGreaterThanOrEqual(2, count($unique), "With tied scores, different orderings should appear over 20 runs");
    }

    public function testVariableTableSizesAreEvaluated(): void
    {
        $game = $this->makeGame(id: 1, min: 1, max: 4);

        $superfan = $this->makeFriend(id: 1, games: [$this->gameWithRating($game, 10)]);
        $casual1 = $this->makeFriend(id: 2, games: [$this->gameWithRating($game, 2)]);
        $casual2 = $this->makeFriend(id: 3, games: [$this->gameWithRating($game, 2)]);
        $casual3 = $this->makeFriend(id: 4, games: [$this->gameWithRating($game, 2)]);

        $result = $this->service->arrange(
            new Collection([$superfan, $casual1, $casual2, $casual3]),
            new Collection([$game]),
        );

        $table = $result["tables"][0];
        $this->assertLessThanOrEqual($game->max_players, $table["friends"]->count());
        $this->assertGreaterThanOrEqual($game->min_players, $table["friends"]->count());
    }

    private function makeFriend(int $id, array $games = []): Friend
    {
        $friend = Friend::make([
            "first_name" => "Friend",
            "last_name" => (string)$id,
            "user_id" => 1,
        ]);

        $friend->id = $id;

        $friend->setRelation("games", new Collection($games));

        return $friend;
    }

    private function makeGame(int $id, int $min, int $max): Game
    {
        $game = Game::make([
            "name" => "Game " . $id,
            "user_id" => 1,
            "min_players" => $min,
            "max_players" => $max,
            "is_shared" => false,
            "copies" => 1,
        ]);

        $game->id = $id;

        return $game;
    }

    private function gameWithRating(Game $game, int $rating): Game
    {
        $clone = clone $game;

        $pivot = new stdClass();
        $pivot->rating = $rating;

        $clone->setRelation("pivot", $pivot);

        return $clone;
    }
}
