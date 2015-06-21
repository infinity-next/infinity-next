<?php namespace App\Http\Controllers\Panel\Roles;

use App\Permission;
use App\Role;
use App\Http\Controllers\Panel\PanelController;

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
		
		return $this->view(static::VIEW_PERMISSIONS, [
			'role'        => $role,
			'permissions' => Permission::all(),
		]);
	}
}
