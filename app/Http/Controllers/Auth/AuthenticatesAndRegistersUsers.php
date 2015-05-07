<?php namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;

trait AuthenticatesAndRegistersUsers {
	
	/**
	 * The Guard implementation.
	 *
	 * @var \Illuminate\Contracts\Auth\Guard
	 */
	protected $auth;
	
	/**
	 * The registrar implementation.
	 *
	 * @var \Illuminate\Contracts\Auth\Registrar
	 */
	protected $registrar;
	
	/**
	 * Show the application registration form.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getRegister()
	{
		return view('auth.register');
	}
	
	/**
	 * Handle a registration request for the application.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function postRegister(Request $request)
	{
		$validator = $this->registrar->validator($request->all());
		
		if ($validator->fails())
		{
			$this->throwValidationException(
				$request,
				$validator
			);
		}
		
		$this->auth->login($this->registrar->create($request->all()));
		
		return redirect($this->redirectPath());
	}
	
	/**
	 * Show the application login form.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getLogin()
	{
		return view('auth.login');
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
		
		// Attempt a login with supplied credentials.
		$credentials = $request->only('username', 'password');
		
		if (!$this->auth->attempt($credentials, $request->has('remember')))
		{
			// Re-attempt with the supplied username as an email address.
			$credentials['email'] = $credentials['username'];
			unset($credentials['username']);
			
			if (!$this->auth->attempt($credentials, $request->has('remember')))
			{
				return redirect($this->loginPath())
							->withInput($request->only('username', 'remember'))
							->withErrors([
									 $this->getFailedLoginMessage(),
							]);
			}
		}
		
		return redirect()->intended($this->redirectPath());
	}
	
	/**
	 * Get the failed login message.
	 *
	 * @return string
	 */
	protected function getFailedLoginMessage()
	{
		return 'These credentials do not match our records.';
	}
	
	/**
	 * Log the user out of the application.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getLogout()
	{
		$this->auth->logout();
		
		return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
	}
	
	/**
	 * Get the post register / login redirect path.
	 *
	 * @return string
	 */
	public function redirectPath()
	{
		if (property_exists($this, 'redirectPath'))
		{
			return $this->redirectPath;
		}
		
		return property_exists($this, 'redirectTo') ? $this->redirectTo : '/cp/home';
	}
	
	/**
	 * Get the path to the login route.
	 *
	 * @return string
	 */
	public function loginPath()
	{
		return property_exists($this, 'loginPath') ? $this->loginPath : '/cp/auth/login';
	}
	
}
