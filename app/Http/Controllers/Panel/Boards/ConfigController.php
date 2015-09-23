<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\BoardAsset;
use App\BoardSetting;
use App\FileStorage;
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
	
	const VIEW_ASSETS = "panel.board.assets";
	const VIEW_CONFIG = "panel.board.config";
	const VIEW_STAFF  = "panel.board.staff";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.board";
	
	/**
	 * View path for the tertiary (inner) navigation.
	 *
	 * @var string
	 */
	public static $navTertiary = "nav.panel.board.settings";
	
	/**
	 * Add a unique validator upon boot.
	 *
	 * @return void
	 */
	protected function boot()
	{
		Validator::resolver(function($translator, $data, $rules, $messages)
		{
			return new ComparisonValidator($translator, $data, $rules, $messages);
		});
	}
	
	/**
	 * Display existing assets.
	 *
	 * @return Response
	 */
	public function getAssets(Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		return $this->view(static::VIEW_CONFIG, [
			'board'   => $board,
			'banners' => $board->getBanners(),
			
			'tab'     => "assets",
		]);
	}
	
	/**
	 * Removes existing assets.
	 *
	 * @return Response
	 */
	public function patchAssets(Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$banners = $board->getBanners();
		$input   = Input::all();
		
		foreach ($banners as $bannerIndex => $banner)
		{
			if (!isset($input['banner'][$banner->board_asset_id]) || !$input['banner'][$banner->board_asset_id])
			{
				$storage = $banner->storage;
				
				$banner->forceDelete();
				unset($banners[$bannerIndex]);
				
				$storage->challengeExistence();
			}
		}
		
		return $this->view(static::VIEW_CONFIG, [
			'board'   => $board,
			'banners' => $banners,
			
			'tab'     => "assets",
		]);
	}
	
	/**
	 * Display existing assets.
	 *
	 * @return Response
	 */
	public function putAssets(Request $request, Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$input     = Input::all();
		$validator = Validator::make($input, [
			'asset_type' => [
				"required",
				"in:board_banner,file_deleted,file_none,file_spoiler",
			],
			
			'new_board_banner' => [
				"required_if:asset_type,board_banner",
				"image",
				"image_size:<=300,<=100",
			],
		]);
		
		if (!$validator->passes())
		{
			return redirect()
				->back()
				->withErrors($validator->errors());
		}
		
		// Fetch the asset.
		$upload = Input::file("new_{$input['asset_type']}");
		
		if(file_exists($upload->getPathname()))
		{
			$storage     = FileStorage::storeUpload($upload);
			
			$asset       = new BoardAsset();
			$asset->asset_type = "board_banner";
			$asset->board_uri  = $board->board_uri;
			$asset->file_id    = $storage->file_id;
			$asset->save();
		}
		
		return $this->view(static::VIEW_CONFIG, [
			'board'   => $board,
			'banners' => $board->getBanners(),
			
			'tab'     => "assets",
		]);
	}
	
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function getConfig(Request $request, Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$optionGroups = OptionGroup::getBoardConfig($board);
		
		return $this->view(static::VIEW_CONFIG, [
			'board'  => $board,
			'groups' => $optionGroups,
			
			'tab'    => "basic",
		]);
	}
	
	/**
	 * Validate and save changes.
	 *
	 * @return Response
	 */
	public function patchConfig(BoardConfigRequest $request, Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$request->setBoard($board);
		$request->validate();
		
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
				
				if (isset($input[$option->option_name]))
				{
					$option->option_value  = $input[$option->option_name];
					$setting->option_value = $input[$option->option_name];
					$setting->save();
				}
				else
				{
					$setting->delete();
				}
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
			
			'tab'    => "basic",
		]);
	}
	
	public function getIndex(Request $request, Board $board)
	{
		return $this->getConfig($request, $board);
	}
	
	public function patchIndex(BoardConfigRequest $request, Board $board)
	{
		return $this->patchConfig($request, $board);
	}
	
	/**
	 * List all staff members to the user.
	 *
	 * @return Response
	 */
	public function getStaff(Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$roles = $board->roles;
		$staff = $board->getStaff();
		
		return $this->view(static::VIEW_STAFF, [
			'board'  => $board,
			'roles'  => $roles,
			'staff'  => $staff,
			
			'tab'    => "staff",
		]);
	}
}
