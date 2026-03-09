<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("friends", function (Blueprint $table): void {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("first_name");
            $table->string("last_name");
            $table->string("email")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("friends");
    }
};
