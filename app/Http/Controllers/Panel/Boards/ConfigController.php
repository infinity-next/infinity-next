<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\BoardAsset;
use App\BoardSetting;
use App\BoardTag;
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
	const VIEW_TAGS   = "panel.board.config";
	
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
			'banned'  => $board->getBannedImages(),
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
		
		$assetsToKeep = Input::get('asset', []);
		$assetType    = Input::get('patching', false);
		$assets       = $board->assets()->where('asset_type', $assetType)->get();
		
		foreach ($assets as $assetIndex => $asset)
		{
			if (!isset($assetsToKeep[$asset->board_asset_id]) || !$assetsToKeep[$asset->board_asset_id])
			{
				$asset->delete();
				$asset->storage->challengeExistence();
			}
		}
		
		Event::fire(new BoardWasModified($board));
		
		return $this->getAssets($board);
	}
	
	/**
	 * Clear singular board assets.
	 *
	 * @return Response
	 */
	public function deleteAssets(Request $request, Board $board)
	{
		$input     = Input::all();
		$validator = Validator::make($input, [
			'asset_type' => [
				"required",
				"in:board_banner,board_icon,file_deleted,file_spoiler",
			],
		]);
		
		if (!$validator->passes())
		{
			return redirect()
				->back()
				->withErrors($validator->errors());
		}
		
		$assets = $board->assets()
			->with('storage')
			->where('asset_type', $input['asset_type'])
			->get();
		
		foreach ($assets as $asset)
		{
			$asset->delete();
			$asset->storage->challengeExistence();
		}
		
		Event::fire(new BoardWasModified($board));
		
		return $this->getAssets($board);
	}
	
	/**
	 * Add new assets.
	 *
	 * @return Response
	 */
	public function putAssets(Request $request, Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		if (!!Input::get('delete', false))
		{
			return $this->deleteAssets($request, $board);
		}
		
		$input     = Input::all();
		$assetType = Input::get('asset_type', false);
		$validator = Validator::make($input, [
			'asset_type' => [
				"required",
				"in:board_banner,board_banned,board_icon,file_deleted,file_spoiler",
			],
			
			'new_board_banned' => [
				"required_if:asset_type,board_banned",
				"image",
				"image_size:100-500",
				"max:250",
			],
			
			'new_board_banner' => [
				"required_if:asset_type,board_banner",
				"image",
				"image_size:<=300,<=100",
				"max:1024",
			],
			
			'new_board_icon' => [
				"required_if:asset_type,board_icon",
				"image",
				"image_aspect:1",
				"image_size:64,64",
				"max:50",
			],
			
			'new_file_deleted' => [
				"required_if:asset_type,file_deleted",
				"image",
				"image_size:100-500",
				"max:250",
			],
			
			'new_file_spoiler' => [
				"required_if:asset_type,file_spoiler",
				"image",
				"image_size:100-500",
				"max:250",
			],
		]);
		
		if (!$validator->passes())
		{
			return redirect()
				->back()
				->withErrors($validator->errors());
		}
		
		// Fetch the asset.
		$upload    = Input::file("new_{$input['asset_type']}");
		$multiples = $assetType == "board_banner" || $assetType == "board_banned";
		
		if(file_exists($upload->getPathname()))
		{
			$storage     = FileStorage::storeUpload($upload);
			
			if ($storage->exists)
			{
				if (!$multiples)
				{
					$assets = $board->assets()
						->with('storage')
						->where('asset_type', $input['asset_type'])
						->get();
					
					foreach ($assets as $asset)
					{
						$asset->delete();
						$asset->storage->challengeExistence();
					}
				}
				
				$asset             = new BoardAsset();
				$asset->asset_type = $input['asset_type'];
				$asset->board_uri  = $board->board_uri;
				$asset->file_id    = $storage->file_id;
				$asset->save();
			}
			else
			{
				return redirect()
					->back()
					->withErrors([ "validation.custom.file_generic" ]);
			}
		}
		
		Event::fire(new BoardWasModified($board));
		
		return $this->getAssets($board);
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
		$input        = $request->all();
		$optionGroups = $request->getBoardOptions();
		$settings     = [];
		
		foreach ($optionGroups as &$optionGroup)
		{
			foreach ($optionGroup->options as &$option)
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
				else if ($option->format == "onoff")
				{
					$option->option_value  = false;
					$setting->option_value = false;
					$setting->save();
				}
				else
				{
					$setting->delete();
					continue;
				}
				
				$settings[] = $setting;
			}
		}
		
		$board->title        = $input['boardBasicTitle'];
		$board->description  = $input['boardBasicDesc'];
		$board->is_overboard = isset($input['boardBasicOverboard']) && !!$input['boardBasicOverboard'];
		$board->is_indexed   = isset($input['boardBasicIndexed']) && !!$input['boardBasicIndexed'];
		$board->is_worksafe  = isset($input['boardBasicWorksafe']) && !!$input['boardBasicWorksafe'];
		$board->save();
		
		$board->setRelation('settings', collect($settings));
		
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
	 * Display tags.
	 *
	 * @return Response
	 */
	public function getTags(Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$tagArray = [];
		
		foreach ($board->tags as $tag)
		{
			$tagArray[] = $tag->tag;
		}
		
		return $this->view(static::VIEW_TAGS, [
			'board'   => $board,
			'tags'    => $tagArray,
			
			'tab'     => "tags",
		]);
	}
	
	/**
	 * Put tags.
	 *
	 * @return Response
	 */
	public function putTags(Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$input = Input::all();
		$rules = [
			'boardTags' => [
				"array",
				"min:0",
				"max:5",
			]
		];
		
		if (isset($input['boardTags']) && is_array($input['boardTags']))
		{
			$input['boardTags'] = array_filter($input['boardTags']);
		}
		
		$validator = Validator::make($input, $rules);
		
		$validator->each('boardTags', [
			'string',
			'alpha_dash',
			'max:24',
		]);
		
		if (!$validator->passes())
		{
			return redirect()
				->back()
				->withErrors($validator->errors());
		}
		
		
		$tags     = [];
		$tagArray = [];
		
		foreach ($input['boardTags'] as $boardTag)
		{
			$boardTag = (string) $boardTag;
			
			if (strlen($boardTag) && !isset($tagArray[$boardTag]))
			{
				// Add the tag to the list of set tags to prevent duplicates.
				$tagArray[$boardTag] = true;
				
				// Find or create the board tag.
				$tags[] = BoardTag::firstorCreate([
					'tag' => $boardTag,
				]);
			}
		}
		
		$board->tags()->detach();
		
		if (count($tags))
		{
			$tags = $board->tags()->saveMany($tags);
		}
		
		Event::fire(new BoardWasModified($board));
		
		return $this->view(static::VIEW_TAGS, [
			'board'   => $board,
			'tags'    => array_keys($tagArray),
			
			'tab'     => "tags",
		]);
	}
	
}
