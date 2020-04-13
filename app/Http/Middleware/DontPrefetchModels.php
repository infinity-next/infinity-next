<?php

namespace App\Http\Middleware;

use App\Auth\AnonymousUser;
use Closure;

/**
 * Informs the container that this request should be made as fast as possible.
 *
 * @category   Middleware
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @see \Illuminate\Auth\Middleware\Authenticate
 *
 * @since      0.6.0
 */
class DontPrefetchModels
{
    public function handle($request, Closure $next)
    {
        $request->route()->setParameter('fast_prefetch', true);

        return $next($request);
    }
}
