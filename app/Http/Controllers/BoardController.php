<?php namespace App\Http\Controllers;

use View;
use \App\Board;

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
	public function index( $uri )
	{
		if ($board = Board::findOrFail($uri))
		{
			$threads = $board->getThreadsForIndex();
			
			$posts = array();
			foreach ($threads as $thread) {
				$posts[$thread->id] = $thread->getRepliesForIndex();
			}
			
			return View::make('board', [
					'board'   => $board,
					'threads' => $threads,
					'posts'   => $posts,
				] );
		}
	}
}
