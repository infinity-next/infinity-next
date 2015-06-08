<?php namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Panel\PanelController;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;

class HomeController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/
	
	const VIEW_HOME = "panel.home";
	
	/**
	 * Asserts middleware.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->middleware('auth');
	}
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return $this->view(static::VIEW_HOME);
	}
	
}
