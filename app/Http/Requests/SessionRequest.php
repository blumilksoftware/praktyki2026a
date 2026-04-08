<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => ["required", "string", "max:255"],
            "date" => ["required", "date"],
            "notes" => ["nullable", "string", "max:1000"],
            "friend_ids" => ["array"],
            "friend_ids.*" => ["integer", "exists:friends,id"],
            "game_ids" => ["array"],
            "game_ids.*" => ["integer", "exists:games,id"],
        ];
    }
}
