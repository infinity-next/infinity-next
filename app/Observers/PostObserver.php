<?php namespace App\Observers;

use App\Post;
use App\PostCite;
use App\Support\IP;

use Event;
use App\Events\PostWasAdded;
use App\Events\PostWasDeleted;
use App\Events\PostWasModified;

class PostObserver {
	
	// Fire events on post created.
	public function created(Post $post)
	{
		Event::fire(new PostWasAdded($post));
	}
	
	public function creating(Post $post)
	{
		return isset($post->board_id);
	}
	
	// After a post is deleted, update OP's reply count.
	public function deleted($post)
	{
		if (!is_null($post->reply_to))
		{
			$lastReply = $post->op->getReplyLast();
			
			if ($lastReply)
			{
				$post->op->reply_last = $lastReply->created_at;
			}
			else
			{
				$post->op->reply_last = $post->op->created_at;
			}
			
			$post->op->reply_count -= 1;
			$post->op->save();
		}
		
		Event::fire(new PostWasDeleted($post));
	}
	
	// When deleting a post, delete its children.
	public function deleting($post)
	{
		Post::replyTo($post->post_id)->delete();
	}
	
	// Update citation references
	public function saved(Post $post)
	{
		$post->cites()->delete();
		
		// Process citations.
		$cited = $post->getCitesFromText();
		$cites = [];
		
		foreach ($cited['posts'] as $citedPost)
		{
			$cites[] = new PostCite([
				'post_board_uri' => $post->board_uri,
				'post_board_id'  => $post->board_id,
				'cite_id'        => $citedPost->post_id,
				'cite_board_uri' => $citedPost->board_uri,
				'cite_board_id'  => $citedPost->board_id,
			]);
		}
		
		foreach ($cited['boards'] as $citedBoard)
		{
			$cites[] = new PostCite([
				'post_board_uri' => $post->board_uri,
				'post_board_id'  => $post->board_id,
				'cite_board_uri' => $citedBoard->board_uri,
			]);
		}
		
		if (count($cites) > 0)
		{
			$post->cites()->saveMany($cites);
		}
		
	}
	
	// public function saving(Post $post)
	// {
	// 	return $post;
	// }
	
	// Fire events on post updated.
	public function updated(Post $post)
	{
		Event::fire(new PostWasModified($post));
	}
	
}
