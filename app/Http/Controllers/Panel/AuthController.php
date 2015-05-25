<?php namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Panel\PanelController;
use App\Http\Controllers\Panel\AuthenticatesAndRegistersUsers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Http\Request;

class AuthController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users.
	|
	*/
	
	const VIEW_LOGIN    = "panel.auth.login";
	const VIEW_REGISTER = "panel.auth.register";
	
	use AuthenticatesAndRegistersUsers;
	
	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->middleware('guest', [
				'except' => 'getLogout'
			]);
		
		return parent::__construct($auth, $registrar);
	}
}
