<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("games", function (Blueprint $table): void {
            $table->id();
            $table->string("name");
            $table->foreignId("user_id")->nullable()->constrained()->onDelete("cascade");
            $table->boolean("is_shared")->default(false);
            $table->integer("min_players")->default(1);
            $table->integer("max_players")->default(4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("games");
    }
};
