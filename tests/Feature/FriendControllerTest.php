<?php

declare(strict_types=1);

use App\Models\Friend;
use App\Models\User;

test("friends index page requires auth", function (): void {
    $this->get("/friends")->assertRedirect("/login");
});

test("friends index page is displayed", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get("/friends")->assertOk();
});

test("friends create page is displayed", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get("/friends/create")->assertOk();
});

test("user can store a friend", function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post("/friends", [
        "first_name" => "Jan",
        "last_name" => "Kowalski",
        "email" => "jan@example.com",
    ]);

    $response->assertRedirect("/friends");
    $this->assertDatabaseHas("friends", ["first_name" => "Jan", "last_name" => "Kowalski", "user_id" => $user->id]);
});

test("store friend email is optional", function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post("/friends", [
        "first_name" => "Jan",
        "last_name" => "Kowalski",
    ]);

    $response->assertRedirect("/friends");
    $this->assertDatabaseHas("friends", ["first_name" => "Jan", "user_id" => $user->id]);
});

test("store friend requires first name and last name", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->post("/friends", [])->assertSessionHasErrors(["first_name", "last_name"]);
});

test("user can edit their own friend", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)->get("/friends/{$friend->id}/edit")->assertOk();
});

test("user cannot edit another user's friend", function (): void {
    $user = User::factory()->create();
    $otherFriend = Friend::factory()->create();

    $this->actingAs($user)->get("/friends/{$otherFriend->id}/edit")->assertForbidden();
});

test("user can update their own friend", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);

    $response = $this->actingAs($user)->put("/friends/{$friend->id}", [
        "first_name" => "Updated",
        "last_name" => "Name",
    ]);

    $response->assertRedirect("/friends");
    $this->assertDatabaseHas("friends", ["id" => $friend->id, "first_name" => "Updated"]);
});

test("user can delete their own friend", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)->delete("/friends/{$friend->id}")->assertRedirect("/friends");
    $this->assertDatabaseMissing("friends", ["id" => $friend->id]);
});

test("user cannot delete another user's friend", function (): void {
    $user = User::factory()->create();
    $otherFriend = Friend::factory()->create();

    $this->actingAs($user)->delete("/friends/{$otherFriend->id}")->assertForbidden();
});

test("index shows only user's own friends", function (): void {
    $user = User::factory()->create();
    Friend::factory()->count(2)->create(["user_id" => $user->id]);
    Friend::factory()->create(); // inny user

    $response = $this->actingAs($user)->get("/friends");

    $response->assertOk();
    $response->assertInertia(fn($page) => $page
        ->component("Friends/Index")
        ->has("friends", 2));
});
