<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("sessions_game", function (Blueprint $table): void {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("name");
            $table->date("date");
            $table->text("notes")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("sessions_game");
    }
};
