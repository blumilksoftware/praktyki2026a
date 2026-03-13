<?php

declare(strict_types=1);

use App\Models\Game;
use App\Models\User;

test("games index page requires auth", function (): void {
    $this->get("/games")->assertRedirect("/login");
});

test("games index page is displayed", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get("/games")->assertOk();
});

test("games create page is displayed", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get("/games/create")->assertOk();
});

test("user can store a game", function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post("/games", [
        "name" => "Catan",
        "min_players" => 2,
        "max_players" => 4,
    ]);

    $response->assertRedirect("/games");
    $this->assertDatabaseHas("games", ["name" => "Catan", "user_id" => $user->id]);
});

test("store game requires name", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->post("/games", [
        "min_players" => 2,
        "max_players" => 4,
    ])->assertSessionHasErrors("name");
});

test("store game requires max_players gte min_players", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->post("/games", [
        "name" => "Catan",
        "min_players" => 5,
        "max_players" => 2,
    ])->assertSessionHasErrors("max_players");
});

test("user can edit their own game", function (): void {
    $user = User::factory()->create();
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)->get("/games/{$game->id}/edit")->assertOk();
});

test("user cannot edit another user's game", function (): void {
    $user = User::factory()->create();
    $otherGame = Game::factory()->userAdded()->create();

    $this->actingAs($user)->get("/games/{$otherGame->id}/edit")->assertForbidden();
});

test("user cannot edit shared game", function (): void {
    $user = User::factory()->create();
    $sharedGame = Game::factory()->shared()->create();

    $this->actingAs($user)->get("/games/{$sharedGame->id}/edit")->assertForbidden();
});

test("user can update their own game", function (): void {
    $user = User::factory()->create();
    $game = Game::factory()->create(["user_id" => $user->id]);

    $response = $this->actingAs($user)->put("/games/{$game->id}", [
        "name" => "Updated Name",
        "min_players" => 2,
        "max_players" => 6,
    ]);

    $response->assertRedirect("/games");
    $this->assertDatabaseHas("games", ["id" => $game->id, "name" => "Updated Name"]);
});

test("user can delete their own game", function (): void {
    $user = User::factory()->create();
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)->delete("/games/{$game->id}")->assertRedirect("/games");
    $this->assertDatabaseMissing("games", ["id" => $game->id]);
});

test("user cannot delete another user's game", function (): void {
    $user = User::factory()->create();
    $otherGame = Game::factory()->userAdded()->create();

    $this->actingAs($user)->delete("/games/{$otherGame->id}")->assertForbidden();
});

test("index shows user's games and shared games", function (): void {
    $user = User::factory()->create();
    Game::factory()->create(["user_id" => $user->id, "name" => "My Game"]);
    Game::factory()->shared()->create(["name" => "Shared Game"]);
    Game::factory()->userAdded()->create(["name" => "Other User Game"]);

    $response = $this->actingAs($user)->get("/games");

    $response->assertOk();
    $response->assertInertia(fn($page) => $page
        ->component("Games/Index")
        ->has("games", 2));
});
