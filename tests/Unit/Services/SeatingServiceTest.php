<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Friend;
use App\Models\Game;
use App\Services\SeatingService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;
use stdClass;

class SeatingServiceTest extends TestCase
{
    private SeatingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SeatingService();
    }

    // =========================================================================
    // TEST GROUP 1: Happy path
    // =========================================================================

    /**
     * The simplest possible case: two friends, one shared game, min=2.
     * They should form exactly one table with the correct average rating.
     */
    public function testTwoFriendsSharingOneGameFormASingleTable(): void
    {
        $game = $this->makeGame(id: 1, min: 2, max: 4);

        // Each friend gets their own copy of the game with their own rating.
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

        // Average of ratings 4 and 3 is 3.5.
        $this->assertEquals(3.5, $table["avg_rating"]);
    }

    /**
     * When friends have completely different game preferences, the algorithm
     * should run multiple rounds and create a separate table for each group.
     * This tests that the while loop in arrange() correctly exhausts the pool.
     */
    public function testFriendsWithDifferentPreferencesSitAtSeparateTables(): void
    {
        $chess = $this->makeGame(id: 1, min: 2, max: 4);
        $poker = $this->makeGame(id: 2, min: 2, max: 6);

        // Alice and Bob only know chess.
        $alice = $this->makeFriend(id: 1, games: [$this->gameWithRating($chess, 5)]);
        $bob = $this->makeFriend(id: 2, games: [$this->gameWithRating($chess, 5)]);

        // Carol and Dave only know poker.
        $carol = $this->makeFriend(id: 3, games: [$this->gameWithRating($poker, 5)]);
        $dave = $this->makeFriend(id: 4, games: [$this->gameWithRating($poker, 5)]);

        $result = $this->service->arrange(
            new Collection([$alice, $bob, $carol, $dave]),
            new Collection([$chess, $poker]),
        );

        $this->assertCount(2, $result["tables"], "Expected two separate tables");
        $this->assertCount(0, $result["unseated"]);

        $totalSeated = collect($result["tables"])->sum(fn($t) => $t["friends"]->count());
        $this->assertEquals(4, $totalSeated, "All four friends should be seated");
    }

    // =========================================================================
    // TEST GROUP 2: Constraint enforcement
    // =========================================================================

    /**
     * When more friends want to play than max_players allows, only the
     * most enthusiastic ones should get seats. This tests topRatedFriends().
     */
    public function testMaxPlayersIsRespectedAndHighestRatersChosen(): void
    {
        // A game that only fits 2 people.
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

    /**
     * When a game's min_players can't be met by normal scoring, the
     * force-assign fallback should kick in and seat the friends anyway.
     * Nobody should be left stranded.
     */
    public function testGameBelowMinPlayersTriggersForceAssign(): void
    {
        // Needs 4 players, but only 2 friends are available.
        $game = $this->makeGame(id: 1, min: 4, max: 8);
        $alice = $this->makeFriend(id: 1, games: [$this->gameWithRating($game, 5)]);
        $bob = $this->makeFriend(id: 2, games: [$this->gameWithRating($game, 5)]);

        $result = $this->service->arrange(
            new Collection([$alice, $bob]),
            new Collection([$game]),
        );

        // Force-assign should have rescued them.
        $totalSeated = collect($result["tables"])->sum(fn($t) => $t["friends"]->count());
        $this->assertEquals(2, $totalSeated, "Force-assign should seat all friends");
        $this->assertCount(0, $result["unseated"]);
    }

    // =========================================================================
    // TEST GROUP 3: Invariants — properties that must always hold
    // =========================================================================

    /**
     * No friend should ever appear at two tables simultaneously.
     * This tests the core correctness of the pool-depletion mechanism.
     */
    public function testEachFriendAppearsInExactlyOneTable(): void
    {
        $gameA = $this->makeGame(id: 1, min: 2, max: 4);
        $gameB = $this->makeGame(id: 2, min: 2, max: 4);

        // Three friends per game, no overlap in preferences.
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

        // If any ID appears twice, array_unique shrinks the array.
        $this->assertEquals(
            count($allSeatedIds),
            count(array_unique($allSeatedIds)),
            "A friend appeared at more than one table — duplication detected",
        );
    }

    /**
     * Every friend passed in must appear somewhere in the output —
     * either seated or explicitly in 'unseated'. Silent disappearance
     * would be a serious bug.
     */
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

    // =========================================================================
    // TEST GROUP 4: Edge cases
    // =========================================================================

    /**
     * An empty friends list is a valid input. The service should return
     * empty collections gracefully rather than crashing.
     */
    public function testEmptyFriendsReturnsEmptyResult(): void
    {
        $game = $this->makeGame(id: 1, min: 2, max: 4);
        $result = $this->service->arrange(new Collection(), new Collection([$game]));

        $this->assertCount(0, $result["tables"]);
        $this->assertCount(0, $result["unseated"]);
    }

    /**
     * An empty games list is also valid. Nobody can be seated, so
     * the result should have zero tables — and not crash.
     */
    public function testEmptyGamesReturnsNoTables(): void
    {
        $alice = $this->makeFriend(id: 1, games: []);
        $result = $this->service->arrange(new Collection([$alice]), new Collection());

        $this->assertCount(0, $result["tables"]);
    }

    /**
     * A friend with no ratings at all has no data for force-assign to work with.
     * They should land in 'unseated' rather than causing an error.
     */
    public function testFriendWithNoRatingsEndsUpUnseated(): void
    {
        $game = $this->makeGame(id: 1, min: 2, max: 4);

        // Alice has a rating, Bob has none at all.
        $alice = $this->makeFriend(id: 1, games: [$this->gameWithRating($game, 4)]);
        $bob = $this->makeFriend(id: 2, games: []); // no ratings

        $result = $this->service->arrange(
            new Collection([$alice, $bob]),
            new Collection([$game]),
        );

        $unseatedIds = $result["unseated"]->pluck("id")->toArray();
        $this->assertContains(2, $unseatedIds, "Bob should be unseated — he has no game preferences");
    }

    /**
     * A rating of 1 is the lowest valid value but still counts as a preference.
     * The service should not treat it the same as "no rating".
     */
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

    // -------------------------------------------------------------------------
    // IN-MEMORY MODEL HELPERS
    //
    // Instead of hitting a database, we build Eloquent model instances
    // directly in memory. Model::make() creates a model without saving it.
    // setRelation() manually injects a loaded relationship — this is exactly
    // what Eloquent does internally after a ->with('games') eager load,
    // so the service code sees no difference.
    // -------------------------------------------------------------------------

    /**
     * Creates an in-memory Friend with a given ID and a pre-loaded 'games'
     * relation containing whichever games you pass in.
     *
     * The $id is set manually because the service uses ->id comparisons.
     * Without a database, auto-increment doesn't exist, so we assign IDs
     * ourselves to keep them unique and predictable.
     */
    private function makeFriend(int $id, array $games = []): Friend
    {
        $friend = Friend::make([
            "first_name" => "Friend",
            "last_name" => (string)$id,
            "user_id" => 1,
        ]);

        // Force the primary key — no database means no auto-increment.
        $friend->id = $id;

        // setRelation() tells Eloquent "this relation is already loaded,
        // don't bother querying for it." The service calls ->load('games')
        // only when it isn't already loaded, but since we pass pre-loaded
        // collections, this will be treated as already resolved.
        $friend->setRelation("games", new Collection($games));

        return $friend;
    }

    /**
     * Creates an in-memory Game with a given ID and player count constraints.
     * The pivot 'rating' is attached to the Game model instance itself,
     * which mimics what Eloquent does when you call ->withPivot('rating').
     */
    private function makeGame(int $id, int $min, int $max): Game
    {
        $game = Game::make([
            "name" => "Game " . $id,
            "user_id" => 1,
            "min_players" => $min,
            "max_players" => $max,
            "is_shared" => false,
        ]);

        $game->id = $id;

        return $game;
    }

    /**
     * This is the most important helper: it attaches a pivot rating to a
     * Game instance, so that when the service calls $friend->games->find($id)
     * and then reads ->pivot->rating, it finds the value we set here.
     *
     * Eloquent pivot data lives on the game instance inside the relation,
     * not on the friend. So we clone the game, add a fake pivot object to
     * the clone, and return it — ready to be placed into a friend's games
     * relation.
     */
    private function gameWithRating(Game $game, int $rating): Game
    {
        // We replicate the game so the original isn't mutated. This matters
        // when the same Game is shared across multiple friends with different
        // ratings — each friend needs their own copy with their own pivot.
        $clone = clone $game;

        // Simulate the pivot model that Eloquent attaches when you use
        // ->withPivot('rating'). We use a plain stdClass because the service
        // only reads ->pivot->rating — it doesn't care about the class type.
        $pivot = new stdClass();
        $pivot->rating = $rating;

        $clone->setRelation("pivot", $pivot);

        return $clone;
    }
}
// test_basic_seating_groups_friends_who_share_a_game
// test_max_players_respected_and_top_raters_preferred
// test_min_players_not_met_triggers_force_assign
// test_each_friend_appears_in_exactly_one_table
// test_empty_friends_returns_empty_result
