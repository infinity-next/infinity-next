<?php namespace App\Http\Controllers;

use App\Board;
use App\Log;
use App\Services\UserManager;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Router as Router;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Cache;
use Input;
use Request;
use Settings;
use View;

abstract class Controller extends BaseController {
	
	use DispatchesCommands, ValidatesRequests;
	
	/**
	 * Cache of the system's options
	 *
	 * @var array
	 */
	protected $options;
	
	/**
	 * @return void
	 */
	public function __construct(UserManager $manager, Router $router)
	{
		$this->userManager = $manager;
		$this->auth        = $manager->auth;
		$this->registrar   = $manager->registrar;
		$this->user        = $manager->user;
		
		View::share('user', $this->user);
		
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
	 * @param  string  $action
	 * @param  App\Board|String  $board
	 * @param  Array $data
	 * @return App\Log
	 */
	public function log($action, $board = null, $data = null)
	{
		$board_uri      = null;
		$action_details = null;
		
		if ($board instanceof \App\Board)
		{
			$board_uri      = $board->board_uri;
			$action_details = $data;
		}
		else if ($board instanceof \App\Post)
		{
			$board_uri      = $board->board_uri;
			$action_details = $data;
		}
		else if (is_string($board))
		{
			$board_uri      = $board;
			$action_details = $data;
		}
		else if(is_array($board) && is_null($data))
		{
			$board_uri      = null;
			$action_details = $board;
		}
		
		if (!is_null($action_details) && !is_array($action_details))
		{
			$action_details = [ $action_details ];
		}
		
		if (!is_null($action_details))
		{
			$action_details = json_encode( $action_details );
		}
		
		$log = new Log([
			'action_name'    => $action,
			'action_details' => $action_details,
			'user_id'        => $this->user->isAnonymous() ? null : $this->user->user_id,
			'user_ip'        => inet_pton(Request::getClientIp()),
			'board_uri'      => $board_uri,
		]);
		
		return $log->save();
	}
	
	/**
	 * Returns an system option's value.
	 *
	 * @param  string  $option
	 * @return string|null
	 */
	public function option($option_name)
	{
		global $app;
		
		if (is_null($app['settings']))
		{
			return null;
		}
		
		return $app['settings']($option_name);
	}
	
	/**
	 * Creates a View with the requested content file.
	 *
	 * @param  string  $template
	 * @param  array  $options
	 * @return View
	 */
	public function view($template, array $options = array())
	{
		return View::make(
			$this->template($template),
			$this->templateOptions($options)
		);
	}
	
	/**
	 * Modifies a template path to yield the correct result.
	 *
	 * @param  string  $template
	 * @return string
	 */
	public static function template($template)
	{
		return "content.{$template}";
	}
	
	/**
	 * Modifies template arguments to include required information.
	 *
	 * @param  array  $options
	 * @return array
	 */
	public function templateOptions(array $options = array())
	{
		return (array) array_merge([
			'c'          => &$this,
			'controller' => &$this,
		], $options);
	}
	
	/**
	 * Returns a validator that can be used to check registration details.
	 *
	 * @return Validator
	 */
	public function registrationValidator()
	{
		$validator = $this->registrar->validator(Request::all());
		$rules     = $validator->getRules();
		
		$rules['username'][] = "alpha_num";
		$rules['username'][] = "unique:users,username";
		$rules['username'][] = "unique:users,email";
		
		$rules['captcha'] = "required|captcha";
		
		$validator->setRules($rules);
		
		return $validator;
	}
}