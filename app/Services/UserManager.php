<?php namespace App\Services;

use App\Support\Anonymous;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;

class UserManager {
	
	/**
	 * Create a new authentication controller instance.
	 * Don't overwrite __construct in any children. Use ::boot
	 *
	 * @param  \Illuminate\Auth\Guard  $auth
	 * @param  \Illuminate\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth      = $auth;
		$this->registrar = $registrar;
		
		if ($auth->guest())
		{
			$this->user  = new Anonymous;
		}
		else
		{
			$this->user  = $auth->user();
		}
	}
	
};