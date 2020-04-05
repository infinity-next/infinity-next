<?php

namespace App\Http\Controllers\Panel;

class HomeController extends PanelController
{
    /*
    |--------------------------------------------------------------------------
    | Home Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders your application's "dashboard" for users that
    | are authenticated. Of course, you are free to change or remove the
    | controller as you wish. It is just here to get your app started!
    |
    */

    const VIEW_HOME = 'panel.home';

    /**
     * Asserts middleware.
     */
    public function boot()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function getIndex()
    {
        return $this->makeView(static::VIEW_HOME);
    }
}
