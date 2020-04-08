<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\BoardAsset;
use App\FileStorage;
use App\Filesystem\Upload;
use App\Http\Controllers\Panel\PanelController;
use Request;
use Validator;
use Event;
use App\Events\BoardWasModified;

/**
 * Board asset controller, for customizing the look of a board.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 *
 * @since      0.6.0
 *
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 */
class AssetController extends PanelController
{
    const VIEW_ASSETS = 'panel.board.assets';
    const VIEW_CONFIG = 'panel.board.config';

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
    public function index(Board $board)
    {
        $this->authorize('configure', $board);

        return $this->makeView(static::VIEW_CONFIG, [
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
    public function destroy(Board $board)
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
    public function patch(Board $board)
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

        return $this->index($board);
    }

    /**
     * Add new assets.
     *
     * @return Response
     */
    public function put(Board $board)
    {
        $this->authorize('configure', $board);

        if ((bool) Request::input('delete', false)) {
            return $this->destroyAssets($board);
        }

        $input = Request::all();
        $assetType = Request::input('asset_type', false);
        $rules = [ 'asset_type' => [
            'required',
            'in:board_banner,board_banned,board_icon,board_flags,file_deleted,file_spoiler',
        ], ];

        foreach (BoardAsset::$validationRules as $assetName => $validationRule) {
            $rules["new_{$assetName}"] = array_merge([ "required_if:asset_type,{$assetName}", 'image' ], $validationRule);
        }

        $validator = Validator::make($input, $rules);

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
                $upload = new Upload($upload);
                $storage = $upload->process(false);

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
                }
                else {
                    return redirect()
                        ->back()
                        ->withErrors(['validation.file_generic']);
                }
            }
        }

        Event::dispatch(new BoardWasModified($board));

        return redirect()->back();
    }
}
