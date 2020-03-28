<?php

namespace App\Http\Controllers\Panel;

use App\User;
use App\Http\Controllers\Panel\PanelController;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\RegistersUsers;
use Auth;
use Cookie;
use Request;
use Session;
use Validator;

echo "AuthController.php depreicated";
exit;

class AuthController extends PanelController
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesUsers,
        ThrottlesLogins;

    const VIEW_LOGIN = 'panel.auth.login';
    const VIEW_REGISTER = 'panel.auth.register';

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
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
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegister()
    {
        if (!$this->user->canCreateUser()) {
            abort(403);
        }

        return $this->view(static::VIEW_REGISTER);
    }

    /**
     * Handle a registration request for the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function putRegister()
    {
        if (!user()->canCreateUser()) {
            abort(403);
        }

        $validator = $this->registrationValidator(Request::all());

        if ($validator->fails()) {
            $this->throwValidationException(
                $request,
                $validator
            );
        }

        Auth::login($this->create(Request::all()));

        return redirect($this->redirectPath());
    }

    /**
     * Handle a login request to the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function postLogin()
    {
        $validatedData = Request::validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Attempt a login with supplied credentials.
        $credentials = Request::only('username', 'password');

        if (env('APP_NO_AUTH', false)) {
            $user = User::where([
                'username' => Request::input('username'),
            ])->firstOrFail();

            $this->auth->login($user);
        }
        elseif (!auth()->attempt($credentials, Request::has('remember'))) {
            // Re-attempt with the supplied username as an email address.
            $credentials['email'] = $credentials['username'];
            unset($credentials['username']);

            if (!auth()->attempt($credentials, Request::has('remember'))) {
                return redirect($this->loginPath())
                    ->withInput(Request::only('username', 'remember'))
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
        Auth::logout();

        Session::flush();

        Cookie::queue(Cookie::forget(config('session.cookie')));
        Cookie::queue(Cookie::forget('XSRF-TOKEN'));

        return redirect($this->redirectPath(), 302);
    }

    /**
     * Get the path to the login route.
     *
     * @return string
     */
    public function loginPath()
    {
        return route('panel.login');
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        return route('panel.home');
    }
}
