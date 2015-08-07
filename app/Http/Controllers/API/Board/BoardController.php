<?php namespace App\Http\Controllers\API\Board;

use App\Board;
use App\Post;

use App\Contracts\ApiController;
use App\Http\Controllers\PageController as ParentController;

class BoardController extends ParentController implements ApiController {
	
	/**
	 * Show the board index for the user.
	 * This is usually the last few threads, depending on the optional page
	 * parameter, which determines the thread offset.
	 *
	 * @var Board $board
	 * @var integer $page
	 * @return Response
	 */
	public function getIndex(Board $board, $page = 1)
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
		
		return $posts;
	}
	
	/**
	 * Returns a thread and its replies to the client. 
	 *
	 * @var Board $board
	 * @var integer|null $thread
	 * @return Response
	 */
	public function getThread(Board $board, $thread)
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
		
		return $thread;
	}
	
	/**
	 * Show the catalog (gridded) board view.
	 *
	 * @var Board $board
	 * @return Response
	 */
	public function getCatalog(Board $board)
	{
		// Load our list of threads and their latest replies.
		return $board->getThreadsForCatalog();
	}
	
}