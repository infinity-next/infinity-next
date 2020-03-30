<?php

namespace App\Http\Middleware;

use App\Auth\AnonymousUser;
use Closure;

/**
 * Extension of the Authenticate middleware to accept anonymous users.
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
        $user = auth()->user();

        if (is_null($user)) {
            $user = new AnonymousUser;
            auth()->guard()->setUser($user);
        }

        view()->share([
            'user' => $user,
        ]);

        return $next($request);
    }
}
