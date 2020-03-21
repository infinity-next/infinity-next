<?php

namespace App\Http\Controllers;

use App\Board;
use App\Log;
use App\Http\MessengerResponse;
use App\Services\UserManager;
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

    /**
     * Constructs all controllers with the user and board as properties.
     *
     * @param  UserManager  $manager
     * @param  Router       $router
     *
     * @return void
     */
    public function __construct(UserManager $manager, Router $router)
    {
        $this->userManager = $manager;
        $this->auth = $manager->auth;
        $this->user = $manager->user;

        $board = app(Board::class);
        $this->board = $board->exists ? $board : null;

        view()->share([
            'board' => $board ?: null,
            'user' => $this->user,
        ]);

        $this->boot();
    }

    /**
     * Hook called immediately after __construct.
     *
     * @return void
     */
    protected function boot()
    {
        // Nothing!
    }

    /**
     * Logs an action.
     *
     * @param string           $action
     * @param App\Board|string $board
     * @param array            $data
     *
     * @return App\Log
     */
    protected function log($action, $board = null, $data = null)
    {
        $board_uri = null;
        $action_details = null;

        if ($board instanceof \App\Board) {
            $board_uri = $board->board_uri;
            $action_details = $data;
        } elseif ($board instanceof \App\Post) {
            $board_uri = $board->board_uri;
            $action_details = $data;
        } elseif (is_string($board)) {
            $board_uri = $board;
            $action_details = $data;
        } elseif (is_array($board) && is_null($data)) {
            $board_uri = null;
            $action_details = $board;
        }

        if (!is_null($action_details) && !is_array($action_details)) {
            $action_details = [$action_details];
        }

        if (!is_null($action_details)) {
            $action_details = json_encode($action_details);
        }

        $log = new Log([
            'action_name' => $action,
            'action_details' => $action_details,
            'user_id' => $this->user->isAnonymous() ? null : $this->user->user_id,
            'user_ip' => inet_pton(Request::getClientIp()),
            'board_uri' => $board_uri,
        ]);

        return $log->save();
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
    protected function view($template, array $options = array())
    {
        return View::make(
            $this->template($template),
            $this->templateOptions($options)
        );
    }

    /**
     * Creates a View with the requested content file.
     *
     * @param string $template
     * @param array  $options
     *
     * @return View
     */
    protected function viewAsJson($template, array $options = array())
    {
        $html = $this->view($template, $options);

        return new MessengerResponse($html);
    }
}
