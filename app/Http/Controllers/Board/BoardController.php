<?php namespace App\Http\Controllers\Board;

use App\Ban;
use App\Board;
use App\Post;
use App\Http\Controllers\MainController;
use App\Http\Requests\PostRequest;

use Illuminate\Http\Request;

use Input;
use File;
use Response;
use Validator;
use View;

class BoardController extends MainController {
	
	/*
	|--------------------------------------------------------------------------
	| Board Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles any requests that point to a directory which is
	| otherwise unavailable. It will determine if such a board exists and then
	| distribute content based on what what additional directries are specified
	| and what information is available to the accessing user.
	|
	*/
	
	const VIEW_BANNED = "banned";
	const VIEW_BOARD  = "board";
	const VIEW_THREAD = "board";
	
	/**
	 * Show the board index for the user.
	 * This is usually the last few threads, depending on the optional page
	 * parameter, which determines the thread offset.
	 *
	 * @return Response
	 */
	public function getIndex(Request $request, Board $board, $page = 1)
	{
		// Determine what page we are on.
		$pages = $board->getPageCount();
		
		// Clamp the page to real values.
		if ($page <= 0)
		{
			$page = 1;
		}
		elseif ($page > $pages)
		{
			$page = $pages;
		}
		
		// Determine if we have a next/prev button.
		$pagePrev = ($page > 1) ? $page - 1 : false;
		$pageNext = ($page < $pages) ? $page + 1 : false;
		
		// Load our list of threads and their latest replies.
		$posts = $board->getThreadsForIndex($page);
		
		return View::make(static::VIEW_BOARD, [
			'board'    => $board,
			'posts'    => $posts,
			'reply_to' => false,
			
			'pages'    => $pages,
			'page'     => $page,
			'pagePrev' => $pagePrev,
			'pageNext' => $pageNext,
		] );
	}
	
	
	public function getLogs(Request $request, Board $board)
	{
		return View::make('content.logs', [
			'board' => $board,
			'logs'  => $board->getLogs(),
		]);
	}
	
	/**
	 * Renders a thread.
	 *
	 * @return Response
	 */
	public function getThread(Request $request, Board $board, $thread = null)
	{
		if (is_null($thread))
		{
			return redirect($board->board_uri);
		}
		
		// Pull the thread.
		$thread = $board->getThread($thread);
		
		if (!$thread)
		{
			return abort(404);
		}
		
		if ($thread->reply_to)
		{
			return redirect("{$board->board_uri}/thread/{$thread->op->board_id}");
		}
		
		return View::make(static::VIEW_THREAD, [
			'board'    => $board,
			'posts'    => [ $thread ],
			'reply_to' => $thread->board_id,
		]);
	}
	
	/**
	 * Handles the creation of a new thread or reply.
	 *
	 * @return Response (redirects to the thread view)
	 */
	public function putThread(PostRequest $request, Board $board, $thread = null)
	{
		// Check for bans.
		if (Ban::isBanned($request->ip(), $board))
		{
			return abort(403);
		}
		
		// Re-validate the request with new rules specific to the board.
		$request->setBoard($board);
		$request->setUser($this->user);
		$request->validate();
		
		
		// Clean up and authorize input.
		$input = Input::all();
		
		if (is_array($input['files']))
		{
			// Having an [null] file array passes validation.
			$input['files'] = array_filter($input['files']);
		}
		
		if (isset($input['capcode']) && $input['capcode'])
		{
			if (!$this->user->isAnonymous())
			{
				$role = $this->user->roles->where('role_id', $input['capcode'])->first();
				
				if ($role && $role->capcode != "")
				{
					$this->capcode_id = (int) $role->role_id;
					$this->author     = $this->user->username;
				}
			}
			else
			{
				unset($input['capcode']);
			}
		}
		
		
		// Create the post.
		$post = new Post();
		$post->submit($input, $board, $thread);
		
		
		// Log staff posts.
		if ($post->capcode_id)
		{
			$this->log('log.post.capcode', $post, [
				"board_id"  => $post->board_id,
				"board_uri" => $post->board_uri,
				"capcode"   => $role->capcode,
				"role"      => $role->role,
			]);
		}
		
		
		// Redirect to the new post or thread.
		if (is_null($thread))
		{
			return redirect("{$board->board_uri}/thread/{$post->board_id}");
		}
		else
		{
			return redirect("{$board->board_uri}/thread/{$thread->board_id}#{$post->board_id}");
		}
	}
}
