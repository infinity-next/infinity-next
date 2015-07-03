<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Http\Controllers\Panel\PanelController;
use Illuminate\Http\Request;
use Input;
use Validator;

class BoardsController extends PanelController {
	
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
	
	const VIEW_DASHBOARD = "panel.board.dashboard";
	const VIEW_CREATE    = "panel.board.create";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.board";
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$boards = $this->user->getBoardsWithConfigRights();
		
		return $this->view(static::VIEW_DASHBOARD, [
			'boards' => $boards,
		]);
	}
	
	/**
	 * Allows for the creation of a new board.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
		if (!$this->user->canCreateBoard())
		{
			return abort(403);
		}
		
		return $this->view(static::VIEW_CREATE);
	}
	
	/**
	 * Allows for the creation of a new board.
	 *
	 * @return Response
	 */
	public function putCreate(Request $request)
	{
		if (!$this->user->canCreateBoard())
		{
			return abort(403);
		}
		
		// Validate input.
		// If the user is anonymous, we must also be creating an account.
		$input = Input::all();
		
		if ($this->user->isAnonymous())
		{
			$validator = $this->registrar->validator($input);
			
			if ($validator->fails())
			{
				$this->throwValidationException(
					$request,
					$validator
				);
			}
		}
		
		// Validate the basic boardconstraints.
		$input['board_uri'] = strtolower( (string) $input['board_uri'] );
		$requirements = [
			'board_uri'   => [
				"required",
				"unique:boards,board_uri",
				"string",
				"regex:(" . Board::URI_PATTERN . ")",
			],
			'title'       => "required|string|between:1,255",
			'description' => "string|between:0,255"
		];
		
		$validator = Validator::make($input, $requirements);
		
		if ($validator->fails())
		{
			$this->throwValidationException(
				$request,
				$validator
			);
		}
		
		if ($this->user->isAnonymous())
		{
			$this->auth->login($this->registrar->create($request->all()));
			$this->user = $this->auth->user();
		}
		
		// Create the board.
		$board = new Board([
			'board_uri'   => $input['board_uri'],
			'title'       => $input['title'],
			'description' => $input['description'],
			'created_by'  => $this->user->user_id,
			'operated_by' => $this->user->user_id,
		]);
		$board->save();
		
		// Seed board ownership.
		$board->setOwner($this->user);
		
		return redirect("cp/board/{$board->board_uri}");
	}
}
