<?php namespace App\Http\Controllers\Panel\Roles;

use App\Permission;
use App\PermissionGroup;
use App\Role;
use App\RolePermission;
use App\Http\Controllers\Panel\PanelController;
use Input;

class PermissionsController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Config Controller
	|--------------------------------------------------------------------------
	|
	| This is the site config controller, available only to admins.
	| Its only job is to load config panels and to validate and save the changes.
	|
	*/
	
	const VIEW_PERMISSIONS = "panel.roles.permissions.edit";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.users";
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function getIndex(Role $role)
	{
		if (!$this->user->canAdminRoles() || !$this->user->canAdminPermissions())
		{
			return abort(403);
		}
		
		$permission_groups = PermissionGroup::withPermissions()->get();
		
		return $this->view(static::VIEW_PERMISSIONS, [
			'role'   => $role,
			'groups' => $permission_groups,
		]);
	}
	
	public function patchIndex(Role $role)
	{
		if (!$this->user->canAdminRoles() || !$this->user->canAdminPermissions())
		{
			return abort(403);
		}
		
		$input           = Input::all();
		$permissions     = Permission::all();
		$rolePermissions = [];
		
		RolePermission::where(['role_id' => $role->role_id])->delete();
		
		foreach ($permissions as $permission)
		{
			foreach ($input as $permission_id => $permission_value)
			{
				$permission_id = str_replace("_", ".", $permission_id);
				
				if ($permission->permission_id == $permission_id)
				{
					switch ($permission_value)
					{
						case "allow" :
						case "deny"  :
							$rolePermissions[] = [
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
		
		RolePermission::insert($rolePermissions);
		
		return $this->view(static::VIEW_PERMISSIONS, [
			'role'        => $role,
			'permissions' => Permission::all(),
		]);
	}
}
