<?php

declare(strict_types=1);

use App\Models\User;

test("games create page is displayed", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get("/games/create")->assertOk();
});

test("user can store a game", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/games", [
            "name" => "Catan",
            "min_players" => 2,
            "max_players" => 4,
        ])
        ->assertRedirect("/games");

    $this->assertDatabaseHas("games", [
        "name" => "Catan",
        "user_id" => $user->id,
    ]);
});

test("store game accepts max_players of exactly 100", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/games", [
            "name" => "Catan",
            "min_players" => 2,
            "max_players" => 100,
        ])
        ->assertRedirect("/games");

    $this->assertDatabaseHas("games", [
        "name" => "Catan",
        "max_players" => 100,
    ]);
});

test("store game requires auth", function (): void {
    $this->post("/games", [
        "name" => "Catan",
        "min_players" => 2,
        "max_players" => 4,
    ])->assertRedirect("/login");
});

test("store game requires name", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/games", ["min_players" => 2, "max_players" => 4])
        ->assertSessionHasErrors("name");
});

test("store game requires min_players", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/games", ["name" => "Catan", "max_players" => 4])
        ->assertSessionHasErrors("min_players");
});

test("store game requires max_players gte min_players", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/games", [
            "name" => "Catan",
            "min_players" => 5,
            "max_players" => 2,
        ])
        ->assertSessionHasErrors("max_players");
});

test("store game rejects min_players less than 1", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/games", [
            "name" => "Catan",
            "min_players" => 0,
            "max_players" => 4,
        ])
        ->assertSessionHasErrors("min_players");
});

test("store game rejects max_players above 100", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/games", [
            "name" => "Catan",
            "min_players" => 2,
            "max_players" => 101,
        ])
        ->assertSessionHasErrors("max_players");
});
