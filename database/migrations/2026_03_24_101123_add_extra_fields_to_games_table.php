<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("games", function (Blueprint $table): void {
            $table->text("description")->nullable()->after("max_players");
            $table->integer("year")->nullable()->after("description");
            $table->integer("copies")->default(1)->after("year");
        });
    }

    public function down(): void
    {
        Schema::table("games", function (Blueprint $table): void {
            $table->dropColumn(["description", "year", "copies"]);
        });
    }
};
