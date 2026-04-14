<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeRequestEncoding
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge($this->normalizeArray($request->all()));

        return $next($request);
    }

    private function normalizeArray(array $data): array
    {
        return array_map(function (mixed $value): mixed {
            if (is_string($value)) {
                return mb_convert_encoding($value, "UTF-8", "UTF-8");
            }

            if (is_array($value)) {
                return $this->normalizeArray($value);
            }

            return $value;
        }, $data);
    }
}
