<?php namespace App\Http\Controllers\Board;

use App\Board;
use App\Post;
use App\Http\Controllers\MainController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

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
	
	/**
	 * Show the board index for the user.
	 * This is usually the last few threads.
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
		$threads = $board->getThreadsForIndex($page);
		
		$posts = array();
		foreach ($threads as $thread)
		{
			$posts[$thread->id] = $thread->getRepliesForIndex();
		}
		
		return View::make('board', [
			'board'    => $board,
			'threads'  => $threads,
			'posts'    => $posts,
			'reply_to' => false,
			
			'pages'    => $pages,
			'page'     => $page,
			'pagePrev' => $pagePrev,
			'pageNext' => $pageNext,
		] );
	}
	
	/**
	 * Show the board index for the user.
	 * This is usually the last few threads.
	 *
	 * @return Response
	 */
	public function getThread(Request $request, Board $board, $thread = NULL)
	{
		if (is_null($thread))
		{
			return redirect($board->uri);
		}
		
		
		$post = $thread = $board->getLocalThread($thread);
		
		while (!is_null($thread->reply_to))
		{
			$thread = Post::find($thread->reply_to);
		}
		
		if ($post->board_id != $thread->board_id)
		{
			return redirect("{$board->uri}/thread/{$thread->board_id}#{$post->board_id}");
		}
		
		
		$posts = array();
		$posts[$thread->id] = $thread->getReplies();
		
		return View::make('board', [
			'board'    => $board,
			'threads'  => [$thread],
			'posts'    => $posts,
			'reply_to' => $thread->board_id,
		] );
	}
	
	/**
	 * Handles the creation of a new thread or reply.
	 *
	 * @param  string  $uri (board uri)
	 * @param  int     $thread_id (optional, new thread is unspecified)
	 * @return Response (redirects to the thread view)
	 */
	public function postThread(Request $request, Board $board, $thread_id = false)
	{
		if ($input = Input::all())
		{
			$this->validate($request, [
					'body' => 'required',
					'captcha' => 'required|captcha',
				]);
			
			$post = new Post($input);
			
			if ($thread_id !== false)
			{
				$thread = $board->getLocalThread($thread_id);
				$post->reply_to = $thread->id;
			}
			
			$board->threads()->save($post);
			
			if ($thread_id === false)
			{
				return redirect("{$board->uri}/thread/{$post->board_id}");
			}
			else
			{
				return redirect("{$board->uri}/thread/{$thread->board_id}#{$post->board_id}");
			}
		}
		
		return redirect($board->uri);
	}
}
