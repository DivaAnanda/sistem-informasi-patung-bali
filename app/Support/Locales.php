<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

final class Locales
{
    private const NAMES = [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
    ];

    /**
     * @return list<string>
     */
    public static function available(): array
    {
        return config('app.available_locales');
    }

    public static function pattern(): string
    {
        return implode('|', self::available());
    }

    public static function name(string $locale): string
    {
        return self::NAMES[$locale] ?? $locale;
    }

    /**
     * The current page in another language, keeping route parameters and the query string.
     */
    public static function currentUrlIn(string $locale): string
    {
        $route = Route::current();

        if ($route?->getName() === null) {
            return url($locale);
        }

        // Route::view() stores its view name and status as route defaults; only URI parameters belong in the URL.
        return route($route->getName(), [
            ...request()->query(),
            ...Arr::only($route->parameters(), $route->parameterNames()),
            'locale' => $locale,
        ]);
    }
}
