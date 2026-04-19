<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecrementGameCopiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $game = $this->route("game");

        return [
            "amount" => ["required", "integer", "min:1", "max:{$game->copies}"],
        ];
    }
}
