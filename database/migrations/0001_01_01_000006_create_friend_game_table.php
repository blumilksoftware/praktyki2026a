<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("friend_game", function (Blueprint $table): void {
            $table->id();
            $table->foreignId("friend_id")->constrained()->onDelete("cascade");
            $table->foreignId("game_id")->constrained()->onDelete("cascade");
            $table->integer("rating");
            $table->timestamps();
            $table->unique(["friend_id", "game_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("friend_game");
    }
};
