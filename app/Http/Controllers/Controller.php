<?php

namespace App\Http\Controllers;

use App\Board;
use App\Log;
use App\Http\MessengerResponse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Router as Router;
use Cache;
use Request;
use Settings;
use Validator;
use View;

abstract class Controller extends BaseController
{
    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    /**
     * Board model for this request.
     *
     * @var \App\Board
     */

    /**
     * Cache of the system's options.
     *
     * @var array
     */
    protected $options;

    protected function log()
    {
        ## TODO ## Remove this.
    }

    /**
     * Returns an system option's value.
     *
     * @param string $option
     *
     * @return string|null
     */
    protected function option($option_name)
    {
        global $app;

        if (is_null($app['settings'])) {
            return;
        }

        return $app['settings']($option_name);
    }

    /**
     * Modifies a template path to yield the correct result.
     *
     * @param string $template
     *
     * @return string
     */
    protected static function template($template)
    {
        return "content.{$template}";
    }

    /**
     * Modifies template arguments to include required information.
     *
     * @param array $options
     *
     * @return array
     */
    protected function templateOptions(array $options = [])
    {
        return ['c' => $this] + $options;
    }

    /**
     * Returns a validator that can be used to check registration details.
     *
     * @return Validator
     */
    protected function registrationValidator(array $data)
    {
        return Validator::make($data, [
            'username' => 'required|alpha_num|max:255|unique:users,username',
            'email   ' => 'email|max:255|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'captcha'  => 'required|captcha',
        ]);
    }

    /**
     * Creates a View with the requested content file.
     *
     * @param string $template
     * @param array  $options
     *
     * @return View
     */
    protected function makeView($template, array $options = array())
    {
        return View::make(
            $this->template($template),
            $this->templateOptions($options)
        );
    }
}
