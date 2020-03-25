<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\User;
use App\Http\Controllers\Panel\PanelController;
use Illuminate\Http\Request;
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
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function createUser(array $data)
    {
        return User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

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
        $boards = $this->user->getBoardsWithAssetRights();
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
        $boards = $this->user->getBoardsWithConfigRights();
        $boards->load('creator', 'operator');

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
        $boards = $this->user->getBoardsWithStaffRights();
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
        if (!$this->user->canCreateBoard()) {
            return abort(403);
        }

        $boardLastCreated = 0;
        $boardsOwned = 0;

        if (!$this->user->isAnonymous()) {
            foreach ($this->user->createdBoards as $board) {
                ++$boardsOwned;

                if ($board->created_at->timestamp > $boardLastCreated) {
                    $boardLastCreated = $board->created_at->timestamp;
                }
            }
        } elseif (!$this->user->canCreateUser()) {
            return abort(403);
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
        if (!$this->user->canCreateBoard()) {
            return abort(403);
        }

        $configErrors = [];

        // Check time and quantity restraints.
        if (!$this->user->canAdminConfig()) {
            $boardLastCreated = null;
            $boardsOwned = 0;
            $boardCreateTimer = $this->option('boardCreateTimer');
            $boardsCreateMax = $this->option('boardCreateMax');

            if (!$this->user->isAnonymous()) {
                foreach ($this->user->createdBoards as $board) {
                    ++$boardsOwned;

                    if (is_null($boardLastCreated) || $board->created_at->timestamp > $boardLastCreated->timestamp) {
                        $boardLastCreated = $board->created_at;
                    }
                }
            } elseif (!$this->user->canCreateUser()) {
                return abort(403);
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

        if ($this->user->isAnonymous()) {
            $validator = $this->registrationValidator($input);

            if ($validator->fails()) {
                $this->throwValidationException(
                    $request,
                    $validator
                );
            }
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
            return !$this->user->isAnonymous();
        });

        // Create the board model.
        $board = new Board([
            'board_uri' => $input['board_uri'],
            'title' => $input['title'],
            'description' => $input['description'],
        ]);

        $failed = $validator->fails();

        if (!$this->user->canCreateBoardWithBannedUri() && $board->hasBannedUri()) {
            $validator->errors()->add(
                'board_uri',
                trans('validation.custom.board_uri_banned')
            );
            $failed = true;
        }

        if ($failed) {
            $this->throwValidationException(
                $request,
                $validator
            );
        }

        // Create user if we have to.
        if ($this->user->isAnonymous()) {
            $this->auth->login($this->createUser    ($request->all()));
            $this->user = $this->auth->user();
        }

        $board->created_by = $this->user->user_id;
        $board->operated_by = $this->user->user_id;

        // Save the board.
        $board->save();

        // Seed board ownership permissions.
        $board->setOwner($this->user);

        $this->log('log.board.create', $board->board_uri);

        return redirect($board->getPanelUrl());
    }
}
