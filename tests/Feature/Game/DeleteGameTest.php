<?php

declare(strict_types=1);

use App\Models\Game;
use App\Models\User;

test("user can delete their own game", function (): void {
    $user = User::factory()->create();
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->delete("/games/{$game->id}")
        ->assertRedirect("/games");

    $this->assertDatabaseMissing("games", ["id" => $game->id]);
});

test("delete game requires auth", function (): void {
    $game = Game::factory()->create();

    $this->delete("/games/{$game->id}")
        ->assertRedirect("/login");
});

test("user cannot delete another user's game", function (): void {
    $user = User::factory()->create();
    $otherGame = Game::factory()->userAdded()->create();

    $this->actingAs($user)
        ->delete("/games/{$otherGame->id}")
        ->assertForbidden();

    $this->assertDatabaseHas("games", ["id" => $otherGame->id]);
});

test("user cannot delete shared game", function (): void {
    $user = User::factory()->create();
    $sharedGame = Game::factory()->shared()->create();

    $this->actingAs($user)
        ->delete("/games/{$sharedGame->id}")
        ->assertForbidden();

    $this->assertDatabaseHas("games", ["id" => $sharedGame->id]);
});
