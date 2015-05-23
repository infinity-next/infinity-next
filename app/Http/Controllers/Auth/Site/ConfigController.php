<?php namespace App\Http\Controllers\Auth\Site;

use App\OptionGroup;
use App\Http\Controllers\Auth\CpController;
use View;

class ConfigController extends CpController {
	
	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		if (!$this->user->can('site.config'))
		{
			return abort(403);
		}
		
		$optionGroups = OptionGroup::getSiteConfig();
		
		return View::make('auth.config.site', [
			'groups' => $optionGroups,
		]);
	}
	
}
