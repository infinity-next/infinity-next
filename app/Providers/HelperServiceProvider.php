<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Includes helper functions automatically..
 *
 * @category   Provider
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register the helper functions.
     *
     * Load all helper functions in out app/Helpers/ directory.
     */
    public function register()
    {
        foreach (glob(app_path().'/Helpers/*.php') as $filename) {
            require $filename;
        }
    }
}
