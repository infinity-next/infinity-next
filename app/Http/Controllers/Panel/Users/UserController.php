<?php

namespace App\Http\Controllers\Panel\Users;

use App\Http\Controllers\Panel\PanelController;
use App\User;

/**
 * Lists and manages users.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class UserController extends PanelController
{
    const VIEW_DASHBOARD = 'panel.users.index';
    const VIEW_SHOW = 'panel.users.show';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.users';

    /**
     * Show the users dashboard to the user.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('username', 'asc')->paginate(15);

        return $this->view(static::VIEW_DASHBOARD, [
            'users' => $users,
        ]);
    }

    /**
     * Shows a single user's profile.
     *
     * @param  \App\User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $user->load('roles');

        $localRoles = $user->roles->filter(function ($item) {
            return !is_null($item->board_uri);
        });
        $globalRoles = $user->roles->diff($localRoles);

        return $this->view(static::VIEW_SHOW, [
            'profile' => $user,
            'globalRoles' => $globalRoles,
            'localRoles'  => $localRoles,
        ]);
    }
}
