<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArrangeSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "coverage_weight" => ["sometimes", "numeric", "between:0,1"],
            "allow_unknown_preference" => ["sometimes", "boolean"],
        ];
    }
}
