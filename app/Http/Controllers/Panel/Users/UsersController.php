<?php

namespace App\Http\Controllers\Panel\Users;

use App\Http\Controllers\Panel\PanelController;

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
class UsersController extends PanelController
{
    const VIEW_DASHBOARD = 'panel.users.index';

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
        return $this->view(static::VIEW_DASHBOARD);
    }
}
