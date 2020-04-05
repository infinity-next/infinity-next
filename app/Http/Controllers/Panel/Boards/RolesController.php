<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Role;
use App\Http\Controllers\Panel\PanelController;
use Request;
use Validator;

/**
 * Lists and creates board roles.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class RolesController extends PanelController
{
    const VIEW_ROLES = 'panel.board.roles';
    const VIEW_CREATE = 'panel.board.roles.create';

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
     * List the board roles.
     *
     * @return Response
     */
    public function get(Board $board)
    {
        $this->authorize('configure', $board);

        $roles = Role::whereBoardRole($board, user())->get();

        return $this->makeView(static::VIEW_ROLES, [
            'board' => $board,
            'roles' => $roles,
            'tab' => 'roles',
        ]);
    }

    /**
     * Show the role creation form.
     *
     * @param \App\Board $board
     *
     * @return Response
     */
    public function create(Board $board)
    {
        $this->authorize('configure', $board);

        $roles = Role::whereCanParentForBoard($board, user())->get();
        $choices = [];

        foreach ($roles as $role) {
            $choices[$role->getDisplayName()] = $role->role;
        }

        return $this->makeView(static::VIEW_CREATE, [
            'board' => $board,
            'role' => null,
            'choices' => $choices,
            'tab' => 'roles',
        ]);
    }

    /**
     * Add a new role.
     *
     * @param \App\Board $board
     *
     * @return Response
     */
    public function store(Board $board)
    {
        $this->authorize('configure', $board);

        $roles = Role::whereCanParentForBoard($board, user())->get();
        $castes = $board->getRoleCastes(Request::input('roleType'))->get()->pluck('caste');

        $rules = [
            'roleType' => [
                'required',
                'string',
                'in:'.$roles->pluck('role')->implode(','),
            ],
            'roleCaste' => [
                'nullable',
                'string',
                'alpha_num',
                "unique:roles,role,{$board->board_uri},board_uri",
            ],
            'roleName' => [
                'nullable',
                'string',
            ],
            'roleCapcode' => [
                'nullable',
                'string',
            ],
        ];

        $validator = Validator::make(Request::all(), $rules);

        $validator->sometimes('roleCaste', 'not_in:'.$castes->implode(','), function ($input) use ($castes) {
            return $castes->count();
        });

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($validator->errors());
        }

        $role = new Role();
        $role->board_uri = $board->board_uri;
        $role->inherit_id = $roles->where('role', strtolower(Request::input('roleType')))->pluck('role_id')[0];
        $role->role = strtolower(Request::input('roleType'));
        $role->caste = strtolower(Request::input('roleCaste'));
        $role->name = Request::input('roleName') ?: "user.role.{$role->role}";
        $role->capcode = Request::input('roleCapcode');
        $role->weight = 5 + constant(Role::class.'::WEIGHT_'.strtoupper(Request::input('roleType')));
        $role->save();

        return redirect($role->getPanelUrl('permissions'));
    }
}
