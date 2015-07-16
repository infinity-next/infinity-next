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

use Event;
use App\Events\BoardWasModified;

class ConfigController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Config Controller
	|--------------------------------------------------------------------------
	|
	| This is the board config controller, available only to the board owner and admins.
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
	public function getIndex(Request $request, Board $board)
	{
		$optionGroups = OptionGroup::getBoardConfig($board);
		
		return $this->view(static::VIEW_CONFIG, [
			'board'  => $board,
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
		$input        = $request->all();
		$optionGroups = $request->getBoardOptions();
		
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
		
		$board->title        = $input['boardBasicTitle'];
		$board->description  = $input['boardBasicDesc'];
		$board->is_overboard = isset($input['boardBasicOverboard']) && !!$input['boardBasicOverboard'];
		$board->is_indexed   = isset($input['boardBasicIndexed']) && !!$input['boardBasicIndexed'];
		$board->is_worksafe  = isset($input['boardBasicWorksafe']) && !!$input['boardBasicWorksafe'];
		$board->save();
		
		Event::fire(new BoardWasModified($board));
		
		return $this->view(static::VIEW_CONFIG, [
			'board'  => $board,
			'groups' => $optionGroups,
		]);
	}
}
