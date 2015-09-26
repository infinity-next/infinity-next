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

class StaffController extends PanelController {
	
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
	 * List all staff members to the user.
	 *
	 * @return Response
	 */
	public function getIndex(Board $board, $user = null)
	{
		if (!is_null($user))
		{
			return abort(404);
		}
		
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$roles = $board->roles;
		$staff = $board->getStaff();
		
		return $this->view(static::VIEW_LIST, [
			'board'  => $board,
			'roles'  => $roles,
			'staff'  => $staff,
			
			'tab'    => "staff",
		]);
	}
	
	/**
	 * Opens staff creation form.
	 *
	 * @return Response
	 */
	public function getAdd(Board $board, $user = null)
	{
		if (!is_null($user))
		{
			return abort(404);
		}
		
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$roles = $board->roles;
		$staff = $board->getStaff();
		
		return $this->view(static::VIEW_ADD, [
			'board'  => $board,
			'roles'  => $roles,
			'staff'  => $staff,
			
			'tab'    => "staff",
		]);
	}
	
	/**
	 * Adds new staff.
	 *
	 * @return Response
	 */
	public function putAdd(Board $board, $user = null)
	{
		if (!is_null($user))
		{
			return abort(404);
		}
		
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$createUser = false;
		$rules      = [];
		$existing   = Input::get('staff-source') == "existing";
		
		if ($existing)
		{
			$rules  = [
				'existinguser' => [
					"required",
					"string",
					"exists:users,username",
				],
				'captcha'     => [
					"required",
					"captcha"
				]
			];
			
			$input      = Input::only('existinguser', 'captcha');
			$validator  = Validator::make($input, $rules);
		}
		else
		{
			$createUser = true;
			$validator  = $this->registrationValidator();
		}
		
		if ($validator->fails())
		{
			return redirect()
				->back()
				->withInput()
				->withErrors($validator->errors());
		}
		else if ($createUser)
		{
			$user = $this->registrar->create(Input::all());
		}
		else
		{
			$user = User::whereUsername(Input::only('existinguser'))->firstOrFail();
		}
		
		$role = Role::firstOrCreate([
			'role'       => "janitor",
			'board_uri'  => $board->board_uri,
			'caste'      => NULL,
			'inherit_id' => Role::ID_JANITOR,
			'name'       => "board.role.janitor",
			'capcode'    => "board.role.janitor",
			'system'     => false,
		]);
		
		$userRole = new UserRole;
		$userRole->user_id = $user->user_id;
		$userRole->role_id = $role->role_id;
		$userRole->save();
		
		return redirect("/cp/board/{$board->board_uri}/staff");
	}
	
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