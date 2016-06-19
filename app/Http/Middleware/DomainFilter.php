<?php

namespace App\Http\Middleware;

use Closure;

class DomainFilter
{
    public function handle($request, Closure $next)
    {
        $request->route()->forgetParameter('domain');
        $request->route()->forgetParameter('tld');

        return $next($request);
    }
}
