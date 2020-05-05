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
        $hiddenService = is_hidden_service();
        $hiddenServiceExists = config('app.url_hs', false);

        if ($request->header('X-TOR', false) || $hiddenService) {
            // Consider a user unaccountable if there's a custom X-TOR header,
            // or if the hostname is our hidden service name.
            $accountable = false;
        }
        elseif ($geolocation->getCountryCode() == 'tor') {
            $accountable = false;

            if (!config('app.debug', false) && $hiddenServiceExists) {
                throw new TorClearnet;
            }
        }

        user()->setAccountable($accountable);

        if (!$accountable || !$hiddenServiceExists) {
            return $next($request);
        }
        else {
            // for Tor Browser as of 9.5a11
            return $next($request)->header('Onion-Location', 'http://' . config('app.url_hs') . '/' . ltrim($request->path(), '/'));
        }
    }
}
