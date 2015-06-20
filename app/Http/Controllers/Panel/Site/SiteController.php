<?php namespace App\Http\Controllers\Panel\Site;

use App\Http\Controllers\Panel\PanelController;

class SiteController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Site Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/
	
	const VIEW_DASHBOARD = "panel.site.dashboard";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.site";
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return $this->view( static::VIEW_DASHBOARD );
	}
	
	/**
	 * Spit out phpinfo() input and stop.
	 *
	 * @return Response
	 */
	public function getPhpinfo()
	{
		if (!$this->user->can('site.config'))
		{
			return abort(403);
		}
		
		phpinfo();
	}
}
