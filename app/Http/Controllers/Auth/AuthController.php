<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\CpController;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

class AuthController extends CpController {
	
	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users. By default, this controller uses
	| a simple trait to add these behaviors.
	|
	*/
	
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
	
		/**
	 * Handle a login request to the application.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function postLogin(Request $request)
	{
		$this->validate($request, [
			'username' => 'required',
			'password' => 'required',
		]);
		
		$credentials = $request->only('username', 'password');
		
		if ($this->auth->attempt($credentials, $request->has('remember')))
		{
			return redirect()->intended($this->redirectPath());
		}
		
		return redirect($this->loginPath())
					->withInput($request->only('username', 'remember'))
					->withErrors([
							 $this->getFailedLoginMessage(),
					]);
	}
}
