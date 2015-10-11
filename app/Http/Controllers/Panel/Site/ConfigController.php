<?php namespace App\Http\Controllers\Panel\Site;

use App\OptionGroup;
use App\SiteSetting;
use App\Http\Controllers\Panel\PanelController;

use DB;
use Input;
use Request;
use Validator;

use Event;
use App\Events\SiteSettingsWereModified;

class ConfigController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Config Controller
	|--------------------------------------------------------------------------
	|
	| This is the site config controller, available only to admins.
	| Its only job is to load config panels and to validate and save the changes.
	|
	*/
	
	const VIEW_CONFIG = "panel.site.config";
	
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
		if (!$this->user->canAdminConfig())
		{
			return abort(403);
		}
		
		$optionGroups = OptionGroup::getSiteConfig();
		
		return $this->view(static::VIEW_CONFIG, [
			'groups' => $optionGroups,
		]);
	}
	
	/**
	 * Validate and save changes.
	 *
	 * @return Response
	 */
	public function patchIndex(Request $request)
	{
		if (!$this->user->canAdminConfig())
		{
			return abort(403);
		}
		
		$input        = Input::all();
		$optionGroups = OptionGroup::getSiteConfig();
		$requirements = [];
		
		// From each group,
		foreach ($optionGroups as $optionGroup)
		{
			// From each option within each group,
			foreach ($optionGroup->options as $option)
			{
				if (!isset($input[$option->option_name]))
				{
					$input[$option->option_name] = null;
				}
				
				// Pull the validation parameter string,
				$requirements[$option->option_name] = $option->getValidation();
				$input[$option->option_name]        = $option->getSanitaryInput($input[$option->option_name]);
			}
		}
		
		// Build our validator.
		$validator = Validator::make($input, $requirements);
		
		if ($validator->fails())
		{
			return redirect(Request::path())
				->withErrors($validator->errors()->all())
				->withInput();
		}
		
		foreach ($optionGroups as $optionGroup)
		{
			foreach ($optionGroup->options as $option)
			{
				$setting = SiteSetting::firstOrNew([
					'option_name'  => $option->option_name,
				]);
				
				$option->option_value  = $input[$option->option_name];
				$setting->option_value = $input[$option->option_name];
				$setting->save();
			}
		}
		
		Event::fire(new SiteSettingsWereModified());
		
		return $this->view(static::VIEW_CONFIG, [
			'groups' => $optionGroups,
		]);
	}
}
