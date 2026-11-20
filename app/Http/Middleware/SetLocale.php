<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);

        // Controllers should not receive the locale as a positional argument.
        $request->route()->forgetParameter('locale');

        return $next($request);
    }
}
