<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;

trait BuildsPaginationMeta
{
    protected function paginationMeta(LengthAwarePaginator $paginator, array $extra = []): array
    {
        return [
            "current_page" => $paginator->currentPage(),
            "last_page" => $paginator->lastPage(),
            "per_page" => $paginator->perPage(),
            "total" => $paginator->total(),
            "from" => $paginator->firstItem(),
            "to" => $paginator->lastItem(),
            ...$extra,
        ];
    }
}
