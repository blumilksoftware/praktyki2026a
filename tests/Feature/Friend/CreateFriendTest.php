<?php

declare(strict_types=1);

use App\Models\User;

test("friends create page is displayed", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get("/friends/create")->assertOk();
});

test("user can store a friend with all fields", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/friends", [
            "first_name" => "Jan",
            "last_name" => "Kowalski",
            "email" => "jan@example.com",
        ])
        ->assertRedirect("/friends");

    $this->assertDatabaseHas("friends", [
        "first_name" => "Jan",
        "last_name" => "Kowalski",
        "email" => "jan@example.com",
        "user_id" => $user->id,
    ]);
});

test("store friend email is optional", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/friends", [
            "first_name" => "Jan",
            "last_name" => "Kowalski",
        ])
        ->assertRedirect("/friends");

    $this->assertDatabaseHas("friends", [
        "first_name" => "Jan",
        "last_name" => "Kowalski",
        "user_id" => $user->id,
    ]);
});

test("store friend requires auth", function (): void {
    $this->post("/friends", [
        "first_name" => "Jan",
        "last_name" => "Kowalski",
    ])->assertRedirect("/login");
});

test("store friend requires first name", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/friends", ["last_name" => "Kowalski"])
        ->assertSessionHasErrors("first_name");
});

test("store friend requires last name", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/friends", ["first_name" => "Jan"])
        ->assertSessionHasErrors("last_name");
});

test("store friend rejects invalid email", function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/friends", [
            "first_name" => "Jan",
            "last_name" => "Kowalski",
            "email" => "not-an-email",
        ])
        ->assertSessionHasErrors("email");
});
