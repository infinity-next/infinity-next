<?php

namespace App\Http\Middleware;

use App\Exceptions\TorClearnet;
use App\Support\Geolocation;
use Auth;
use Closure;

class TorFilter
{
    public function handle($request, Closure $next)
    {
        $accountable = true;
        $geolocation = new Geolocation;

        if ($request->header('X-TOR', false) || is_hidden_service()) {
            // Consider a user unaccountable if there's a custom X-TOR header,
            // or if the hostname is our hidden service name.
            $accountable = false;
        }
        elseif ($geolocation->getCountryCode() == 'tor' || true) {
            $accountable = false;

            if (!config('app.debug', false) && env('APP_URL_HS', false)) {
                throw new TorClearnet;
            }
            dd(111);
        }

        user()->setAccountable($accountable);

        return $next($request);
    }
}
