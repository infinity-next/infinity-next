<?php namespace App\Http\Middleware;

use App;
use Closure;
use Lang;

class LocalizedSubdomains
{
	public function handle($request, Closure $next)
	{
		$urlArray = explode('.', parse_url($request->url(), PHP_URL_HOST));
		$subdomain = $urlArray[0];
		
		// Does it have a "hello" message?
		if (Lang::has("panel.authed_as", $subdomain)) {
			App::setLocale($subdomain);
		}
		
		return $next($request);
	}
}