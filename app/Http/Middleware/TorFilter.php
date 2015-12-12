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
		$accountable = !$request->header('X-TOR', false);
		$this->auth->user()->setAccountable($accountable);
		return $next($request);
	}
}
