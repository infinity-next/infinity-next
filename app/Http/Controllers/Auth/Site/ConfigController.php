<?php namespace App\Http\Controllers\Auth\Site;

use App\OptionGroup;
use App\Http\Controllers\Auth\CpController;
use DB;
use Input;
use Request;
use Validator;
use View;

class ConfigController extends CpController {
	
	/*
	|--------------------------------------------------------------------------
	| Config Controller
	|--------------------------------------------------------------------------
	|
	| This is the site config controller, available only to admins.
	| Its only job is to load config panels and to validate and save the changes.
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
	
	/**
	 * Validate and save changes.
	 *
	 * @return Response
	 */
	public function patchIndex(Request $request)
	{
		if (!$this->user->can('site.config'))
		{
			return abort(403);
		}
		
		$optionGroups = OptionGroup::getSiteConfig();
		$requirements = [];
		
		// From each group,
		foreach ($optionGroups as $optionGroup)
		{
			// From each option within each group,
			foreach ($optionGroup->options as $option)
			{
				// Pull the validation parameter string,
				$requirements[$option->option_name] = $option->getValidation();
			}
		}
		
		// Build our validator.
		$input     = Input::all();
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
				if ($option->option_value != $input[$option->option_name])
				{
					$option->option_value = $input[$option->option_name];
					$option->save();
				}
			}
		}
		
		return View::make('auth.config.site', [
			'groups' => $optionGroups,
		]);
	}
}
