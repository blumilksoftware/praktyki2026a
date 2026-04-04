<?php

declare(strict_types=1);

use App\Models\Game;
use App\Models\User;

test("user can edit their own game", function (): void {
    $user = User::factory()->create();
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->get("/games/{$game->id}/edit")
        ->assertOk();
});

test("user can update their own game", function (): void {
    $user = User::factory()->create();
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/games/{$game->id}", [
            "name" => "Updated Name",
            "min_players" => 2,
            "max_players" => 6,
        ])
        ->assertRedirect("/games");

    $this->assertDatabaseHas("games", [
        "id" => $game->id,
        "name" => "Updated Name",
    ]);
});

test("update game accepts max_players of exactly 100", function (): void {
    $user = User::factory()->create();
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/games/{$game->id}", [
            "name" => "Catan",
            "min_players" => 2,
            "max_players" => 100,
        ])
        ->assertRedirect("/games");

    $this->assertDatabaseHas("games", [
        "id" => $game->id,
        "max_players" => 100,
    ]);
});

test("edit game requires auth", function (): void {
    $game = Game::factory()->create();

    $this->get("/games/{$game->id}/edit")
        ->assertRedirect("/login");
});

test("update game requires auth", function (): void {
    $game = Game::factory()->create();

    $this->put("/games/{$game->id}", [
        "name" => "Updated",
        "min_players" => 2,
        "max_players" => 4,
    ])->assertRedirect("/login");
});

test("user cannot edit another user's game", function (): void {
    $user = User::factory()->create();
    $otherGame = Game::factory()->userAdded()->create();

    $this->actingAs($user)
        ->get("/games/{$otherGame->id}/edit")
        ->assertForbidden();
});

test("user cannot edit shared game", function (): void {
    $user = User::factory()->create();
    $sharedGame = Game::factory()->shared()->create();

    $this->actingAs($user)
        ->get("/games/{$sharedGame->id}/edit")
        ->assertForbidden();
});

test("user cannot update another user's game", function (): void {
    $user = User::factory()->create();
    $otherGame = Game::factory()->userAdded()->create();

    $this->actingAs($user)
        ->put("/games/{$otherGame->id}", [
            "name" => "Updated",
            "min_players" => 2,
            "max_players" => 4,
        ])
        ->assertForbidden();
});

test("user cannot update shared game", function (): void {
    $user = User::factory()->create();
    $sharedGame = Game::factory()->shared()->create();

    $this->actingAs($user)
        ->put("/games/{$sharedGame->id}", [
            "name" => "Updated",
            "min_players" => 2,
            "max_players" => 4,
        ])
        ->assertForbidden();
});

test("update game requires name", function (): void {
    $user = User::factory()->create();
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/games/{$game->id}", [
            "min_players" => 2,
            "max_players" => 4,
        ])
        ->assertSessionHasErrors("name");
});

test("update game requires max_players gte min_players", function (): void {
    $user = User::factory()->create();
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/games/{$game->id}", [
            "name" => "Catan",
            "min_players" => 5,
            "max_players" => 2,
        ])
        ->assertSessionHasErrors("max_players");
});

test("update game rejects max_players above 100", function (): void {
    $user = User::factory()->create();
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/games/{$game->id}", [
            "name" => "Catan",
            "min_players" => 2,
            "max_players" => 101,
        ])
        ->assertSessionHasErrors("max_players");
});
