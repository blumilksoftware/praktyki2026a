<?php

declare(strict_types=1);

use App\Models\Friend;
use App\Models\User;

test("friends index page is displayed", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get("/friends")->assertOk();
});

test("index shows only user's own friends", function (): void {
    $user = User::factory()->create();
    Friend::factory()->count(2)->create(["user_id" => $user->id]);
    Friend::factory()->create();

    $this->actingAs($user)
        ->get("/friends")
        ->assertOk()
        ->assertInertia(fn($page) => $page
            ->component("Friends/Index")
            ->has("friends", 2));
});

test("friends index page requires auth", function (): void {
    $this->get("/friends")->assertRedirect("/login");
});
