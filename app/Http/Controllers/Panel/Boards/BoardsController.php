<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\User;
use App\Http\Controllers\Panel\PanelController;
use Lang;
use Request;
use Validator;

/**
 * Lists and creates boards.
 *
 * @category   Http
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class BoardsController extends PanelController
{
    const VIEW_DASHBOARD = 'panel.board.dashboard';
    const VIEW_CREATE = 'panel.board.create';
    const VIEW_STAFF = 'panel.board.staff';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.board';

    /**
     * Show the application dashboard to the user.
     * This is the config list.
     *
     * @return Response
     */
    public function getIndex()
    {
        return $this->getConfig();
    }

    /**
     * List boards with asset management rights.
     *
     * @return Response
     */
    public function getAssets()
    {
        $boards = user()->getBoardsWithAssetRights();
        $boards->load('creator', 'operator');

        return $this->view(static::VIEW_DASHBOARD, [
            'boards' => $boards,
            'suffix' => 'assets',
        ]);
    }

    /**
     * List boards with config management rights.
     *
     * @return Response
     */
    public function getConfig()
    {
        $boards = user()->getBoardsWithConfigRights();

        if (method_exists($boards, 'load')) {
            $boards->load('creator', 'operator');
        }

        return $this->view(static::VIEW_DASHBOARD, [
            'boards' => $boards,
            'suffix' => 'config',
        ]);
    }

    /**
     * List boards with staff management rights.
     *
     * @return Response
     */
    public function getStaff()
    {
        $boards = user()->getBoardsWithStaffRights();
        $boards->load('creator', 'operator');

        return $this->view(static::VIEW_DASHBOARD, [
            'boards' => $boards,
            'suffix' => 'staff',
        ]);
    }

    /**
     * Allows for the creation of a new board.
     *
     * @return Response
     */
    public function getCreate()
    {
        $this->authorize('create', Board::class);

        $boardLastCreated = 0;
        $boardsOwned = 0;

        if (!user()->isAnonymous()) {
            foreach (user()->createdBoards as $board) {
                ++$boardsOwned;

                if ($board->created_at->timestamp > $boardLastCreated) {
                    $boardLastCreated = $board->created_at->timestamp;
                }
            }
        }
        else {
            $this->can('register');
        }

        return $this->view(static::VIEW_CREATE, [
            'boardLastCreated' => $boardLastCreated,
            'boardsOwned' => $boardsOwned,

            'boardCreateTimer' => $this->option('boardCreateTimer'),
            'boardsCreateMax' => $this->option('boardCreateMax'),
        ]);
    }

    /**
     * Allows for the creation of a new board.
     *
     * @return Response
     */
    public function putCreate(Request $request)
    {
        $this->authorize('create', Board::class);

        $configErrors = [];

        // Check time and quantity restraints.
        if (!user()->can('admin-config')) {
            $boardLastCreated = null;
            $boardsOwned = 0;
            $boardCreateTimer = $this->option('boardCreateTimer');
            $boardsCreateMax = $this->option('boardCreateMax');

            if (!user()->isAnonymous()) {
                foreach (user()->createdBoards as $board) {
                    ++$boardsOwned;

                    if (is_null($boardLastCreated) || $board->created_at->timestamp > $boardLastCreated->timestamp) {
                        $boardLastCreated = $board->created_at;
                    }
                }
            }
            else {
                $this->authorize('register');
            }

            if ($boardsCreateMax > 0 && $boardsOwned >= $boardsCreateMax) {
                $configErrors[] = Lang::choice('panel.error.board.create_more_than_max', $boardsCreateMax, [
                    'boardsCreateMax' => $boardsCreateMax,
                ]);
            }

            if ($boardCreateTimer > 0 && (!is_null($boardLastCreated) && $boardLastCreated->diffInMinutes() < $boardCreateTimer)) {
                $configErrors[] = Lang::choice('panel.error.board.create_so_soon', $boardLastCreated->addMinutes($boardCreateTimer)->diffInMinutes() + 1, [
                    'boardCreateTimer' => $boardLastCreated->diffInMinutes(),
                ]);
            }
        }

        if (count($configErrors)) {
            return redirect()->back()
                ->withInput()
                ->withErrors($configErrors);
        }


        // Validate input.
        // If the user is anonymous, we must also be creating an account.
        $input = Request::all();

        if (user()->isAnonymous()) {
            $this->registrationValidator($input)->validate();
        }

        // Generate a list of banned URIs.
        $bannedUris = array_filter(explode("\n", $this->option('boardUriBanned')));
        $bannedUris[] = 'cp';
        $bannedUris = implode(',', $bannedUris);

        // Validate the basic boardconstraints.
        $input['board_uri'] = strtolower((string) $input['board_uri']);
        $requirements = [
            'board_uri' => [
                'required',
                'unique:boards,board_uri',
                'string',
                'regex:('.Board::URI_PATTERN.')',
            ],
            'title' => 'required|string|between:1,255',
            'description' => 'string|between:0,255',
        ];

        $validator = Validator::make($input, $requirements);

        // Hide a second captcha on registration+create combo forms.
        $validator->sometimes('captcha', 'required|captcha', function ($input) {
            return !user()->isAnonymous();
        });

        // Create the board model.
        $board = new Board([
            'board_uri' => $input['board_uri'],
            'title' => $input['title'],
            'description' => $input['description'],
        ]);

        $failed = $validator->fails();

        if (!user()->canCreateBoardWithBannedUri() && $board->hasBannedUri()) {
            $validator->errors()->add(
                'board_uri',
                trans('validation.custom.board_uri_banned')
            );
            $failed = true;
        }

        if ($failed) {
            $validator->validate();
        }

        // Create user if we have to.
        if (user()->isAnonymous()) {
            auth()->login($this->createUser($request->all()));
        }

        $board->created_by = user()->user_id;
        $board->operated_by = user()->user_id;

        // Save the board.
        $board->save();

        return redirect($board->getPanelUrl());
    }
}
