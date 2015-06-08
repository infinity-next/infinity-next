<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\BoardSetting;
use App\OptionGroup;
use App\Http\Requests\BoardConfigRequest;
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
	public function getIndex(BoardConfigRequest $request, Board $board)
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
	public function patchIndex(BoardConfigRequest $request, Board $board)
	{
		// Re-validate the request with new rules specific to the board.
		$request->setBoard($board);
		$request->setUser($this->user);
		$request->validate();
		
		$input        = $request->all();
		$optionGroups = $request->getBoardOptionGroups();
		
		foreach ($optionGroups as $optionGroup)
		{
			foreach ($optionGroup->options as $option)
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
