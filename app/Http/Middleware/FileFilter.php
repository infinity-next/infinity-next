<?php namespace App\Http\Middleware;

use Closure;

class FileFilter
{
	public function handle($request, Closure $next)
	{
		$request->route()->forgetParameter('board');
		
		return $next($request);
	}
}