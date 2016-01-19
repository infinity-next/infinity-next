<?php

use Illuminate\Contracts\Routing\UrlGenerator;

if (! function_exists('esi_url')) {
	/**
	 * Generate a url for Edge-Side Includes.
	 *
	 * @param  string  $path
	 * @param  mixed   $parameters
	 * @return Illuminate\Contracts\Routing\UrlGenerator|string
	 */
	function esi_url($path = null, $parameters = [])
	{
		$gen = app(UrlGenerator::class);
		
		if (is_null($path)) {
			return $gen;
		}
		
		return $gen->to($path, $parameters, false).'?'.$gen->getRequest()->getScheme();
	}
}
