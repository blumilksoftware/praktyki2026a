<?php

declare(strict_types=1);

use App\Models\Friend;
use App\Models\User;

test("user can delete their own friend", function (): void {
    $user = User::factory()->create();
    $friend = Friend::factory()->create(["user_id" => $user->id]);

    $this->actingAs($user)
        ->delete("/friends/{$friend->id}")
        ->assertRedirect("/friends");

    $this->assertDatabaseMissing("friends", ["id" => $friend->id]);
});

test("delete friend requires auth", function (): void {
    $friend = Friend::factory()->create();

    $this->delete("/friends/{$friend->id}")
        ->assertRedirect("/login");
});

test("user cannot delete another user's friend", function (): void {
    $user = User::factory()->create();
    $otherFriend = Friend::factory()->create();

    $this->actingAs($user)
        ->delete("/friends/{$otherFriend->id}")
        ->assertForbidden();

    $this->assertDatabaseHas("friends", ["id" => $otherFriend->id]);
});
