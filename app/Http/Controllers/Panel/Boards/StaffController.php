<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;

use App\Http\Controllers\Panel\PanelController;

use Event;

class StaffController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Staff Controller
	|--------------------------------------------------------------------------
	|
	| This is the staff controller, available only to board owners and admins.
	| Its only job is to list staff and allow the addition of new staff.
	|
	*/
	
	const VIEW_STAFF = "panel.board.staff";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.board";
	
	/**
	 * List all staff members to the user.
	 *
	 * @return Response
	 */
	public function getIndex(Board $board)
	{
		$roles = $board->roles;
		$staff = $board->getStaff();
		
		return $this->view(static::VIEW_STAFF, [
			'board'  => $board,
			'roles'  => $roles,
			'staff'  => $staff,
		]);
	}
	
}