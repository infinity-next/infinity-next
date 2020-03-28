<?php

namespace App\Http\Middleware;

use App\Auth\AnonymousUser;
use Closure;

/**
 * Extension of the Authenticate middleware to reject anonymous users.
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
class Anonymous
{
    public function handle($request, Closure $next)
    {
        if (is_null(auth()->user())) {
            auth()->guard()->setUser(new AnonymousUser);
        }

        return $next($request);
    }
}
