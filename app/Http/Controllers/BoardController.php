<?php namespace App\Http\Controllers;

use Input;
use Request;
use View;
use \App\Board;
use \App\Post;

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
	
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		// $this->middleware('guest');
	}
	
	/**
	 * Show the board index for the user.
	 * This is usually the last few threads.
	 *
	 * @return Response
	 */
	public function index($uri)
	{
		$board = Board::findOrFail($uri);
		
		$threads = $board->getThreadsForIndex();
		
		$posts = array();
		foreach ($threads as $thread)
		{
			$posts[$thread->id] = $thread->getRepliesForIndex();
		}
		
		return View::make('board', [
				'reply_to' => "",
				'board'    => $board,
				'threads'  => $threads,
				'posts'    => $posts,
			] );
	}
	
	
	/**
	 * Handles the creation of a new thread.
	 *
	 * @return Response (redirects to the thread view)
	 */
	public function post($uri, $thread_id = false)
	{
		$board = Board::findOrFail($uri);
		
		if ($input = Input::all())
		{
			$post = new Post( $input );
			
			if ($thread_id !== false)
			{
				$thread = Post::findOnBoard($uri, $thread_id, true);
				$post->reply_to = $thread->id;
			}
			
			$board->threads()->save( $post );
			
			if ($thread_id === false)
			{
				return redirect("{$uri}/thread/{$post->board_id}");
			}
			else
			{
				return redirect("{$uri}/thread/{$thread->board_id}#{$post->board_id}");
			}
		}
		
		return redirect($uri);
	}
	
	/**
	 * Show the board index for the user.
	 * This is usually the last few threads.
	 *
	 * @return Response
	 */
	public function thread($uri, $post)
	{
		$board = Board::findOrFail($uri);
		
		$post = $thread = Post::findOnBoard($uri, $post, true);
		
		while ($thread->reply_to)
		{
			$thread = Post::find($thread->reply_to);
		}
		
		if ($post->board_id != $thread->board_id)
		{
			return redirect("{$uri}/thread/{$thread->board_id}#{$post->board_id}");
		}
		
		
		$posts = array();
		$posts[$thread->id] = $thread->getReplies();
		
		return View::make('board', [
				'reply_to' => $thread->board_id,
				
				'board'    => $board,
				'threads'  => [$thread],
				'posts'    => $posts,
			] );
	}
}
