<?php namespace App\Http\Middleware;

use App\Services\UserManager;

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

		if ($request->header('X-TOR', false) || $request->server('HTTP_HOST') === env('APP_URL_HS'))
		{
			// Consider a user unaccountable if there's a custom X-TOR header,
			// or if the hostname is our hidden service name.
			$accountable = false;
		}

		$this->auth->user()->setAccountable($accountable);
		return $next($request);
	}
}
