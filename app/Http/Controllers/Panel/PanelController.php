<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Cookie;

abstract class PanelController extends Controller
{
    /**
     * View path for the primary navigation.
     *
     * @var string
     */
    public static $navPrimary = 'nav.panel';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Passes a warning message if we do not have a CSRF token.
     *
     * @param array $options
     *
     * @return array
     */
    public function templateOptions(array $options = array())
    {
        if (is_null(Cookie::get(config('session.cookie')))) {
            if (isset($options['messages']['xsrf-token-missing'])) {
                unset($options['messages']['xsrf-token-missing']);
            }

            $options = (array) array_merge_recursive([
                'messages' => [
                    'xsrf-token-missing' => trans('panel.error.auth.csrf_token'),
                ],
            ], $options);
        }

        return parent::templateOptions($options);
    }
}
