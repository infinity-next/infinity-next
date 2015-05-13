<?php namespace App\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mews\Captcha
 */
class Captcha extends Facade {
	
	protected static function getFacadeAccessor() { return 'captcha'; }
	
}
