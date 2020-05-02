<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\User;
use App\Http\Controllers\Panel\PanelController;
use Illuminate\Auth\Events\Registered;
use Hash;
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
    const VIEW_LIST = 'panel.board.staff.index';
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
        $this->authorize('configure', $board);

        $roles = $board->roles;
        $staff = $board->getStaff();

        return $this->makeView(static::VIEW_LIST, [
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
        $this->authorize('configure', $board);

        $roles = user()->getAssignableRoles($board);
        $staff = $board->getStaff();

        return $this->makeView(static::VIEW_ADD, [
            'board' => $board,
            'roles' => $roles,
            'staff' => $staff,

            'tab' => 'staff',
        ]);
    }

    /**
     * Adds new staff.
     *
     * @param  \App\Board  $board
     *
     * @return Response
     */
    public function store(Board $board)
    {
        $this->authorize('configure', $board);

        $user = user();
        $createUser = false;
        $roles = $user->getAssignableRoles($board);
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
        }
        else {
            $createUser = true;
            $validator = $this->registrationValidator(Request::all());
        }

        $castes = $roles->pluck('role_id');
        $casteRules = [
            'castes' => [
                'required',
                'array',
            ],
            'castes.*' => [
                'required',
                'in:'.$castes->implode(','),
            ]
        ];
        $casteInput = Request::only('castes');
        $casteValidator = Validator::make($casteInput, $casteRules);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($validator->errors());
        }
        elseif ($casteValidator->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($casteValidator->errors());
        }
        elseif ($createUser) {
            $staff = User::create([
                'username' => Request::input('username'),
                'email' => Request::input('email'),
                'password' => Hash::make(Request::input('password')),
            ]);
        }
        else {
            $staff = User::whereUsername(Request::input('existinguser'))->firstOrFail();
        }

        $staff->roles()->detach($roles->pluck('role_id')->toArray());
        $staff->roles()->attach($casteInput['castes']);

        Event::dispatch(new UserRolesModified($staff));

        return redirect($board->getPanelUrl('staff'));
    }

    /**
     * Opens staff management form.
     *
     * @param  \App\Board  $board
     * @param  \App\User   $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Board $board, User $staff)
    {
        $this->authorize('editStaff', [$board, $staff]);

        $roles = user()->getAssignableRoles($board);
        $staff = $board->getStaff();

        $staff->load('roles');

        return $this->makeView(static::VIEW_EDIT, [
            'board' => $board,
            'roles' => $roles,
            'staff' => $staff,

            'tab' => 'staff',
        ]);
    }

    /**
     * Saves new castes to staff member.
     *
     * @param  \App\Board  $board
     * @param  \App\User   $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function patch(Board $board, User $staff)
    {
        $this->authorize('editStaff', [$board, $staff]);

        $staff->load('roles');

        $roles = $staff->getAssignableRoles($board);
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

        $staff->roles()->detach($roles->pluck('role_id')->toArray());

        if (is_array($input['castes'])) {
            $staff->roles()->attach($input['castes']);
        }

        Event::dispatch(new UserRolesModified($staff));

        if (count($input['castes'])) {
            return redirect()->back();
        }
        else {
            return redirect($board->getPanelUrl('staff'));
        }
    }

    /**
     * Presents staff dismissal confirmation.
     *
     * @param  \App\Board  $board
     * @param  \App\User   $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Board $board, User $staff)
    {
        $this->authorize('editStaff', [$board, $staff]);

        return $this->makeView(static::VIEW_DELETE, [
            'board' => $board,
            'tab' => 'staff',
            'staff' => $staff,
        ]);
    }

    /**
     * Deletes staff position.
     *
     * @param  \App\Board  $board
     * @param  \App\User   $staff
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Board $board, User $staff)
    {
        $this->authorize('editStaff', [$board, $staff]);
        $user = user();

        if ($user->user_id === $staff->user_id) {
            $this->validate(Request::all(), [
                'confirmation' => [
                    'required',
                    'boolean',
                ]
            ]);
        }

        $staff->load('roles');

        $roles = $staff->getBoardRoles($board);

        $staff->roles()->detach($roles->pluck('role_id')->toArray());

        Event::dispatch(new UserRolesModified($staff));

        return redirect($board->getPanelUrl('staff'));
    }
}
