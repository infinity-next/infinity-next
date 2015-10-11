<?php namespace App\Http\Controllers\Panel\Roles;

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
	
	const VIEW_ROLES = "panel.roles.dashboard";
	
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
	public function getPermissions()
	{
		if (!$this->user->canAdminRoles() || !$this->user->canAdminPermissions())
		{
			return abort(403);
		}
		
		$roles = Role::where('system', true)->orderBy('weight', 'desc')->get();
		
		return $this->view(static::VIEW_ROLES, [
			'roles' => $roles,
		]);
	}
}
