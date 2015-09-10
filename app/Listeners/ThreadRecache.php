<?php namespace App\Listeners;

use App\Post;
use App\Listeners\Listener;
use Cache;

class ThreadRecache extends Listener
{
	
	/**
	 * Create the event listener.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}
	
	/**
	 * Handle the event.
	 *
	 * @param  Event  $event
	 * @return void
	 */
	public function handle($event)
	{
		$post = $event->post;
		
		// If this post is a reply to a thread
		if ($post->reply_to_board_id)
		{
			$thread_id = $post->reply_to_board_id;
		}
		else
		{
			$thread_id = $post->board_id;
		}
		
		// Side-note:
		// We use the "board.b.thread.board_id" syntax instead of
		// "thread.board_id" because we will often want to clear threads
		// for an entire board. Without that prefix, we can't easily
		// accomplish that.
		Cache::forget("board.{$post->board_uri}.thread.{$thread_id}");
		Cache::tags(["board.{$post->board_uri}", "threads"])->forget("board.{$post->board_uri}.thread.{$thread_id}");
	}
	
}