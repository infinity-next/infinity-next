<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Role;
use App\Http\Controllers\Panel\PanelController;
use Input;
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
     * @param \App\Board $board
     *
     * @return Response
     */
    public function getIndex(Board $board, Role $role = null)
    {
        if (!$this->user->canEditConfig($board)) {
            return abort(403);
        }

        return $this->view(static::VIEW_EDIT, [
            'board' => $board,
            'role' => $role,
            'tab' => 'roles',
        ]);
    }

    /**
     * Update basic details for an existing role.
     *
     * @param \App\Board $board
     *
     * @return Response
     */
    public function patchIndex(Board $board, Role $role)
    {
        if (!$this->user->canEditConfig($board)) {
            return abort(403);
        }

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

        $validator = Validator::make(Input::all(), $rules);

        $validator->sometimes('roleCaste', 'not_in:'.$castes->implode(','), function ($input) use ($castes) {
            return $castes->count();
        });

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($validator->errors());
        }

        $role->caste = strtolower(Input::get('roleCaste'));
        $role->name = Input::get('roleName');
        $role->capcode = Input::get('roleCapcode');
        $role->save();

        return $this->getIndex($board, $role);
    }


    public function getDelete(Board $board, Role $role)
    {
        if (!$this->user->canEditConfig($board)) {
            return abort(403);
        }

        return $this->view(static::VIEW_DELETE, [
            'board' => $board,
            'role' => $role,
            'tab' => 'roles',
        ]);
    }


    public function destroyDelete(Board $board, Role $role)
    {
        if (!$this->user->canEditConfig($board)) {
            return abort(403);
        }

        $role->delete();

        return redirect($board->getPanelUrl('roles'));
    }
}
