<?php namespace App\Http\Middleware;

use Config;
use Closure;
use Illuminate\Session\Middleware\StartSession as BaseStartSession;

class StartSession extends BaseStartSession
{
	/**
	 * Pattern to match control panel URIs.
	 *
	 * @var string
	 */
	protected $panel = 'cp/*';

	/**
	 * Patterns matching control panel URIs that do not require persistent sessions.
	 *
	 * @var array
	 */
	protected $except = [
		'cp/adventure',
		'cp/captcha*'
	];

	/**
	 * Determine if a request is for control panel URIs that require persistent sessions.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return bool
	 */
	protected function shouldPassThrough($request)
	{
		if (!$request->is($this->panel))
		{
			return false;
		}

		foreach($this->except as $except)
		{
			if ($request->is($except))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($this->shouldPassThrough($request))
		{
			return parent::handle($request, $next);
		}

		if (!$request->cookie())
		{
			Config::set('session.driver', 'array');
		}

		return parent::handle($request, $next);
	}
}
