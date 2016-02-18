<?php namespace App\Http\Controllers\Panel;

use App\User;
use App\Http\Controllers\Panel\PanelController;
use Cookie;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Http\Request;
use Session;

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
	
	/**
	 * Asserts middleware.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->middleware('guest', [
			'except' => 'getLogout'
		]);
	}
	
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
		if (!$this->user->canCreateUser())
		{
			abort(403);
		}
		
		return $this->view(static::VIEW_REGISTER);
	}
	
	/**
	 * Handle a registration request for the application.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function putRegister(Request $request)
	{
		if (!$this->user->canCreateUser())
		{
			abort(403);
		}
		
		$validator = $this->registrationValidator();
		
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
		return $this->view(static::VIEW_LOGIN);
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
		
		if (env('APP_NO_AUTH', false))
		{
			$user = User::where(['username' => $request->get('username')])->firstOrFail();
			$this->auth->login($user);
		}
		else if (!$this->auth->attempt($credentials, $request->has('remember')))
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
		return trans('auth.mismatch');
	}
	
	/**
	 * Log the user out of the application.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getLogout()
	{
		$this->auth->logout();

		Session::flush();

		Cookie::queue(Cookie::forget('laravel_session'));
		Cookie::queue(Cookie::forget('XSRF-TOKEN'));
		
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
