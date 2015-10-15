<?php namespace App\Http\Controllers;

use App\Http\Controllers\Board\BoardStats;

class BoardlistController extends Controller {
	
	use BoardStats;
	
	/*
	|--------------------------------------------------------------------------
	| Boardlist Controller
	|--------------------------------------------------------------------------
	|
	|
	|
	*/
	
	/**
	 * View file for the main index page container.
	 *
	 * @var string
	 */
	const VIEW_INDEX = "boardlist";
	
	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return $this->view(static::VIEW_INDEX, [
			'stats' => $this->boardStats(),
		]);
	}
	
}
