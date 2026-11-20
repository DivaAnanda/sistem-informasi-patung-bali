<?php

namespace App\Http\Middleware;

use App\Support\Expert;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExpert
{
    /**
     * Validation pages answer 404 to everyone else, so their existence is not advertised.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Expert::active(), 404);

        return $next($request);
    }
}
