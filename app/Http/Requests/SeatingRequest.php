<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "friend_ids" => ["required", "array", "min:1"],
            "friend_ids.*" => ["integer", "exists:friends,id"],
            "game_ids" => ["required", "array", "min:1"],
            "game_ids.*" => ["integer", "exists:games,id"],
        ];
    }
}
