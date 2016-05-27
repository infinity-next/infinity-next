<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Removes Board information from the LOIC's parameter builder.
 *
 * Good for controllers which can operate with or without a board.
 * You can use `app('\App\Board')` to construct the route board instance.
 *
 * @category   Middleware
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class BoardAmbivilance
{
    public function handle($request, Closure $next)
    {
        $request->route()->forgetParameter('board');

        return $next($request);
    }
}
