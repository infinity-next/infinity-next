<?php

namespace App\Http\Controllers\Panel\Site;

use App\Http\Controllers\Panel\PanelController;

class SiteController extends PanelController
{
    /*
    |--------------------------------------------------------------------------
    | Site Controller
    |--------------------------------------------------------------------------
    |
    | This controller is the landing page for basic application level config
    | and controls.
    |
    */

    const VIEW_DASHBOARD = 'panel.site.dashboard';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.site';

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getIndex()
    {
        return $this->makeView(static::VIEW_DASHBOARD);
    }

    /**
     * Spit out phpinfo() input and stop.
     *
     * @return Response
     */
    public function getPhpinfo()
    {
        $this->authorize('admin-config');

        phpinfo();
    }
}
