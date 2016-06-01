<?php

namespace App\Http\Controllers\Panel;

use App\User;
use App\Http\Controllers\Panel\PanelController;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Cookie;
use Input;
use Session;
use Validator;

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

    use AuthenticatesAndRegistersUsers,
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
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function boot()
    {
        $this->middleware(
            $this->guestMiddleware(),
            ['except' => 'getLogout']
        );
    }

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
    public function putRegister(Request $request)
    {
        if (!$this->user->canCreateUser()) {
            abort(403);
        }

        $validator = $this->registrationValidator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                $request,
                $validator
            );
        }

        $this->auth->login($this->create($request->all()));

        return redirect($this->redirectPath());
    }

    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     *
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

        if (env('APP_NO_AUTH', false)) {
            $user = User::where([
                'username' => $request->get('username'),
            ])->firstOrFail();

            $this->auth->login($user);
        } elseif (!$this->auth->attempt($credentials, $request->has('remember'))) {
            // Re-attempt with the supplied username as an email address.
            $credentials['email'] = $credentials['username'];
            unset($credentials['username']);

            if (!$this->auth->attempt($credentials, $request->has('remember'))) {
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
