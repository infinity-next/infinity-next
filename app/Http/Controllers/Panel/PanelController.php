<?php namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;

abstract class PanelController extends Controller {
	
	/**
	 * View path for the primary navigation.
	 *
	 * @var string
	 */
	public static $navPrimary = "nav.panel";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.home";
	
}
