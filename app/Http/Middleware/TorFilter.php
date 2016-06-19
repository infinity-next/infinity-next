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
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(UserManager $auth)
    {
        $this->auth = $auth;
    }


    public function handle($request, Closure $next)
    {
        $accountable = true;

        if ($request->header('X-TOR', false) || is_hidden_service())
        {
            // Consider a user unaccountable if there's a custom X-TOR header,
            // or if the hostname is our hidden service name.
            $accountable = false;
        }
        elseif (!env('APP_DEBUG', false) && env('APP_URL_HS', false) && (new Geolocation)->getCountryCode() == "tor")
        {
            throw new TorClearnet;
        }

        $this->auth->user()->setAccountable($accountable);
        return $next($request);
    }
}
