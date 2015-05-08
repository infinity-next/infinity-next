<?php namespace App\Http\Controllers;

use App\Http\Controllers\MainController;
use App\Http\Controllers\Board\BoardStats;
use Illuminate\Support\Facades\View;

class WelcomeController extends MainController {
	
	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/
	
	use BoardStats;
	
	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return View::make('welcome', [
			'stats' => $this->boardStats(),
		]);
	}
	
}
