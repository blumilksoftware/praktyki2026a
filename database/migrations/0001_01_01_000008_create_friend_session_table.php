<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("friend_session", function (Blueprint $table): void {
            $table->id();
            $table->foreignId("friend_id")->constrained()->onDelete("cascade");
            $table->foreignId("session_id")->constrained("sessions_game")->onDelete("cascade");
            $table->timestamps();
            $table->unique(["friend_id", "session_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("friend_session");
    }
};
