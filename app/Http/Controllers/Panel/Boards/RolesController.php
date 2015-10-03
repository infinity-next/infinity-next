<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Role;
use App\Http\Controllers\Panel\PanelController;

use Input;
use Validator;

class RolesController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Roles Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles an index request for all roles in the system.
	|
	*/
	
	const VIEW_ROLES  = "panel.board.roles";
	const VIEW_CREATE = "panel.board.roles.create";
	
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
	
	
	/**
	 * Show the role creation form.
	 *
	 * @param  \App\Board  $board
	 * @return Response
	 */
	public function getAdd(Board $board)
	{
		if (!$this->user->canEditConfig($board))
		{
			return abort(403);
		}
		
		$roles   = Role::whereCanParentForBoard($board, $this->user)->get();
		$choices = [];
		
		foreach ($roles as $role)
		{
			$choices[$role->getDisplayName()] = $role->role;
		}
		
		return $this->view(static::VIEW_CREATE, [
			'board'   => $board,
			'role'    => null,
			'choices' => $choices,
			'tab'     => "roles",
		]);
	}
	
	/**
	 * Add a new role.
	 *
	 * @param  \App\Board  $board
	 * @return Response
	 */
	public function putAdd(Board $board)
	{
		if (!$this->user->canEditConfig($board))
		{
			return abort(403);
		}
		
		$roles = Role::whereCanParentForBoard($board, $this->user)->get();
		
		$rules = [
			'roleType'    => [
				"required",
				"string",
				"in:" . $roles->lists('role')->implode(","),
			],
			'roleCaste'   => [
				"string",
				"alpha_num",
				"unique:roles,role,{$board->board_uri},board_uri",
			],
			'roleName'    => [
				"string",
			],
			'roleCapcode' => [
				"string",
			],
		];
		
		$validator = Validator::make(Input::all(), $rules);
		
		if ($validator->fails())
		{
			return redirect()
				->back()
				->withInput()
				->withErrors($validator->errors());
		}
		
		$role = new Role();
		$role->board_uri  = $board->board_uri;
		$role->inherit_id = $roles->where('role', strtolower(Input::get('roleType')))->pluck('role_id')[0];
		$role->role       = strtolower(Input::get('roleType'));
		$role->caste      = strtolower(Input::get('roleCaste'));
		$role->name       = Input::get('roleName') ?: "user.role.{$role->role}";
		$role->capcode    = Input::get('capcode');
		$role->weight     = 5 + constant(Role::class . "::WEIGHT_" . strtoupper(Input::get('roleType')))  ;
		$role->save();
		
		return redirect( $role->getPermissionsURLForBoard() );
	}
	
}
