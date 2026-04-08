<?php

declare(strict_types=1);

use App\Http\Controllers\FriendController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionController;
use App\Models\Friend;
use App\Models\Game;
use App\Models\Session;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get("/", fn() => Inertia::render("Welcome", [
    "canLogin" => Route::has("login"),
    "canRegister" => Route::has("register"),
]));

Route::get("/dashboard", function () {
    $userId = auth()->id();

    return Inertia::render("Dashboard", [
        "gamesCount" => Game::where("user_id", $userId)->orWhere("is_shared", true)->count(),
        "friendsCount" => Friend::where("user_id", $userId)->count(),
        "sessionsCount" => Session::where("user_id", $userId)->count(),
    ]);
})->middleware(["auth", "verified"])->name("dashboard");

Route::resource("/games", GameController::class)
    ->middleware(["auth", "verified"]);

Route::resource("/friends", FriendController::class)->middleware(["auth", "verified"])->except(["show"]);

Route::resource("/sessions", SessionController::class)->middleware(["auth", "verified"]);

Route::post("/sessions/{session}/arrange", [SessionController::class, "arrange"])
    ->middleware(["auth", "verified"])
    ->name("sessions.arrange");

Route::get("/friends/{friend}/preferences", [PreferenceController::class, "show"])->middleware(["auth", "verified"])->name("preferences.show");

Route::put("/friends/{friend}/preferences", [PreferenceController::class, "update"])->middleware(["auth", "verified"])->name("preferences.update");

Route::middleware("auth")->group(function (): void {
    Route::get("/profile", [ProfileController::class, "edit"])->name("profile.edit");
    Route::patch("/profile", [ProfileController::class, "update"])->name("profile.update");
    Route::delete("/profile", [ProfileController::class, "destroy"])->name("profile.destroy");
});

Route::post("/locale/{locale}", function (string $locale) {
    if (in_array($locale, ["pl", "en"], true)) {
        session()->put("locale", $locale);
    }

    return back();
})->name("locale.switch");

require __DIR__ . "/auth.php";
