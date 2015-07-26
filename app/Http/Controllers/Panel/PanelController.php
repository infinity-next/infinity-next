<?php namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;

use Cookie;

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
	
	
	/**
	 * Passes a warning message if we do not have a CSRF token.
	 *
	 * @param  array  $options
	 * @return array
	 */
	public function templateOptions(array $options = array())
	{
		if (is_null(Cookie::get('XSRF-TOKEN')))
		{
			$options = (array) array_merge_recursive([
				'messages' => [
					trans('panel.error.auth.csrf_token'),
				],
			], $options);
		}
		
		return parent::templateOptions($options);
	}
}
