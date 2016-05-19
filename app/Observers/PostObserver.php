<?php

namespace App\Observers;

use App\Post;
use App\PostChecksum;
use App\PostCite;

use Event;
use App\Events\PostWasAdded;
use App\Events\PostWasDeleted;
use App\Events\PostWasModified;

class PostObserver {

	/**
	 * Handles model after create (non-existant save).
	 *
	 * @param \App\Post  $post
	 * @return boolean
	 */
	public function created(Post $post)
	{
		// Fire event, which clears cache among other things.
		Event::fire(new PostWasAdded($post));

		return true;
	}

	/**
	 * Checks if this model is allowed to create (non-existant save).
	 *
	 * @param \App\Post  $post
	 * @return boolean
	 */
	public function creating(Post $post)
	{
		// Reuire board_id to save.
		return isset($post->board_id);
	}

	/**
	 * Handles model after delete (pre-existing hard or soft deletion).
	 *
	 * @param \App\Post  $post
	 * @return boolean
	 */
	public function deleted($post)
	{
		// After a post is deleted, update OP's reply count.
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

		return true;
	}

	/**
	 * Checks if this model is allowed to delete (pre-existing deletion).
	 *
	 * @param \App\Post  $post
	 * @return boolean
	 */
	public function deleting($post)
	{
		// When deleting a post, delete its children.
		Post::replyTo($post->post_id)->delete();

		// Clear authorshop information.
		$post->author_ip = null;
		$post->author_ip_nulled_at = \Carbon\Carbon::now();
		$post->save();

		return true;
	}

	/**
	 * Handles model after save (pre-existing or non-existant save).
	 *
	 * @param \App\Post  $post
	 * @return boolean
	 */
	public function saved(Post $post)
	{
		// Rebuild citation relationships.

		// Clear citations.
		$post->cites()->delete();

		// Readd citations.
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

		return true;
	}

	/**
	 * Checks if this model is allowed to save (pre-existing or non-existant save).
	 *
	 * @param \App\Post  $post
	 * @return boolean
	 */
	public function saving(Post $post)
	{
		// Reuire board_id to save.
		return isset($post->board_id);
	}

	/**
	 * Handles model after update (pre-existing save).
	 *
	 * @param \App\Post  $post
	 * @return boolean
	 */
	public function updated(Post $post)
	{
		// Fire event, which clears cache among other things.
		Event::fire(new PostWasModified($post));

		return true;
	}

	/**
	 * Checks if this model is allowed to update (pre-existing save).
	 *
	 * @param \App\Post  $post
	 * @return boolean
	 */
	public function updating(Post $post)
	{
		return true;
	}

}
