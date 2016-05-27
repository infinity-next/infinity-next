<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Lang;

class LocalizedSubdomains
{
    public function handle($request, Closure $next)
    {
        $urlArray = explode('.', parse_url($request->url(), PHP_URL_HOST));
        $subdomain = $urlArray[0];
        $direction = 'ltr';

        // Does it have a text direction set?
        if (Lang::has('l18n.direction', $subdomain)) {
            App::setLocale($subdomain);
            $direction = Lang::trans('l18n.direction') == 'rtl' ? 'rtl' : 'ltr';
        }

        view()->share([
            'direction' => $direction,
            'ltr' => $direction === 'ltr',
            'rtl' => $direction === 'rtl',
        ]);

        return $next($request);
    }
}
