<?php

declare(strict_types=1);

use App\Models\Friend;
use App\Models\Game;
use App\Models\User;

test("preferences page is displayed", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->get("/friends/{$friend->id}/preferences")
        ->assertOk();
});

test("user can update friend preferences", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}/preferences", [
            "ratings" => [
                ["game_id" => $game->id, "rating" => 8],
            ],
        ])
        ->assertRedirect("/friends");

    $this->assertDatabaseHas("friend_game", [
        "friend_id" => $friend->id,
        "game_id" => $game->id,
        "rating" => 8,
    ]);
});

test("user can update preferences with minimum valid rating", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}/preferences", [
            "ratings" => [["game_id" => $game->id, "rating" => 1]],
        ])
        ->assertRedirect("/friends");

    $this->assertDatabaseHas("friend_game", [
        "friend_id" => $friend->id,
        "rating" => 1,
    ]);
});

test("user can update preferences with maximum valid rating", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}/preferences", [
            "ratings" => [["game_id" => $game->id, "rating" => 10]],
        ])
        ->assertRedirect("/friends");

    $this->assertDatabaseHas("friend_game", [
        "friend_id" => $friend->id,
        "rating" => 10,
    ]);
});

test("user is redirected to redirect_to after updating preferences", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}/preferences", [
            "ratings" => [["game_id" => $game->id, "rating" => 8]],
            "redirect_to" => "/sessions/5",
        ])
        ->assertRedirect("/sessions/5");
});

test("preferences page requires auth", function (): void {
    $friend = Friend::factory()->create();

    $this->get("/friends/{$friend->id}/preferences")
        ->assertRedirect("/login");
});

test("update preferences requires auth", function (): void {
    $friend = Friend::factory()->create();

    $this->put("/friends/{$friend->id}/preferences", [
        "ratings" => [],
    ])->assertRedirect("/login");
});

test("user cannot view another user's friend preferences", function (): void {
    $user = User::factory()->create();
    $otherFriend = Friend::factory()->create();

    $this->actingAs($user)
        ->get("/friends/{$otherFriend->id}/preferences")
        ->assertForbidden();
});

test("user cannot update another user's friend preferences", function (): void {
    $user = User::factory()->create();
    $otherFriend = Friend::factory()->create();
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$otherFriend->id}/preferences", [
            "ratings" => [["game_id" => $game->id, "rating" => 5]],
        ])
        ->assertForbidden();
});

test("preference rating must be at least 1", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}/preferences", [
            "ratings" => [["game_id" => $game->id, "rating" => 0]],
        ])
        ->assertSessionHasErrors("ratings.0.rating");
});

test("preference rating must be at most 10", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}/preferences", [
            "ratings" => [["game_id" => $game->id, "rating" => 11]],
        ])
        ->assertSessionHasErrors("ratings.0.rating");
});

test("preference rating must not be negative", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);
    $game = Game::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}/preferences", [
            "ratings" => [["game_id" => $game->id, "rating" => -1]],
        ])
        ->assertSessionHasErrors("ratings.0.rating");
});

test("preference game must exist", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}/preferences", [
            "ratings" => [["game_id" => 99999, "rating" => 5]],
        ])
        ->assertSessionHasErrors("ratings.0.game_id");
});
