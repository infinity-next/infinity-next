<?php namespace App\Services;

use App;
use App\Contracts\PermissionUser;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;

use Config;
use Debugbar;

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
		$this->user      = App::make(PermissionUser::class);
	}

	/**
	 * Returns our user object.
	 *
	 * @return \App\Contracts\PermissionUser
	 */
	public function user()
	{
		return $this->user;
	}

};
