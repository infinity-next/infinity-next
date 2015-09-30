<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Role;
use App\Http\Controllers\Panel\PanelController;

class RolesController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Roles Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles an index request for all roles in the system.
	|
	*/
	
	const VIEW_ROLES = "panel.board..roles";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.board";
	
	/**
	 * View path for the tertiary (inner) navigation.
	 *
	 * @var string
	 */
	public static $navTertiary = "nav.panel.board.settings";
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function getIndex(Board $board)
	{
		if (!$this->user->canEditConfig($board))
		{
			return abort(403);
		}
		
		$roles = Role::whereBoardRole($board, $this->user)->get();
		
		return $this->view(static::VIEW_ROLES, [
			'board'   => $board,
			'roles'   => $roles,
			'tab'     => "roles",
		]);
	}
}
