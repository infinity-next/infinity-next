<?php

namespace App\Http\Middleware;

use Config;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession as BaseStartSession;
use Illuminate\Contracts\Session\Session;
use Symfony\Component\HttpFoundation\Response;

class StartSession extends BaseStartSession
{
    protected $shouldPassThrough = false;

    /**
     * Patterns matching URIs that do not require persistent sessions.
     *
     * @var array
     */
    protected $except = [
        '*/file/*',
    ];

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return $this->shouldPassThrough = true;
            }
        }

        return $this->shouldPassThrough = false;
    }

    /**
     * Add the session cookie to the application response.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param \Illuminate\Contracts\Session\Session       $session
     */
    protected function addCookieToResponse(Response $response, Session $session)
    {
        if (!$this->shouldPassThrough) {
            parent::addCookieToResponse($response, $session);
        }
    }
}
