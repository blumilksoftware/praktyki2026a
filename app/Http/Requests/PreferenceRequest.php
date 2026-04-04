<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "ratings" => ["array"],
            "ratings.*.game_id" => ["required", "integer", "exists:games,id"],
            "ratings.*.rating" => ["required", "integer", "min:1", "max:10"],
            "redirect_to" => ["nullable", "string"],
        ];
    }
}
