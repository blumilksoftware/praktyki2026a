<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = "app";

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $locale = app()->getLocale();
        $path = lang_path("{$locale}.json");
        $translations = file_exists($path) ? json_decode(file_get_contents($path), true) : [];

        return [
            ...parent::share($request),
            "auth" => [
                "user" => $request->user(),
            ],
            "locale" => $locale,
            "translations" => $translations,
        ];
    }
}
