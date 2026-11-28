<?php

namespace App\Http\Middleware;

use App\Support\Admin;
use App\Support\Expert;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a CDN answer repeat visits to the public pages so the app server stays asleep. Runs outside the
 * session middleware: a response that carries a cookie is never cached, so anonymous pages are sent without one.
 */
class CachePublicPages
{
    private const PAGES = ['home', 'statues.index', 'statues.show', 'map', 'documentation', 'about'];

    // Fresh for 10 minutes; afterwards the CDN keeps serving the old copy while it refreshes in the background.
    private const CACHE_CONTROL = 'public, max-age=0, s-maxage=600, stale-while-revalidate=86400';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->cacheable($request, $response)) {
            return $response;
        }

        foreach ($response->headers->getCookies() as $cookie) {
            $response->headers->removeCookie($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
        }

        $response->headers->set('Cache-Control', self::CACHE_CONTROL);

        return $response;
    }

    private function cacheable(Request $request, Response $response): bool
    {
        return $request->isMethodCacheable()
            && $response->getStatusCode() === Response::HTTP_OK
            && $request->routeIs(...self::PAGES)
            && ! $request->query->has(Expert::BYPASS_QUERY)
            && ! Expert::active()
            && ! Admin::active();
    }
}
