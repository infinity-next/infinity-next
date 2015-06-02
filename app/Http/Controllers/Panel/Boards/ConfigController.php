<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\BoardSetting;
use App\OptionGroup;
use App\Http\Controllers\Panel\PanelController;
use App\Validators\ComparisonValidator;
use DB;
use Input;
use Request;
use Validator;

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
	
	const VIEW_CONFIG = "panel.board.config";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.board";
	
	protected function boot()
	{
		Validator::resolver(function($translator, $data, $rules, $messages)
		{
			return new ComparisonValidator($translator, $data, $rules, $messages);
		});
	}
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function getIndex(Board $board)
	{
		if (!$this->user->can('board.config', $board))
		{
			return abort(403);
		}
		
		$optionGroups = OptionGroup::getBoardConfig($board);
		
		return $this->view(static::VIEW_CONFIG, [
			'groups' => $optionGroups,
		]);
	}
	
	/**
	 * Validate and save changes.
	 *
	 * @return Response
	 */
	public function patchIndex(Request $request, Board $board)
	{
		if (!$this->user->can('board.config'))
		{
			return abort(403);
		}
		
		$input        = Input::all();
		$optionGroups = OptionGroup::getBoardConfig($board);
		$requirements = [];
		
		// From each group,
		foreach ($optionGroups as $optionGroup)
		{
			// From each option within each group,
			foreach ($optionGroup->options as $option)
			{
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
		
		foreach ($optionGroups as &$optionGroup)
		{
			foreach ($optionGroup->options as &$option)
			{
				$setting = BoardSetting::firstOrNew([
					'option_name'  => $option->option_name,
					'board_uri'    => $board->board_uri,
				]);
				
				$option->option_value  = $input[$option->option_name];
				$setting->option_value = $input[$option->option_name];
				$setting->save();
			}
		}
		
		return $this->view(static::VIEW_CONFIG, [
			'groups' => $optionGroups,
		]);
	}
}
