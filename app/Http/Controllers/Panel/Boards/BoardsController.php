<?php namespace App\Http\Controllers\Panel\Boards;

use App\Http\Controllers\Panel\PanelController;

class BoardsController extends PanelController {
	
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
	const VIEW_CREATE    = "panel.board.create";
	
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
		$boards = $this->user->getBoardsWithConfigRights();
		
		return $this->view(static::VIEW_DASHBOARD, [
			'boards' => $boards,
		]);
	}
	
	/**
	 * Allows for the creation of a new board.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
		if (!$this->user->canCreateBoard())
		{
			return abort(403);
		}
		
		return $this->view(static::VIEW_CREATE);
	}
}
