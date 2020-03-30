<?php

namespace App\Http\Middleware;

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
class Authenticate extends \Illuminate\Auth\Middleware\Authenticate
{
    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        $user = null;
        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {

                if ($this->auth->guard($guard)->user()->isAnonymous()) {
                    continue;
                }

                $user = $this->auth->guard($guard);

                return $this->auth->shouldUse($guard);
            }
        }

        view()->share([
            'user' => $user,
        ]);

        $this->unauthenticated($request, $guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('auth.login');
        }
    }
}
