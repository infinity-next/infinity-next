<?php namespace App\Http\Controllers;

use App\Board;
use App\Log;
use App\Support\Anonymous;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Request;
use View;

abstract class MainController extends Controller {
	
	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth      = $auth;
		$this->registrar = $registrar;
		
		if ($auth->guest())
		{
			$this->user  = new Anonymous;
		}
		else
		{
			$this->user  = $auth->user();
		}
		
		View::share('boardbar', Board::getBoardListBar());
		View::share('user', $this->user);
	}
	
	public function log($action, $board = null, $data = null)
	{
		$board_uri      = null;
		$action_details = null;
		
		if ($board instanceof Board)
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
			'user_ip'        => Request::getClientIp(),
			'board_uri'      => $board_uri,
		]);
		
		return $log->save();
	}
}