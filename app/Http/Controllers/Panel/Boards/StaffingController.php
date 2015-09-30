<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Role;
use App\UserRole;
use App\User;

use App\Contracts\PermissionUser;
use App\Http\Controllers\Panel\PanelController;

use Input;
use Request;
use Validator;

class StaffingController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Staff Controller
	|--------------------------------------------------------------------------
	|
	| This is the board staff controller, available only to the board owner and admins.
	| Its only job is to list, remove, update, and add staff members to a board.
	|
	*/
	
	const VIEW_LIST  = "panel.board.staff";
	const VIEW_ADD   = "panel.board.staff.create";
	const VIEW_EDIT  = "panel.board.staff.edit";
	
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
	 * Opens staff management form.
	 *
	 * @return Response
	 */
	public function getEdit(Board $board, PermissionUser $user)
	{
		if (!$this->user->canEditBoardStaffMember($user, $board))
		{
			return abort(403);
		}
		
		$roles  = $this->user->getAssignableRolesForBoard($board);
		$staff  = $board->getStaff();
		
		return $this->view(static::VIEW_EDIT, [
			'board'  => $board,
			'roles'  => $roles,
			'staff'  => $user,
			
			'tab'    => "staff",
		]);
	}
	
}