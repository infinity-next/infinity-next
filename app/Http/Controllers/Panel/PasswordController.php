<?php namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Panel\PanelController;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class PasswordController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Password Reset Controller
	|--------------------------------------------------------------------------
	|
	| This controller is responsible for handling password reset requests
	| and uses a simple trait to include this behavior. You're free to
	| explore this trait and override any methods you wish to tweak.
	|
	*/
	
	use ResetsPasswords;
	
	const VIEW_CHANGE = "panel.password.change";
	const VIEW_FORGOT = "panel.password.forgot";
	
	/**
	 * Create a new password controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\PasswordBroker  $passwords
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar, PasswordBroker $passwords)
	{
		$this->passwords = $passwords;
		$this->middleware('auth', [
				'except' => ['getEmail', 'postEmail'],
			]);
		
		return parent::__construct($auth, $registrar);
	}
	
	/**
	 * Display the form to request a password reset link.
	 *
	 * @return Response
	 */
	public function getEmail()
	{
		return $this->view(static::VIEW_FORGOT);
	}

	/**
	 * Opens the password reset form.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
		return $this->view(static::VIEW_CHANGE);
	}
	
	/**
	 * Opens the password reset form.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function postIndex(Request $request)
	{
		$validator = $this->validate($request, [
			'password'     => "required",
			'password_new' => "required|confirmed|min:4",
		]);
		
		$credentials = $request->only('password', 'password_new');
		$user = $this->auth->user();
		
		if ($this->auth->validate(['username' => $user->username, 'password' => $credentials['password']]))
		{
			$user->password = bcrypt($credentials['password_new']);
			$user->save();
			
			$this->auth->login($user);
			
			return view('auth.password.change')
				->withStatus(trans('custom.success.password_new'));
		}
		
		return view('auth.password.change')
			->withErrors(['username' => trans('custom.validate.password_old')]);
	}
	
	/**
	 * Send a reset link to the given user.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function postEmail(Request $request)
	{
		$this->validate($request, [
				'email'   => 'required|email',
				'captcha' => 'required|captcha',
			]);
		return parent::postEmail($request);
	}
}
