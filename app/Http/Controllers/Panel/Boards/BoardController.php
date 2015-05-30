<?php namespace App\Http\Controllers\Panel\Board;

use App\Http\Controllers\Panel\PanelController;

class BoardController extends PanelController {
	
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
	
	const VIEW_DASHBOARD = "panel.board.dashboard";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.board";
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return $this->view( static::VIEW_DASHBOARD );
	}
	
}
