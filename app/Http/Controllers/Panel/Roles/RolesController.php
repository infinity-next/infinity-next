<?php

namespace App\Http\Controllers\Panel\Roles;

use App\Role;
use App\Http\Controllers\Panel\PanelController;

/**
 * Lists and manages site roles.
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
    const VIEW_ROLES = 'panel.roles.index';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.users';

    /**
     * Show the application dashboard to the user.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('admin-users');
        
        $roles = Role::where('system', true)->orderBy('weight', 'desc')->get();

        return $this->makeView(static::VIEW_ROLES, [
            'roles' => $roles,
        ]);
    }
}
