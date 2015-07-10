<?php namespace App\Http\Controllers\Board;

use App\Board;
use App\Post;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;

use Illuminate\Http\Request;
use App\Validators\FileValidator;

use Input;
use File;
use Response;
use Validator;

class BoardController extends Controller {
	
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
	
	const VIEW_BOARD  = "board";
	const VIEW_THREAD = "board";
	const VIEW_LOGS   = "board.logs";
	
	/**
	 * Show the board index for the user.
	 * This is usually the last few threads, depending on the optional page
	 * parameter, which determines the thread offset.
	 *
	 * @var \App\Http\Requests\Request $request
	 * @var Board $board
	 * @var integer $page
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
		
		return $this->view(static::VIEW_BOARD, [
			'board'    => $board,
			'posts'    => $posts,
			'reply_to' => false,
			
			'pages'    => $pages,
			'page'     => $page,
			'pagePrev' => $pagePrev,
			'pageNext' => $pageNext,
		] );
	}
	
	/**
	 * Renders a thread.
	 *
	 * @var \App\Http\Requests\Request $request
	 * @var Board $board
	 * @return Response
	 */
	public function getLogs(Request $request, Board $board)
	{
		return $this->view(static::VIEW_LOGS, [
			'board' => $board,
			'logs'  => $board->getLogs(),
		]);
	}
	
	/**
	 * Renders a thread.
	 *
	 * @var \App\Http\Requests\Request $request
	 * @var Board $board
	 * @var integer|null $thread
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
		
		return $this->view(static::VIEW_THREAD, [
			'board'    => $board,
			'posts'    => [ $thread ],
			'reply_to' => $thread->board_id,
		]);
	}
	
	/**
	 * Handles the creation of a new thread or reply.
	 *
	 * @var \App\Http\Requests\PostRequest $request
	 * @var Board $board
	 * @var integer|null $thread
	 * @return Response (redirects to the thread view)
	 */
	public function putThread(PostRequest $request, Board $board, $thread = null)
	{
		// Create the post.
		$post = new Post($request->all());
		$post->submitTo($board, $thread);
		
		
		// Log staff posts.
		if ($post->capcode_id)
		{
			$this->log('log.post.capcode', $post, [
				"board_id"  => $post->board_id,
				"board_uri" => $post->board_uri,
				"capcode"   => $post->capcode->capcode,
				"role"      => $post->capcode->role,
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
	
	/**
	 * Supplies a user generated stylesheet based on board options.
	 *
	 * @var  Board  $board
	 * @return Response
	 */
	public function getStylesheet(Board $board)
	{
		$stylesheet  = $board->getStylesheet();
		$statusCode  = 200;
		$contentType = "text/css";
		
		if (strlen((string) $stylesheet) == 0)
		{
			return abort(404);
		}
		
		return response($stylesheet, $statusCode)
			->header('Content-Type', $contentType);
	}
}
