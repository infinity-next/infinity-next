<?php

namespace App\Http\Middleware;

use App\Contracts\Support\Sluggable as SluggableContract;
use Closure;
use Route;

/**
 * Deals with URL slugs.
 *
 * @category   Middleware
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class SlugRouting
{
    public function handle($request, Closure $next)
    {
        $route = $request->route();
        $route->forgetParameter('slug');

        return $next($request);
    }
}
