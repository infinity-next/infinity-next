<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\BoardSetting;
use App\BoardTag;
use App\FileStorage;
use App\OptionGroup;
use App\Http\Requests\BoardConfigRequest;
use App\Http\Controllers\Panel\PanelController;
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
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getConfig(Board $board)
    {
        $this->authorize('configure', $board);

        $optionGroups = OptionGroup::getBoardConfig($board);

        return $this->makeView(static::VIEW_CONFIG, [
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

                if ($setting->isLocked() && !user()->canEditSettingLock($board, $option)) {
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
        $board->updated_at = now();
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

        return $this->makeView(static::VIEW_TAGS, [
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

        return $this->makeView(static::VIEW_TAGS, [
            'board' => $board,
            'tags' => array_keys($tagArray),

            'tab' => 'tags',
        ]);
    }
}
