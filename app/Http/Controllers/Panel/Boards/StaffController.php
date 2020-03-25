<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\User;
use App\Http\Controllers\Panel\PanelController;
use Request;
use Validator;
use Event;
use App\Events\UserRolesModified;

/**
 * Lists and adds board staff.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class StaffController extends PanelController
{
    const VIEW_LIST = 'panel.board.staff';
    const VIEW_ADD = 'panel.board.staff.create';
    const VIEW_EDIT = 'panel.board.staff.edit';
    const VIEW_DELETE = 'panel.board.staff.delete';

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
     * List all staff members to the user.
     *
     * @return Response
     */
    public function index(Board $board)
    {
        if (!$board->canEditConfig($this->user)) {
            return abort(403);
        }

        $roles = $board->roles;
        $staff = $board->getStaff();

        return $this->view(static::VIEW_LIST, [
            'board' => $board,
            'roles' => $roles,
            'staff' => $staff,

            'tab' => 'staff',
        ]);
    }

    /**
     * Opens staff creation form.
     *
     * @return Response
     */
    public function create(Board $board)
    {
        if (!$board->canEditConfig($this->user)) {
            return abort(403);
        }

        $roles = $this->user->getAssignableRoles($board);
        $staff = $board->getStaff();

        return $this->view(static::VIEW_ADD, [
            'board' => $board,
            'roles' => $roles,
            'staff' => $staff,

            'tab' => 'staff',
        ]);
    }

    /**
     * Adds new staff.
     *
     * @return Response
     */
    public function store(Board $board)
    {
        if (!$board->canEditConfig($this->user)) {
            return abort(403);
        }

        $createUser = false;
        $roles = $this->user->getAssignableRoles($board);
        $rules = [];
        $existing = Request::input('staff-source') == 'existing';

        if ($existing) {
            $rules = [
                'existinguser' => [
                    'required',
                    'string',
                    'exists:users,username',
                ],
                'captcha' => [
                    'required',
                    'captcha',
                ],
            ];

            $input = Request::only('existinguser', 'captcha');
            $validator = Validator::make($input, $rules);
        } else {
            $createUser = true;
            $validator = $this->registrationValidator();
        }

        $castes = $roles->pluck('role_id');
        $casteRules = [
            'castes' => [
                'required',
                'array',
            ],
        ];
        $casteInput = Request::only('castes');
        $casteValidator = Validator::make($casteInput, $casteRules);

        $casteValidator->each('castes', [
            'in:'.$castes->implode(','),
        ]);


        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($validator->errors());
        } elseif ($casteValidator->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($casteValidator->errors());
        } elseif ($createUser) {
            $user = $this->registrar->create(Request::all());
        } else {
            $user = User::whereUsername(Request::only('existinguser'))->firstOrFail();
        }


        $user->roles()->detach($roles->pluck('role_id')->toArray());
        $user->roles()->attach($casteInput['castes']);

        Event::dispatch(new UserRolesModified($user));

        return redirect($board->getPanelUrl('staff'));
    }

    /**
     * Opens staff management form.
     *
     * @param  \App\Board  $board
     * @param  \App\User  $user
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Board $board, User $user)
    {
        if (!$this->user->canEditBoardStaffMember($user, $board)) {
            return abort(403);
        }

        $roles = $this->user->getAssignableRoles($board);
        $staff = $board->getStaff();

        $user->load('roles');

        return $this->view(static::VIEW_EDIT, [
            'board' => $board,
            'roles' => $roles,
            'staff' => $user,

            'tab' => 'staff',
        ]);
    }

    /**
     * Saves new castes to staff member.
     *
     * @param  \App\Board  $board
     * @param  \App\User  $user
     *
     * @return \Illuminate\Http\Response
     */
    public function patch(Board $board, User $user)
    {
        if (!$this->user->canEditBoardStaffMember($user, $board)) {
            return abort(403);
        }

        $user->load('roles');

        $roles = $this->user->getAssignableRoles($board);
        $castes = $roles->pluck('role_id');
        $rules = [
            'castes' => [
                'array',
            ],
        ];
        $input = Request::only('castes');
        $validator = Validator::make($input, $rules);

        $validator->each('castes', [
            'in:'.$castes->implode(','),
        ]);


        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($validator->errors());
        }


        $user->roles()->detach($roles->pluck('role_id')->toArray());

        if (is_array($input['castes'])) {
            $user->roles()->attach($input['castes']);
        }

        Event::dispatch(new UserRolesModified($user));

        if (count($input['castes'])) {
            return redirect()->back();
        } else {
            return redirect($board->getPanelUrl('staff'));
        }
    }

    /**
     * Presents staff dismissal confirmation.
     *
     * @param  \App\Board  $board
     * @param  \App\User  $user
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Board $board, User $user)
    {
        if (!$this->user->canEditBoardStaffMember($user, $board)) {
            return abort(403);
        }

        return $this->view(static::VIEW_DELETE, [
            'board' => $board,
            'tab' => 'staff',
            'staff' => $user,
        ]);
    }

    /**
     * Deletes staff position.
     *
     * @param  \App\Board  $board
     * @param  \App\User  $user
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Board $board, User $user)
    {
        if (!$this->user->canEditBoardStaffMember($user, $board)) {
            return abort(403);
        }

        if ($user->user_id === $this->user->user_id) {
            $this->validate(Request::all(), [
                'confirmation' => [
                    'required',
                    'boolean',
                ]
            ]);
        }

        $user->load('roles');

        $roles = $user->getBoardRoles($board);

        $user->roles()->detach($roles->pluck('role_id')->toArray());

        Event::dispatch(new UserRolesModified($user));

        return redirect($board->getPanelUrl('staff'));
    }
}
