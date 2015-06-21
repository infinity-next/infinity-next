<?php namespace App\Http\Controllers\Panel\Site;

use Role;
use App\Http\Controllers\Panel\PanelController;

class RolesController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Config Controller
	|--------------------------------------------------------------------------
	|
	| This is the site config controller, available only to admins.
	| Its only job is to load config panels and to validate and save the changes.
	|
	*/
	
	const VIEW_ROLES = "panel.site.roles";
	
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
	public function getIndex()
	{
		if (!$this->user->canAdminRoles() || !$this->user->canAdminPermissions())
		{
			return abort(403);
		}
		
		$roles = Role::all();
		
		return $this->view(static::VIEW_ROLES, [
			'roles' => $roles,
		]);
	}
	
	/**
	 * Validate and save changes.
	 *
	 * @return Response
	 */
	public function patchIndex(Request $request)
	{
		
	}
}
