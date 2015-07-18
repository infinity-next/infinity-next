<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Http\Controllers\Panel\PanelController;
use Illuminate\Http\Request;

use Lang;
use Input;
use Validator;

use Event;
use App\Events\BoardWasCreated;

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
	const VIEW_STAFF     = "panel.board.staff";
	
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
		
		$boardLastCreated = 0;
		$boardsOwned = 0;
		
		if (!$this->user->isAnonymous())
		{
			foreach ($this->user->createdBoards as $board)
			{
				++$boardsOwned;
				
				if ($board->created_at->timestamp > $boardLastCreated)
				{
					$boardLastCreated = $board->created_at->timestamp;
				}
			}
		}
		
		return $this->view(static::VIEW_CREATE, [
			'boardLastCreated'  => $boardLastCreated,
			'boardsOwned'       => $boardsOwned,
			
			'boardCreateTimer'  => $this->option('boardCreateTimer'),
			'boardsCreateMax'   => $this->option('boardCreateMax'),
		]);
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
		
		
		// Check time and quantity restraints.
		if (!$this->user->canAdminConfig())
		{
			$configErrors     = [];
			$boardLastCreated = null;
			$boardsOwned      = 0;
			$boardCreateTimer = $this->option('boardCreateTimer');
			$boardsCreateMax  = $this->option('boardCreateMax');
			
			if (!$this->user->isAnonymous())
			{
				foreach ($this->user->createdBoards as $board)
				{
					++$boardsOwned;
					
					if (is_null($boardLastCreated) || $board->created_at->timestamp > $boardLastCreated->timestamp)
					{
						$boardLastCreated = $board->created_at;
					}
				}
			}
			
			if ($boardsCreateMax > 0 && $boardsOwned >= $boardsCreateMax)
			{
				$configErrors[] = Lang::choice("panel.error.board.create_more_than_max", $boardsCreateMax, [
					'boardsCreateMax' => $boardsCreateMax,
				]);
			}
			
			if ($boardCreateTimer > 0 && ( !is_null($boardLastCreated) && $boardLastCreated->diffInMinutes() < $boardCreateTimer))
			{
				$configErrors[] = Lang::choice("panel.error.board.create_so_soon", $boardLastCreated->addMinutes($boardCreateTimer)->diffInMinutes() + 1, [
					'boardCreateTimer' => $boardLastCreated->diffInMinutes(),
				]);
			}
			
			if (count($configErrors))
			{
				return redirect()->back()->withInput()->withErrors($configErrors);
			}
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
		
		// Seed board ownership permissions.
		$board->setOwner($this->user);
		
		$this->log("log.board.create", $board->board_uri);
		
		Event::fire(new BoardWasCreated($board, $this->user));
		
		return redirect("cp/board/{$board->board_uri}");
	}
	
}
