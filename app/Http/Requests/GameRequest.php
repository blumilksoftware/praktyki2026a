<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => ["required", "string", "max:255"],
            "min_players" => ["required", "integer", "min:1"],
            "max_players" => ["required", "integer", "min:1", "max:100", "gte:min_players"],
            "description" => ["nullable", "string"],
            "year" => ["nullable", "integer", "min:1800", "max:" . date("Y")],
            "copies" => ["required", "integer", "min:1", "max:100"],
        ];
    }
}
