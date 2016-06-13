<?php

namespace App\Http\Middleware;

use App\Exceptions\TorClearnet;
use App\Services\UserManager;
use App\Support\Geolocation;
use Auth;
use Closure;

class TorFilter
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param Guard $auth
     */
    public function __construct(UserManager $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next)
    {
        $accountable = true;
        $geolocation = new Geolocation;

        if ($request->header('X-TOR', false) || is_hidden_service()) {
            // Consider a user unaccountable if there's a custom X-TOR header,
            // or if the hostname is our hidden service name.
            $accountable = false;
        } elseif ($geolocation->getCountryCode() == 'tor') {
            $accountable = false;

            if (!config('app.debug', false) && env('APP_URL_HS', false)) {
                throw new TorClearnet;
            }
        }

        $this->auth->user()->setAccountable($accountable);

        return $next($request);
    }
}
