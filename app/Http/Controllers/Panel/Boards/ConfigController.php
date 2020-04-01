<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\BoardAsset;
use App\BoardSetting;
use App\BoardTag;
use App\FileStorage;
use App\OptionGroup;
use App\Http\Requests\BoardConfigRequest;
use App\Http\Controllers\Panel\PanelController;
use Carbon\Carbon;
use Request;
use Validator;
use Event;
use App\Events\BoardWasModified;

/**
 * This is the board config controller, available with appropriate permissions.
 * Its job is to load config panels and to validate and save the changes.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2015 Infinity Next Development Group
 *
 * @since      0.5.1
 *
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 */
class ConfigController extends PanelController
{
    const VIEW_ASSETS = 'panel.board.assets';
    const VIEW_CONFIG = 'panel.board.config';
    const VIEW_STAFF = 'panel.board.staff';
    const VIEW_TAGS = 'panel.board.config';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.board';

    /**
     * View path for the tertiary (inner) navigation.
     *
     * @var string
     */
    public static $navTertiary = 'nav.panel.board.settings';

    /**
     * Display existing assets.
     *
     * @return Response
     */
    public function getAssets(Board $board)
    {
        $this->authorize('configure', $board);

        return $this->view(static::VIEW_CONFIG, [
            'board' => $board,
            'banned' => $board->getBannedImages(),
            'banners' => $board->getBanners(),
            'flags' => $board->getFlags(),

            'tab' => 'assets',
        ]);
    }

    /**
     * Clear singular board assets.
     *
     * @return Response
     */
    public function destroyAssets(Request $request, Board $board)
    {
        $this->authorize('configure', $board);

        $input = Request::all();
        $validator = Validator::make($input, [
            'asset_type' => [
                'required',
                'in:board_banner,board_icon,file_deleted,file_spoiler',
            ],
        ]);

        if (!$validator->passes()) {
            return redirect()
                ->back()
                ->withErrors($validator->errors());
        }

        $assets = $board->assets()
            ->with('storage')
            ->where('asset_type', $input['asset_type'])
            ->get();

        foreach ($assets as $asset) {
            $asset->delete();
            $asset->storage->challengeExistence();
        }

        Event::dispatch(new BoardWasModified($board));

        return redirect()->back();
    }

    /**
     * Removes existing assets.
     *
     * @return Response
     */
    public function patchAssets(Board $board)
    {
        $this->authorize('configure', $board);

        $assetsToKeep = Request::input('asset', []);
        $assetsToName = Request::input('asset_name', []);
        $assetsToReplace = Request::file('asset_file', []);
        $assetType = Request::input('patching', false);
        $assets = $board->assets()->where('asset_type', $assetType)->get();

        foreach ($assets as $assetIndex => $asset) {
            if (!isset($assetsToKeep[$asset->board_asset_id]) || !$assetsToKeep[$asset->board_asset_id]) {
                $asset->delete();
                $asset->storage->challengeExistence();
            } else {
                $changed = false;

                if (isset($assetsToName[$asset->board_asset_id])) {
                    $changed = true;
                    $asset->asset_name = $assetsToName[$asset->board_asset_id];
                }

                if (isset($assetsToReplace[$asset->board_asset_id]) && !is_null($assetsToReplace[$asset->board_asset_id])) {
                    $newFile = $assetsToReplace[$asset->board_asset_id];

                    switch ($assetType) {
                        // Don't copy this logic.
                        // This is a lazy duplicate of putAssets.
                        case 'board_flags':
                            $imageRules = array_merge(['required'], BoardAsset::getRulesForFlags($board));

                            $validator = Validator::make([
                                'file' => $newFile,
                            ], [
                                'file' => $imageRules,
                            ]);

                            if (!$validator->passes()) {
                                return redirect()
                                    ->back()
                                    ->withErrors($validator->errors());
                            }

                            $storage = FileStorage::storeUpload($newFile);

                            if ($storage->exists) {
                                $changed = true;
                                $asset->file_id = $storage->file_id;
                            } else {
                                return redirect()
                                    ->back()
                                    ->withErrors(['validation.custom.file_generic']);
                            }

                            break;
                    }
                }

                $asset->save();
            }
        }

        Event::dispatch(new BoardWasModified($board));

        return $this->getAssets($board);
    }

    /**
     * Add new assets.
     *
     * @return Response
     */
    public function putAssets(Request $request, Board $board)
    {
        $this->authorize('configure', $board);

        if ((bool) Request::input('delete', false)) {
            return $this->destroyAssets($request, $board);
        }

        $input = Request::all();
        $assetType = Request::input('asset_type', false);
        $validator = Validator::make($input, [
            'asset_type' => [
                'required',
                'in:board_banner,board_banned,board_icon,board_flags,file_deleted,file_spoiler',
            ],

            'new_board_banned' => [
                'required_if:asset_type,board_banned',
                'image',
                'dimensions:max_height=500,max_width=500,min_height=100,min_width=100',
                'max:250',
            ],

            'new_board_banner' => [
                'required_if:asset_type,board_banner',
                'image',
                'dimensions:max_height=100,max_width=300',
                'max:1024',
            ],

            'new_board_flags' => [
                'required_if:asset_type,board_flags',
                'array',
                'min:1',
                'max:500',
            ],

            'new_board_icon' => [
                'required_if:asset_type,board_icon',
                'image',
                'dimensions:width=64,height=64,ratio=1/1',
                'max:50',
            ],

            'new_file_deleted' => [
                'required_if:asset_type,file_deleted',
                'image',
                'dimensions:max_height=500,max_width=500,min_height=100,min_width=100',
                'max:250',
            ],

            'new_file_spoiler' => [
                'required_if:asset_type,file_spoiler',
                'image',
                'dimensions:max_height=500,max_width=500,min_height=100,min_width=100',
                'max:250',
            ],
        ]);

        if (!$validator->passes()) {
            return redirect()
                ->back()
                ->withErrors($validator->errors());
        }

        // Fetch the asset.
        $multiples = $assetType == 'board_banner' || $assetType == 'board_banned' || $assetType == 'board_flags';

        if ($assetType == 'board_flags') {
            $new = $input["new_{$input['asset_type']}"];
            $names = $new['name'] ?? [];
            $uploads = $new['file'] ?? [];
            $rules = [];

            $nameRules = [
                'required',
                'string',
                'between:1,128',
            ];
            $imageRules = array_merge(['required'], [
                'image',
                'dimensions:max_height=64,max_width=64,min_height=8,min_width=8',
                'max:16',
            ]);

            foreach (range(0, count($uploads) - 1) as $index) {
                $rules["name.{$index}"] = $nameRules;
                $rules["file.{$index}"] = $imageRules;
            }

            $validator = Validator::make($new, $rules);

            if (!$validator->passes()) {
                return redirect()
                    ->back()
                    ->withErrors($validator->errors());
            }
        }
        else {
            $uploads = [Request::file("new_{$input['asset_type']}")];
        }

        foreach ((array) $uploads as $index => $upload) {
            if (file_exists($upload->getPathname())) {
                $storage = FileStorage::storeUpload($upload);

                if ($storage->exists) {
                    if (!$multiples) {
                        $assets = $board->assets()
                            ->with('storage')
                            ->where('asset_type', $input['asset_type'])
                            ->get();

                        foreach ($assets as $asset) {
                            $asset->delete();
                            $asset->storage->challengeExistence();
                        }
                    }

                    $asset = new BoardAsset();
                    $asset->asset_type = $input['asset_type'];
                    $asset->asset_name = isset($names[$index]) ? $names[$index] : null;
                    $asset->board_uri = $board->board_uri;
                    $asset->file_id = $storage->file_id;
                    $asset->save();
                } else {
                    return redirect()
                        ->back()
                        ->withErrors(['validation.custom.file_generic']);
                }
            }
        }

        Event::dispatch(new BoardWasModified($board));

        return redirect()->back();
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getConfig(Board $board)
    {
        $this->authorize('configure', $board);

        $optionGroups = OptionGroup::getBoardConfig($board);

        return $this->view(static::VIEW_CONFIG, [
            'board' => $board,
            'groups' => $optionGroups,

            'tab' => 'basic',
        ]);
    }

    /**
     * Validate and save changes.
     *
     * @return Response
     */
    public function patchConfig(BoardConfigRequest $request, Board $board)
    {
        $this->authorize('configure', $board);

        $input = $request->all();
        $optionGroups = $request->getBoardOptions();
        $settings = [];

        foreach ($optionGroups as $optionGroup) {
            foreach ($optionGroup->options as $option) {
                $setting = BoardSetting::firstOrNew([
                    'option_name' => $option->option_name,
                    'board_uri' => $board->board_uri,
                ]);

                // Skip locked items unless we can edit them.
                $locking = isset($input['lock'][$option->option_name]) && (bool) $input['lock'][$option->option_name];

                if ($setting->isLocked() && !$this->user->canEditSettingLock($board, $option)) {
                    continue;
                }

                // Save the value.
                if (isset($input[$option->option_name])) {
                    $setting->option_value = $input[$option->option_name];
                } elseif ($option->format == 'onoff') {
                    $setting->option_value = false;
                } elseif (!$locking) {
                    // Delete it if we have no value and aren't saving.
                    $setting->delete();
                    continue;
                } else {
                    $setting->option_value = null;
                }

                // Set our locked status.
                if ($locking) {
                    $setting->is_locked = (bool) $input['lock'][$option->option_name];
                }

                $setting->save();

                $settings[] = $setting;
            }
        }

        $board->title = $input['boardBasicTitle'];
        $board->description = $input['boardBasicDesc'];
        $board->is_overboard = isset($input['boardBasicOverboard']) && (bool) $input['boardBasicOverboard'];
        $board->is_indexed = isset($input['boardBasicIndexed']) && (bool) $input['boardBasicIndexed'];
        $board->is_worksafe = isset($input['boardBasicWorksafe']) && (bool) $input['boardBasicWorksafe'];
        $board->setRelation('settings', $settings);
        $board->updated_at = Carbon::now();
        $board->save();

        Event::dispatch(new BoardWasModified($board));

        return redirect()->back();
    }

    public function getIndex(Request $request, Board $board)
    {
        return $this->getConfig($board);
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
        $this->authorize('configure', $board);

        $tagArray = [];

        foreach ($board->tags as $tag) {
            $tagArray[] = $tag->tag;
        }

        return $this->view(static::VIEW_TAGS, [
            'board' => $board,
            'tags' => $tagArray,

            'tab' => 'tags',
        ]);
    }

    /**
     * Put tags.
     *
     * @return Response
     */
    public function putTags(Board $board)
    {
        $this->authorize('configure', $board);

        $input = Request::all();
        $rules = [
            'boardTags' => [
                'array',
                'min:0',
                'max:5',
            ],
            'boardTags.*' => [
                'string',
                'alpha_dash',
                'max:24',
            ]
        ];

        if (isset($input['boardTags']) && is_array($input['boardTags'])) {
            $input['boardTags'] = array_filter($input['boardTags']);
        }

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return redirect()
                ->back()
                ->withErrors($validator->errors());
        }


        $tags = [];
        $tagArray = [];

        foreach ($input['boardTags'] as $boardTag) {
            $boardTag = (string) strtolower($boardTag);

            if (strlen($boardTag) && !isset($tagArray[$boardTag])) {
                // Add the tag to the list of set tags to prevent duplicates.
                $tagArray[$boardTag] = true;

                // Find or create the board tag.
                $tags[] = BoardTag::firstOrCreate([
                    'tag' => $boardTag,
                ]);
            }
        }

        $board->tags()->detach();

        if (count($tags)) {
            $tags = $board->tags()->saveMany($tags);
        }

        Event::dispatch(new BoardWasModified($board));

        return $this->view(static::VIEW_TAGS, [
            'board' => $board,
            'tags' => array_keys($tagArray),

            'tab' => 'tags',
        ]);
    }
}
