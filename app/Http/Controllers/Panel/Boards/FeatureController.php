<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Http\Controllers\Panel\PanelController;
use Request;
use Validator;

/**
 * Features or unfeatures a board.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class FeatureController extends PanelController
{
    const VIEW_FEATURE = 'panel.board.feature';

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

    public function boot()
    {
        view()->share([
            'board' => app(Board::class),
        ]);
    }

    public function show(Board $board)
    {
        $this->authorize('feature', $board);

        return $this->makeView(static::VIEW_FEATURE);
    }

    public function feature(Board $board)
    {
        $this->authorize('feature', $board);

        $input = Request::all();
        $rules = [
            'action' => [
                'required',
                'in:update,delete',
            ],
        ];
        $validator = Validator::make($input, $rules);

        if ($input['action'] === 'update') {
            $board->featured_at = $board->freshTimestamp();
        } else {
            $board->featured_at = null;
        }

        $board->save();

        return $this->getIndex($board);
    }
}
