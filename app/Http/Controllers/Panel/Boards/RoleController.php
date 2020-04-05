<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Role;
use App\Http\Controllers\Panel\PanelController;
use Request;
use Validator;
use Event;
use App\Events\RoleWasModified;

/**
 * Manages a board role.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class RoleController extends PanelController
{
    const VIEW_EDIT = 'panel.board.roles.edit';
    const VIEW_DELETE = 'panel.board.roles.delete';

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
     * Show role basic details.
     *
     * @param \App\Board  $board
     * @param \App\Role   $role
     *
     * @return Response
     */
    public function getIndex(Board $board, ?Role $role = null)
    {
        $this->authorize('configure', $board);

        return $this->makeView(static::VIEW_EDIT, [
            'board' => $board,
            'role' => $role,
            'tab' => 'roles',
        ]);
    }

    /**
     * Update basic details for an existing role.
     *
     * @param \App\Board  $board
     * @param \App\Role   $role
     *
     * @return Response
     */
    public function patchIndex(Board $board, Role $role)
    {
        $this->authorize('configure', $board);

        $castes = $board->getRoleCastes($role->role, $role->role_id)->get()->pluck('caste');

        $rules = [
            'roleCaste' => [
                'string',
                'alpha_num',
            ],
            'roleName' => [
                'string',
            ],
            'roleCapcode' => [
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

        $role->caste = strtolower(Request::input('roleCaste'));
        $role->name = Request::input('roleName');
        $role->capcode = Request::input('roleCapcode');
        $role->save();

        return $this->getIndex($board, $role);
    }

    /**
     * Shows a confirm delete page.
     *
     * @param \App\Board  $board
     * @param \App\Role   $role
     *
     * @return Response
     */
    public function getDelete(Board $board, Role $role)
    {
        $this->authorize('configure', $board);

        return $this->makeView(static::VIEW_DELETE, [
            'board' => $board,
            'role' => $role,
            'tab' => 'roles',
        ]);
    }

    /**
     * Deletes a role.
     *
     * @param \App\Board  $board
     * @param \App\Role   $role
     *
     * @return Response
     */
    public function destroyDelete(Board $board, Role $role)
    {
        $this->authorize('configure', $board);

        $role->delete();

        return redirect($board->getPanelUrl('roles'));
    }
}
