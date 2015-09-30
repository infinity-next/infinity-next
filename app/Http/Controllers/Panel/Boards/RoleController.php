<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Permission;
use App\PermissionGroup;
use App\Role;
use App\RolePermission;
use App\Http\Controllers\Panel\PanelController;

use Input;

use Event;
use App\Events\RoleWasModified;

class RoleController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Roles Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles an index request for all roles in the system.
	|
	*/
	
	const VIEW_PERMISSIONS = "panel.roles.permissions.edit";
	
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
	 * @param  \App\Board  The board we're working with.
	 * @param  \App\Role  The role being modified.
	 * @return Response
	 */
	public function getPermissions(Board $board, Role $role)
	{
		if (!$role->canSetPermissions($this->user))
		{
			return abort(403);
		}
		
		$permission_groups = PermissionGroup::orderBy('display_order', 'asc')->withPermissions()->get();
		
		return $this->view(static::VIEW_PERMISSIONS, [
			'board'  => $board,
			'role'   => $role,
			'groups' => $permission_groups,
			
			'tab'    => "roles",
		]);
	}
	
	/**
	 * Commit updates to the role permissions.
	 *
	 * @param  \App\Board  The board we're working with.
	 * @param  \App\Role  The role being modified.
	 * @return Response
	 */
	public function patchPermissions(Board $board, Role $role)
	{
		if (!$role->canSetPermissions($this->user))
		{
			return abort(403);
		}
		
		$input           = Input::all();
		$permissions     = Permission::all();
		$rolePermissions = [];
		$nullPermissions = [];
		
		foreach ($permissions as $permission)
		{
			if ($this->user->can($permission->permission_id))
			{
				$nullPermissions[] = $permission->permission_id;
				
				foreach ($input['permission'] as $permission_id => $permission_value)
				{
					$permission_id = str_replace("_", ".", $permission_id);
					
					if ($permission->permission_id == $permission_id)
					{
						switch ($permission_value)
						{
							case "allow" :
							case "revoke"  :
							case "deny"  :
								$rolePermissions[$permission_id] = [
									'role_id'       => $role->role_id,
									'permission_id' => $permission_id,
									'value'         => $permission_value == "allow",
								];
							break;
						}
						
						break;
					}
				}
			}
		}
		
		$role->permissions()->detach($nullPermissions);
		$role->permissions()->attach($rolePermissions);
		
		$permission_groups = PermissionGroup::withPermissions()->get();
		
		Event::fire(new RoleWasModified($role));
		
		return $this->view(static::VIEW_PERMISSIONS, [
			'board'  => $board,
			'role'   => $role,
			'groups' => $permission_groups,
			
			'tab'    => "roles",
		]);
	}
}