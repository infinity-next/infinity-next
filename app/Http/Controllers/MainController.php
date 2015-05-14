<?php namespace App\Http\Controllers;

use App\Board;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;

abstract class MainController extends Controller {
	
	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth      = $auth;
		$this->registrar = $registrar;
		$this->user      = $this->auth->user();
		
		View::share('boardbar', Board::getBoardListBar());
		View::share('user', $this->user);
	}
}