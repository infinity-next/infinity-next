<?php

namespace App\Http\Controllers\Panel\Users;

use App\Http\Controllers\Panel\PanelController;

class UsersController extends PanelController
{
    /*
    |--------------------------------------------------------------------------
    | Users Controller
    |--------------------------------------------------------------------------
    |
    | "Users" is for any sort of group or admin-level account utilities,
    | including Groups and Permissions.
    |
    */

    const VIEW_DASHBOARD = 'panel.users.dashboard';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.users';

    /**
     * Show the users dashboard to the user.
     *
     * @return Response
     */
    public function getIndex()
    {
        return $this->view(static::VIEW_DASHBOARD);
    }
}
