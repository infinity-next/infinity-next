<?php namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Panel\PanelController;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\Registrar;
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
	
	const VIEW_CHANGE = "panel.password.change";
	const VIEW_FORGOT = "panel.password.forgot";
	const VIEW_RESET  = "panel.password.reset";
	
	
	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;
	
	/**
	 * The password broker implementation.
	 *
	 * @var PasswordBroker
	 */
	protected $passwords;
	
	/**
	 * The email subject for password resets..
	 *
	 * @var PasswordBroker
	 */
	protected $subject = "email.password.subject";
	
	
	
	/**
	 * Create a new password controller instance.
	 *
	 * @param  \App\Services\UserManager  $auth
	 * @param  \Illuminate\Contracts\Auth\PasswordBroker  $passwords
	 * @return void
	 */
	public function __construct(\App\Services\UserManager $manager, PasswordBroker $passwords)
	{
		$this->passwords = $passwords;
		
		/*
		$this->middleware('auth', [
				'except' => ['getEmail', 'postEmail'],
			]);
		)
		*/
		
		return parent::__construct($manager);
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
			
			return $this->view(static::VIEW_CHANGE)
				->withStatus(trans('custom.success.password_new'));
		}
		
		return $this->view(static::VIEW_CHANGE)
			->withErrors(['username' => trans('custom.validate.password_old')]);
	}
	
	
	/**
	 * Display the password reset view for the given token.
	 *
	 * @param  string  $token
	 * @return Response
	 */
	public function getReset($token = null)
	{
		if (is_null($token))
		{
			return abort(404);
		}
		
		return $this->view(static::VIEW_RESET)->with('token', $token);
	}
	
	/**
	 * Reset the given user's password.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function postReset(Request $request)
	{
		$this->validate($request, [
			'token' => 'required',
			'email' => 'required|email',
			'password' => 'required|confirmed',
		]);
		
		$credentials = $request->only(
			'email',
			'password',
			'password_confirmation',
			'token'
		);
		
		$response = $this->passwords->reset($credentials, function($user, $password)
		{
			$user->password = bcrypt($password);
			
			$user->save();
			
			$this->auth->login($user);
		});
		
		switch ($response)
		{
			case PasswordBroker::PASSWORD_RESET:
				return redirect("/cp/home");
			
			default:
				return redirect()->back()
					->withInput($request->only('email'))
					->withErrors(['email' => trans($response)]);
		}
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
		
		$response = $this->passwords->sendResetLink($request->only('email'), function($m)
		{
			$m->subject(trans($this->subject, [
				'site' => env('SITE_NAME'),
			]));
		});
		
		switch ($response)
		{
			case PasswordBroker::RESET_LINK_SENT:
				return redirect()->back()->with('status', trans($response));
			
			case PasswordBroker::INVALID_USER:
				return redirect()->back()->withErrors(['email' => trans($response)]);
		}
	}
	
}
