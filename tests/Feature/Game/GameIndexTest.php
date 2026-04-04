<?php

declare(strict_types=1);

use App\Models\Game;
use App\Models\User;

test("games index page is displayed", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get("/games")->assertOk();
});

test("index shows user's own games and shared games but not other users' games", function (): void {
    $user = User::factory()->create();
    Game::factory()->create(["user_id" => $user->id, "name" => "My Game"]);
    Game::factory()->shared()->create(["name" => "Shared Game"]);
    Game::factory()->userAdded()->create(["name" => "Other User Game"]);

    $this->actingAs($user)
        ->get("/games")
        ->assertOk()
        ->assertInertia(fn($page) => $page
            ->component("Games/Index")
            ->has("games", 2));
});

test("games index page requires auth", function (): void {
    $this->get("/games")->assertRedirect("/login");
});
