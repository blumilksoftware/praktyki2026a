<?php

declare(strict_types=1);

use App\Models\Friend;
use App\Models\User;

test("user can edit their own friend", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->get("/friends/{$friend->id}/edit")
        ->assertOk();
});

test("user can update their own friend", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}", [
            "first_name" => "Updated",
            "last_name" => "Name",
            "email" => "updated@example.com",
        ])
        ->assertRedirect("/friends");

    $this->assertDatabaseHas("friends", [
        "id" => $friend->id,
        "first_name" => "Updated",
        "last_name" => "Name",
    ]);
});

test("user can update friend and clear email", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create([
        "user_id" => $user->id,
        "email" => "old@example.com",
    ]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}", [
            "first_name" => $friend->first_name,
            "last_name" => $friend->last_name,
            "email" => "",
        ])
        ->assertRedirect("/friends");

    $this->assertDatabaseHas("friends", [
        "id" => $friend->id,
        "email" => null,
    ]);
});

test("edit friend requires auth", function (): void {
    $friend = Friend::factory()->create();

    $this->get("/friends/{$friend->id}/edit")
        ->assertRedirect("/login");
});

test("update friend requires auth", function (): void {
    $friend = Friend::factory()->create();

    $this->put("/friends/{$friend->id}", [
        "first_name" => "Updated",
        "last_name" => "Name",
    ])->assertRedirect("/login");
});

test("user cannot edit another user's friend", function (): void {
    $user = User::factory()->create();
    $otherFriend = Friend::factory()->create();

    $this->actingAs($user)
        ->get("/friends/{$otherFriend->id}/edit")
        ->assertForbidden();
});

test("user cannot update another user's friend", function (): void {
    $user = User::factory()->create();
    $otherFriend = Friend::factory()->create();

    $this->actingAs($user)
        ->put("/friends/{$otherFriend->id}", [
            "first_name" => "Updated",
            "last_name" => "Name",
        ])
        ->assertForbidden();
});

test("update friend requires first name", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}", ["last_name" => "Name"])
        ->assertSessionHasErrors("first_name");
});

test("update friend requires last name", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}", ["first_name" => "Updated"])
        ->assertSessionHasErrors("last_name");
});

test("update friend rejects invalid email", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->put("/friends/{$friend->id}", [
            "first_name" => "Jan",
            "last_name" => "Kowalski",
            "email" => "not-an-email",
        ])
        ->assertSessionHasErrors("email");
});
