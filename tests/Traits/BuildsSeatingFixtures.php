<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Friend;
use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

/**
 * A reusable trait that provides factory helpers for building in-memory
 * Friend and Game models without touching the database. Any test class
 * that needs to work with SeatingService can pull this in with `use`.
 *
 * The key idea: Laravel's factory ->make() method creates a fully
 * hydrated model instance without persisting it, and setRelation()
 * manually injects relationship data as if Eloquent had loaded it.
 * Together, they let us simulate a real object graph in pure memory.
 */
trait BuildsSeatingFixtures
{
    // This counter gives each model a unique ID within a test run,
    // replacing the auto-increment that a real database would provide.
    // It's declared here in the trait so every class using the trait
    // gets its own copy of it — they won't interfere with each other.
    private int $nextId = 1;

    /**
     * Call this in your setUp() method to reset the ID counter between
     * tests. Without this, IDs would keep incrementing across test methods,
     * which could cause hard-to-debug failures when tests rely on specific IDs.
     *
     * Example setUp():
     *   protected function setUp(): void {
     *       parent::setUp();
     *       $this->resetFixtureIds(); // <-- call this
     *       $this->service = new SeatingService();
     *   }
     */
    private function resetFixtureIds(): void
    {
        $this->nextId = 1;
    }

    /**
     * Generates the next unique ID and advances the counter.
     * Private because only the other helpers in this trait need it —
     * test classes interact with makeFriend() and makeGame(), not this.
     */
    private function nextId(): int
    {
        return $this->nextId++;
    }

    /**
     * Creates an in-memory Friend using the factory definition.
     * All realistic attribute data (names, email, etc.) is generated
     * by the FriendFactory — we just override user_id to keep things
     * consistent, and inject the games relation manually.
     *
     * @param array<Game> $games Pass game instances built with withRating()
     *                           so that pivot data is already attached.
     */
    private function makeFriend(array $games = []): Friend
    {
        $friend = Friend::factory()->make(["user_id" => 1]);
        $friend->id = $this->nextId();

        // setRelation() tells Eloquent this relation is already loaded.
        // The service checks relationLoaded('games') before querying,
        // so this prevents any accidental database calls during tests.
        $friend->setRelation("games", new Collection($games));

        return $friend;
    }

    /**
     * Creates an in-memory Game using the factory definition.
     * We always override min/max players explicitly because those values
     * are the core constraint the SeatingService logic depends on — leaving
     * them to chance from the factory would make tests non-deterministic.
     */
    private function makeGame(int $min, int $max): Game
    {
        $game = Game::factory()->make([
            "user_id" => 1,
            "min_players" => $min,
            "max_players" => $max,
        ]);

        $game->id = $this->nextId();

        return $game;
    }

    /**
     * Attaches a pivot rating to a clone of the given game, ready to be
     * placed inside a friend's games relation.
     *
     * We clone so that the same base Game can be reused across multiple
     * friends — each friend needs their own copy with their own rating.
     * Without cloning, setting the pivot on one friend's game would
     * silently overwrite another friend's rating for the same object.
     *
     * Usage:
     *   $game  = $this->makeGame(min: 2, max: 4);
     *   $alice = $this->makeFriend([$this->withRating($game, 5)]);
     *   $bob   = $this->makeFriend([$this->withRating($game, 2)]);
     *   // alice and bob both "have" the game but with different ratings
     */
    private function withRating(Game $game, int $rating): Game
    {
        $clone = clone $game;

        // We use stdClass here because the service only reads ->pivot->rating.
        // It never checks the class type of the pivot, so a plain object works
        // perfectly and avoids the overhead of constructing a real Pivot model.
        $pivot = new stdClass();
        $pivot->rating = $rating;
        $clone->setRelation("pivot", $pivot);

        return $clone;
    }
}
